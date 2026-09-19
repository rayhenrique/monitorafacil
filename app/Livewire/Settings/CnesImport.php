<?php

namespace App\Livewire\Settings;

use App\Services\CnesXmlParserService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('layouts.app')]
class CnesImport extends Component
{
    use WithFileUploads;

    /** @var mixed */
    public $xmlFile;

    /** @var array{ibge: string, teams: list<array{ine: string, cnes: string, type: string}>, counts: array{esf: int, saude_bucal: int, emulti: int, total: int}}|null */
    public ?array $parsedPreview = null;

    public ?string $previewError = null;

    public ?string $successMessage = null;

    public function updatedXmlFile(CnesXmlParserService $parser, SettingsService $settingsService): void
    {
        $this->validate([
            'xmlFile' => ['required', 'file', 'mimetypes:text/xml,application/xml,text/plain'],
        ], [
            'xmlFile.required' => 'Selecione um arquivo XML do CNES para validação.',
            'xmlFile.mimetypes' => 'O arquivo selecionado deve ser um documento XML válido.',
        ]);

        $this->previewError = null;
        $this->parsedPreview = null;
        $this->successMessage = null;

        try {
            $tempPath = $this->xmlFile->getRealPath();
            $result = $parser->parse($tempPath);

            $configuredIbge = $settingsService->get('municipio_ibge');
            if ($configuredIbge !== null && $configuredIbge !== '' && $configuredIbge !== $result['ibge']) {
                $this->previewError = "Atenção: O código IBGE do XML ({$result['ibge']}) é diferente do IBGE configurado no sistema ({$configuredIbge}).";
            }

            $this->parsedPreview = $result;
        } catch (Throwable $e) {
            $this->previewError = 'Falha na validação do XML: '.$e->getMessage();
        }
    }

    public function saveXml(): void
    {
        if ($this->xmlFile === null || $this->parsedPreview === null) {
            $this->addError('xmlFile', 'Nenhum arquivo XML válido foi analisado para importação.');

            return;
        }

        try {
            $dir = base_path('importacao');
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $originalName = $this->xmlFile->getClientOriginalName();
            $targetPath = $dir.DIRECTORY_SEPARATOR.$originalName;

            // Move uploaded file to importacao
            File::copy($this->xmlFile->getRealPath(), $targetPath);

            $this->successMessage = "Arquivo CNES '{$originalName}' importado com sucesso! Contém {$this->parsedPreview['counts']['total']} equipes homologadas.";
            $this->xmlFile = null;
            $this->parsedPreview = null;
        } catch (Throwable $e) {
            $this->previewError = 'Erro ao salvar o arquivo XML: '.$e->getMessage();
        }
    }

    public function render(CnesXmlParserService $parser): View
    {
        $currentXmlPath = config('esus.homologated_xml_path');
        $currentXmlInfo = null;

        if (filled($currentXmlPath)) {
            $resolved = trim($currentXmlPath);
            if (! (str_starts_with($resolved, '/') || str_starts_with($resolved, '\\') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $resolved))) {
                $resolved = base_path($resolved);
            }

            if (File::exists($resolved)) {
                $fileSize = File::size($resolved);
                $lastModified = File::lastModified($resolved);

                try {
                    $parsedCurrent = $parser->parse($resolved);
                } catch (Throwable) {
                    $parsedCurrent = null;
                }

                $currentXmlInfo = [
                    'path' => $currentXmlPath,
                    'full_path' => $resolved,
                    'size_formatted' => round($fileSize / 1024, 1).' KB',
                    'last_modified' => date('d/m/Y H:i:s', $lastModified),
                    'parsed' => $parsedCurrent,
                ];
            }
        }

        return view('livewire.settings.cnes-import', [
            'currentXmlInfo' => $currentXmlInfo,
        ]);
    }
}
