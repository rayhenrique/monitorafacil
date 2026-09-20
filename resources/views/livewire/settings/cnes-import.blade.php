<div class="app-page">
    <x-settings-tabs
        title="Importar CNES / XML"
        subtitle="Upload e validação estrutural do arquivo oficial de homologação das equipes de APS (XmlParaESUS31)"
        activeTab="cnes-import"
    />

    <!-- Alertas de Feedback -->
    @if ($successMessage)
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-900 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ $successMessage }}</span>
        </div>
    @endif

    @if ($previewError)
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-900 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span class="font-medium">{{ $previewError }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna de Upload e Prévia -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Área de Upload do XML -->
            <div class="rounded-3xl border border-line bg-white p-6 sm:p-8 shadow-sm">
                <h2 class="text-base font-bold text-ink mb-1">Upload de Novo Arquivo XML</h2>
                <p class="text-xs text-muted mb-6">
                    Selecione o arquivo de homologação exportado do CNES oficial (geralmente no formato <span class="font-mono text-slate-800">XmlParaESUS31_{cnes}.xml</span>). O sistema validará a integridade, sintaxe sem DTD e contagem das equipes.
                </p>

                <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 p-6 sm:p-8 text-center transition hover:border-teal-400 hover:bg-slate-50">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>

                    <div class="mt-4 flex flex-col items-center justify-center text-xs leading-6 text-slate-600">
                        <label for="cnesXmlFileInput" class="relative cursor-pointer rounded-xl bg-teal-700 px-4 py-2 font-semibold text-white shadow-sm hover:bg-teal-800 transition focus-within:outline-none focus-within:ring-2 focus-within:ring-teal-500/40">
                            <span>Selecionar Arquivo XML</span>
                            <input
                                id="cnesXmlFileInput"
                                type="file"
                                wire:model="xmlFile"
                                accept=".xml,text/xml,application/xml"
                                class="sr-only"
                            >
                        </label>
                        <p class="mt-2 text-[11px] text-muted">Apenas arquivos no formato .xml oficial</p>
                    </div>

                    <div wire:loading wire:target="xmlFile" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-50 px-3 py-1.5 text-xs text-teal-800 font-medium">
                        <svg class="animate-spin h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Carregando e validando estrutura do XML...</span>
                    </div>

                    @error('xmlFile')
                        <span class="mt-3 text-xs text-rose-600 block font-medium">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Card de Prévia dos Dados Encontrados -->
            @if ($parsedPreview)
                <div class="rounded-3xl border border-teal-200 bg-teal-50/40 p-6 sm:p-8 shadow-sm space-y-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-600 text-white">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-teal-950">Validação Estrutural Aprovada</h3>
                                <p class="text-xs text-teal-800">Código IBGE identificado: <span class="font-mono font-bold">{{ $parsedPreview['ibge'] }}</span></p>
                            </div>
                        </div>

                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800 border border-teal-200">
                            {{ $parsedPreview['counts']['total'] }} Equipes
                        </span>
                    </div>

                    <!-- Detalhamento por Tipo de Equipe -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white p-4 border border-teal-100 shadow-xs text-center">
                            <span class="text-[10px] font-bold uppercase text-muted tracking-wider block">eSF (Tipo 70)</span>
                            <span class="text-2xl font-bold font-mono text-teal-900 mt-1 block">{{ $parsedPreview['counts']['esf'] }}</span>
                            <span class="text-[11px] text-muted">Saúde da Família</span>
                        </div>

                        <div class="rounded-2xl bg-white p-4 border border-teal-100 shadow-xs text-center">
                            <span class="text-[10px] font-bold uppercase text-muted tracking-wider block">eSB (Tipo 71)</span>
                            <span class="text-2xl font-bold font-mono text-teal-900 mt-1 block">{{ $parsedPreview['counts']['saude_bucal'] }}</span>
                            <span class="text-[11px] text-muted">Saúde Bucal</span>
                        </div>

                        <div class="rounded-2xl bg-white p-4 border border-teal-100 shadow-xs text-center">
                            <span class="text-[10px] font-bold uppercase text-muted tracking-wider block">eMulti (Tipo 72)</span>
                            <span class="text-2xl font-bold font-mono text-teal-900 mt-1 block">{{ $parsedPreview['counts']['emulti'] }}</span>
                            <span class="text-[11px] text-muted">Multiprofissional</span>
                        </div>
                    </div>

                    <!-- Botão de Confirmação e Salvamento -->
                    <div class="flex flex-col-reverse gap-3 border-t border-teal-200/60 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button
                            type="button"
                            wire:click="$set('parsedPreview', null)"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer sm:w-auto"
                        >
                            Descartar
                        </button>
                        <button
                            type="button"
                            wire:click="saveXml"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 px-5 py-2 text-xs font-semibold text-white shadow-sm transition cursor-pointer sm:w-auto"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Confirmar e Salvar Arquivo</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Coluna de Arquivo Ativo Configurado -->
        <div class="space-y-5">
            <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-700 border border-teal-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted">Arquivo Ativo em Uso</h3>
                        <p class="text-xs text-ink font-semibold mt-0.5">Configuração do Sistema</p>
                    </div>
                </div>

                @if ($currentXmlInfo)
                    <div class="space-y-3 text-xs border-t border-line pt-3">
                        <div>
                            <span class="text-[11px] text-muted block">Caminho no .env:</span>
                            <span class="font-mono text-slate-800 break-all font-medium">{{ $currentXmlInfo['path'] }}</span>
                        </div>

                        <div class="flex justify-between border-t border-slate-100 pt-2">
                            <span class="text-muted">Tamanho:</span>
                            <span class="font-semibold text-ink font-mono">{{ $currentXmlInfo['size_formatted'] }}</span>
                        </div>

                        <div class="flex justify-between border-t border-slate-100 pt-2">
                            <span class="text-muted">Modificado em:</span>
                            <span class="font-medium text-ink tabular-nums">{{ $currentXmlInfo['last_modified'] }}</span>
                        </div>

                        @if ($currentXmlInfo['parsed'])
                            <div class="rounded-2xl bg-slate-50 p-3 border border-slate-200 mt-2 space-y-1.5">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-muted">IBGE no XML:</span>
                                    <span class="font-mono font-bold text-ink">{{ $currentXmlInfo['parsed']['ibge'] }}</span>
                                </div>
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-muted">Total de Equipes:</span>
                                    <span class="font-mono font-bold text-teal-800">{{ $currentXmlInfo['parsed']['counts']['total'] }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-500">
                                    <span>eSF / eSB / eMulti:</span>
                                    <span class="font-mono">{{ $currentXmlInfo['parsed']['counts']['esf'] }} / {{ $currentXmlInfo['parsed']['counts']['saude_bucal'] }} / {{ $currentXmlInfo['parsed']['counts']['emulti'] }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="border-t border-line pt-3 text-xs text-rose-600 space-y-1">
                        <p class="font-medium">Nenhum arquivo ativo localizado no caminho especificado em ESUS_HOMOLOGATED_XML_PATH.</p>
                        <p class="text-muted">Importe um arquivo acima para habilitar o processamento.</p>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] to-[#081714] text-white p-6 shadow-md">
                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-400 mb-2">Orientações Técnicas</h4>
                <p class="text-xs text-slate-300 leading-relaxed">
                    O arquivo de equipes homologadas deve ser renovado mensalmente ou sempre que houver desativação ou credenciamento de novas equipes no CNES/MS. Assegure-se de que o código IBGE corresponda exatamente ao município cadastrado.
                </p>
            </div>
        </div>
    </div>
</div>
