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
                    '76' => TeamType::Eap->value,
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

                $name = trim((string) $reader->getAttribute('NM_REFERENCIA'));
                if ($name === '') {
                    $name = trim((string) $reader->getAttribute('DS_EQUIPE'));
                }

                $typesByIne[$ine] = $type;
                $teams[$type.'|'.$ine.'|'.$cnes] = compact('ine', 'cnes', 'type', 'name');
            }
        } finally {
            $reader->close();
        }

        if (! preg_match('/^\d{7}$/', (string) $ibge) || $teams === []) {
            throw new RuntimeException('O XML CNES não contém código de município ou equipes homologadas válidas.');
        }

        $teamsList = array_values($teams);

        // Conta INEs únicos por tipo (evita duplicação quando o mesmo INE aparece em múltiplos CNES)
        $uniqueInesByType = [
            'esf' => [],
            'eap' => [],
            'saude_bucal' => [],
            'emulti' => [],
        ];

        foreach ($teamsList as $team) {
            $typeKey = match ($team['type']) {
                TeamType::Esf->value => 'esf',
                TeamType::Eap->value => 'eap',
                TeamType::SaudeBucal->value => 'saude_bucal',
                TeamType::Emulti->value => 'emulti',
                default => null,
            };
            if ($typeKey !== null) {
                $uniqueInesByType[$typeKey][$team['ine']] = true;
            }
        }

        $counts = [
            'esf' => count($uniqueInesByType['esf']),
            'eap' => count($uniqueInesByType['eap']),
            'saude_bucal' => count($uniqueInesByType['saude_bucal']),
            'emulti' => count($uniqueInesByType['emulti']),
            'total' => count($teamsList),
        ];

        return [
            'ibge' => (string) $ibge,
            'teams' => $teamsList,
            'counts' => $counts,
        ];
    }

    /**
     * Retorna a lista de equipes homologadas no CNES elegíveis exclusivamente ao Indicador C1 (eSF Tipo 70 e eAP Tipo 76).
     *
     * @return list<array{ine: string, name: string, type: string, cnes: string}>
     */
    public function getEligibleC1Teams(?string $xmlPath = null): array
    {
        $targetPath = $this->resolveAvailableXmlPath($xmlPath);
        if ($targetPath === null || ! is_file($targetPath) || ! is_readable($targetPath)) {
            return [];
        }

        try {
            $parsed = $this->parse($targetPath);
            $eligible = [];
            $seenInes = [];

            foreach ($parsed['teams'] as $team) {
                if ($team['type'] === TeamType::Esf->value || $team['type'] === TeamType::Eap->value) {
                    $ine = $team['ine'];
                    if (isset($seenInes[$ine])) {
                        continue;
                    }
                    $seenInes[$ine] = true;
                    $teamCode = ($team['type'] === TeamType::Eap->value) ? '76' : '70';
                    $eligible[] = [
                        'ine' => $ine,
                        'name' => ! empty($team['name']) ? $team['name'] : ($teamCode === '76' ? 'eAP '.$ine : 'eSF '.$ine),
                        'type' => $teamCode,
                        'cnes' => $team['cnes'] ?? '',
                    ];
                }
            }

            return $eligible;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Localiza o caminho de um arquivo XML CNES válido disponível no sistema.
     */
    public function resolveAvailableXmlPath(?string $explicitPath = null): ?string
    {
        if (filled($explicitPath)) {
            $path = $this->normalizePath($explicitPath);
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        $configured = config('esus.homologated_xml_path');
        if (filled($configured)) {
            $path = $this->normalizePath((string) $configured);
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        // Procura por arquivos XML na pasta importacao/
        $importDir = base_path('importacao');
        if (is_dir($importDir)) {
            $xmlFiles = glob($importDir . DIRECTORY_SEPARATOR . '*.xml');
            if (! empty($xmlFiles)) {
                // Prioriza arquivos contendo "XmlParaESUS"
                foreach ($xmlFiles as $file) {
                    if (stripos(basename($file), 'XmlParaESUS') !== false && is_readable($file)) {
                        return $file;
                    }
                }
                if (is_readable($xmlFiles[0])) {
                    return $xmlFiles[0];
                }
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, '/') || str_starts_with($trimmed, '\\') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $trimmed)) {
            return $trimmed;
        }

        return base_path($trimmed);
    }
}
