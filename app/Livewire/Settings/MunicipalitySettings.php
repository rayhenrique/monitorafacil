<?php

namespace App\Livewire\Settings;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class MunicipalitySettings extends Component
{
    use WithFileUploads;

    public string $municipio_nome = '';

    public string $municipio_ibge = '';

    public string $municipio_cnes_sede = '';

    /** @var mixed */
    public $logo;

    public ?string $currentLogoPath = null;

    public ?string $successMessage = null;

    public function mount(SettingsService $settingsService): void
    {
        $settings = $settingsService->all();
        $this->municipio_nome = $settings['municipio_nome'] ?? '';
        $this->municipio_ibge = $settings['municipio_ibge'] ?? '';
        $this->municipio_cnes_sede = $settings['municipio_cnes_sede'] ?? '';
        $this->currentLogoPath = $settings['logo_path'] ?? null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'municipio_nome' => ['required', 'string', 'min:2', 'max:120'],
            'municipio_ibge' => ['nullable', 'string', 'digits:7'],
            'municipio_cnes_sede' => ['nullable', 'string', 'digits:7'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ];
    }

    protected $messages = [
        'municipio_nome.required' => 'O nome do município é obrigatório.',
        'municipio_nome.min' => 'O nome do município deve ter no mínimo 2 caracteres.',
        'municipio_ibge.digits' => 'O código IBGE deve conter exatamente 7 dígitos numéricos.',
        'municipio_cnes_sede.digits' => 'O CNES deve conter exatamente 7 dígitos numéricos.',
        'logo.image' => 'O arquivo selecionado deve ser uma imagem válida.',
        'logo.mimes' => 'A imagem deve ser do formato PNG, JPG, JPEG, WEBP ou SVG.',
        'logo.max' => 'A imagem não pode ultrapassar 2MB.',
    ];

    public function save(SettingsService $settingsService): void
    {
        $this->validate();

        $settingsService->set('municipio_nome', trim($this->municipio_nome));
        $settingsService->set('municipio_ibge', trim($this->municipio_ibge) ?: null);
        $settingsService->set('municipio_cnes_sede', trim($this->municipio_cnes_sede) ?: null);

        if ($this->logo !== null) {
            // Delete old logo if exists
            if ($this->currentLogoPath && Storage::disk('public')->exists($this->currentLogoPath)) {
                Storage::disk('public')->delete($this->currentLogoPath);
            }

            $path = $this->logo->store('logos', 'public');
            $settingsService->set('logo_path', $path);
            $this->currentLogoPath = $path;
            $this->logo = null;
        }

        $this->successMessage = 'Configurações do município atualizadas com sucesso!';
    }

    public function removeLogo(SettingsService $settingsService): void
    {
        if ($this->currentLogoPath && Storage::disk('public')->exists($this->currentLogoPath)) {
            Storage::disk('public')->delete($this->currentLogoPath);
        }

        $settingsService->set('logo_path', null);
        $this->currentLogoPath = null;
        $this->logo = null;
        $this->successMessage = 'Logotipo removido com sucesso!';
    }

    public function render(): View
    {
        return view('livewire.settings.municipality-settings');
    }
}
