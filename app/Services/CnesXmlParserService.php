<?php

namespace App\Services;

use App\Enums\TeamType;
use RuntimeException;
use XMLReader;

class CnesXmlParserService
{
    /**
     * Parse and validate a CNES XML file.
     *
     * @return array{
     *     ibge: string,
     *     teams: list<array{ine: string, cnes: string, type: string}>,
     *     counts: array{esf: int, saude_bucal: int, emulti: int, total: int}
     * }
     */
    public function parse(string $filePath): array
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new RuntimeException('O arquivo XML informado não foi encontrado ou não pode ser lido.');
        }

        $reader = new XMLReader();

        if (! @$reader->open($filePath, null, LIBXML_NONET)) {
            throw new RuntimeException('Não foi possível abrir o arquivo XML informado.');
        }

        $reader->setParserProperty(XMLReader::LOADDTD, false);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, false);

        $ibge = null;
        $cnes = null;
        $teams = [];
        $typesByIne = [];

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::DOC_TYPE) {
                    throw new RuntimeException('O arquivo XML CNES não pode conter uma DTD.');
                }

                if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'DADOS_GERAIS_ESTABELECIMENTOS') {
                    $cnes = null;
                    continue;
                }

                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }

                if ($reader->name === 'IDENTIFICACAO') {
                    $ibge = trim((string) $reader->getAttribute('CO_IBGE_MUN'));
                    continue;
                }

                if ($reader->name === 'DADOS_GERAIS_ESTABELECIMENTOS') {
                    $cnes = trim((string) $reader->getAttribute('CNES'));
                    continue;
                }

                if ($reader->name !== 'DADOS_EQUIPES' || trim((string) $reader->getAttribute('DT_DESATIVACAO')) !== '') {
                    continue;
                }

                $type = match ($reader->getAttribute('TP_EQUIPE')) {
                    '70' => TeamType::Esf->value,
                    '71' => TeamType::SaudeBucal->value,
                    '72' => TeamType::Emulti->value,
                    default => null,
                };

                if ($type === null) {
                    continue;
                }

                $ine = trim((string) $reader->getAttribute('CO_INE'));

                if (! preg_match('/^\d{10}$/', $ine) || ! preg_match('/^\d{7}$/', (string) $cnes)) {
                    throw new RuntimeException('O arquivo XML CNES contém uma equipe com INE ou CNES inválido.');
                }

                if (isset($typesByIne[$ine]) && $typesByIne[$ine] !== $type) {
                    throw new RuntimeException('O arquivo XML CNES atribui tipos diferentes ao mesmo INE.');
                }

                $typesByIne[$ine] = $type;
                $teams[$type.'|'.$ine.'|'.$cnes] = compact('ine', 'cnes', 'type');
            }
        } finally {
            $reader->close();
        }

        if (! preg_match('/^\d{7}$/', (string) $ibge) || $teams === []) {
            throw new RuntimeException('O XML CNES não contém código de município ou equipes homologadas válidas.');
        }

        $teamsList = array_values($teams);

        $counts = [
            'esf' => 0,
            'saude_bucal' => 0,
            'emulti' => 0,
            'total' => count($teamsList),
        ];

        foreach ($teamsList as $team) {
            match ($team['type']) {
                TeamType::Esf->value => $counts['esf']++,
                TeamType::SaudeBucal->value => $counts['saude_bucal']++,
                TeamType::Emulti->value => $counts['emulti']++,
                default => null,
            };
        }

        return [
            'ibge' => (string) $ibge,
            'teams' => $teamsList,
            'counts' => $counts,
        ];
    }
}
