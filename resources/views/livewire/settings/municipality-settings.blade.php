<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-settings-tabs
        title="Dados do Município"
        subtitle="Identificação institucional, códigos sanitários e identidade visual"
        activeTab="municipality"
    />

    @if ($successMessage)
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-900 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ $successMessage }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informações Institucionais e Logotipo -->
        <div class="lg:col-span-2 rounded-3xl border border-line bg-white shadow-sm overflow-hidden p-6 sm:p-8">
            <h2 class="text-base font-bold text-ink mb-1">Parâmetros Cadastrais</h2>
            <p class="text-xs text-muted mb-6">Esses dados definem os cabeçalhos executivos, relatórios e a validação do XML de homologação.</p>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <label for="municipio_nome" class="block text-xs font-semibold text-ink mb-1.5">
                        Nome do Município <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="municipio_nome"
                        wire:model="municipio_nome"
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                        placeholder="Ex: Pão de Açúcar"
                    >
                    @error('municipio_nome') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="municipio_ibge" class="block text-xs font-semibold text-ink mb-1.5">
                            Código IBGE (7 dígitos)
                        </label>
                        <input
                            type="text"
                            id="municipio_ibge"
                            wire:model="municipio_ibge"
                            maxlength="7"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink font-mono placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Ex: 2706401"
                        >
                        <span class="text-[11px] text-muted mt-1 block">Utilizado para cruzar com as equipes do XML CNES.</span>
                        @error('municipio_ibge') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="municipio_cnes_sede" class="block text-xs font-semibold text-ink mb-1.5">
                            CNES da Sede / Secretaria (7 dígitos)
                        </label>
                        <input
                            type="text"
                            id="municipio_cnes_sede"
                            wire:model="municipio_cnes_sede"
                            maxlength="7"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink font-mono placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Ex: 2709151"
                        >
                        <span class="text-[11px] text-muted mt-1 block">Código identificador da sede da saúde municipal.</span>
                        @error('municipio_cnes_sede') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Seção do Logotipo -->
                <div class="pt-4 border-t border-line">
                    <label class="block text-xs font-semibold text-ink mb-2">
                        Brasão / Logotipo Oficial
                    </label>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                        <!-- Preview Atual ou Novo -->
                        <div class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-2 overflow-hidden shadow-inner">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="Prévia do logotipo" class="h-full w-full object-contain">
                            @elseif ($currentLogoPath)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentLogoPath) }}" alt="Logotipo atual" class="h-full w-full object-contain">
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-400 text-center">
                                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span class="text-[10px] text-muted">Sem logo</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-2">
                            <input
                                type="file"
                                id="logoInput"
                                wire:model="logo"
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer"
                            >
                            <div wire:loading wire:target="logo" class="text-[11px] text-teal-600 font-medium">
                                Carregando imagem...
                            </div>
                            <p class="text-[11px] text-muted">Formatos suportados: PNG, JPG, WEBP ou SVG (máx. 2MB). O logotipo será exibido na barra superior e nos relatórios.</p>
                            @error('logo') <span class="text-[11px] text-rose-600 block font-medium">{{ $message }}</span> @enderror

                            @if ($currentLogoPath && ! $logo)
                                <button
                                    type="button"
                                    wire:click="removeLogo"
                                    wire:confirm="Deseja realmente remover o logotipo atual?"
                                    class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-800 transition font-medium"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    <span>Remover Logotipo Atual</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-5 border-t border-line flex items-center justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/30 cursor-pointer"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Salvar Configurações</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Painel Informativo Lateral -->
        <div class="space-y-5">
            <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] to-[#081714] text-white p-6 shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Importância do IBGE</h3>
                        <p class="text-xs text-teal-300/80">Regra de integridade do e-SUS</p>
                    </div>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    O código IBGE registrado nesta tela atua como uma barreira de segurança sanitária durante o processo de consolidação dos dados do prontuário eletrônico. Se o arquivo XML do CNES tiver código IBGE divergente, a rotina de sincronização não prosseguirá.
                </p>
            </div>

            <div class="rounded-3xl border border-line bg-white p-6 shadow-sm">
                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Resumo Atual</h4>
                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-muted">Município:</dt>
                        <dd class="font-semibold text-ink">{{ $municipio_nome ?: 'Não definido' }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-muted">Código IBGE:</dt>
                        <dd class="font-mono text-ink">{{ $municipio_ibge ?: 'Não definido' }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-muted">CNES Sede:</dt>
                        <dd class="font-mono text-ink">{{ $municipio_cnes_sede ?: 'Não definido' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted">Logotipo:</dt>
                        <dd class="text-ink font-medium">{{ $currentLogoPath ? 'Cadastrado' : 'Padrão (MF)' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
