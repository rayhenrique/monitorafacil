<section id="simulador" aria-labelledby="financial-title" class="mt-10 scroll-mt-8">
    <div class="mb-5">
        <p class="eyebrow">Planejamento financeiro</p>
        <h2 id="financial-title" class="mt-2 text-2xl font-semibold tracking-tight text-ink">Simulador de vínculo e acompanhamento</h2>
        <p class="mt-1 max-w-3xl text-sm leading-relaxed text-muted">Distribua as eSF homologadas entre as classificações para estimar o valor bruto mensal deste componente. Este cenário não representa a classificação oficial nem o repasse efetivo.</p>
    </div>

    @if ($teamCount === null)
        <div class="rounded-2xl border border-dashed border-line bg-white p-7 text-sm text-muted">Ainda não há total de eSF consolidado para este período. O simulador ficará disponível após a sincronização.</div>
    @else
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,1fr)]">
            <div class="rounded-2xl border border-line bg-white p-6 shadow-panel">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-ink">Classificação das eSF</h3>
                        <p class="mt-1 text-sm text-muted">{{ number_format($teamCount, 0, ',', '.') }} equipes disponíveis para distribuir.</p>
                    </div>
                    <button type="button" wire:click="useScenario('good')" class="rounded-lg border border-line px-3 py-2 text-sm font-medium text-teal-800 hover:border-teal-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">Todas em Bom</button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ([['key' => 'optimal', 'label' => 'Ótimo'], ['key' => 'good', 'label' => 'Bom'], ['key' => 'sufficient', 'label' => 'Suficiente'], ['key' => 'regular', 'label' => 'Regular']] as $classification)
                        <label class="block rounded-xl border border-line p-4">
                            <span class="flex items-center justify-between gap-2 text-sm font-medium text-ink">
                                <span>{{ $classification['label'] }}</span>
                                <span class="text-xs font-normal text-muted">R$ {{ number_format($rates[$classification['key']], 0, ',', '.') }}/mês</span>
                            </span>
                            <input type="number" min="0" max="{{ $teamCount }}" inputmode="numeric" wire:model.live.debounce.300ms="{{ $classification['key'] }}" class="mt-3 w-full rounded-lg border border-line bg-white px-3 py-2.5 text-base tabular-nums focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200" aria-label="Quantidade de eSF em {{ $classification['label'] }}">
                        </label>
                    @endforeach
                </div>

                <p class="mt-5 text-sm {{ $assigned === $teamCount ? 'text-teal-800' : 'text-amber-800' }}" role="status">
                    @if ($assigned > $teamCount)
                        A distribuição excede o total em {{ number_format($assigned - $teamCount, 0, ',', '.') }} equipe(s).
                    @elseif ($assigned < $teamCount)
                        Restam {{ number_format($teamCount - $assigned, 0, ',', '.') }} equipe(s) para distribuir.
                    @else
                        Todas as equipes foram distribuídas.
                    @endif
                </p>
            </div>

            <div class="rounded-2xl bg-teal-950 p-6 text-white shadow-panel" aria-live="polite">
                <p class="text-xs font-bold uppercase tracking-wider text-teal-200">Projeção do cenário</p>
                @if ($monthly === null)
                    <p class="mt-7 text-3xl font-semibold tracking-tight">Aguardando distribuição</p>
                    <p class="mt-3 text-sm leading-relaxed text-teal-100">Atribua exatamente {{ number_format($teamCount, 0, ',', '.') }} eSF às classificações para calcular.</p>
                @else
                    <p class="mt-7 text-sm text-teal-100">Valor bruto mensal</p>
                    <p class="mt-1 text-4xl font-semibold tracking-tight tabular-nums">R$ {{ number_format($monthly, 2, ',', '.') }}</p>
                    <div class="mt-7 border-t border-teal-800 pt-6">
                        <p class="text-sm text-teal-100">Projeção para quatro parcelas mensais</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">R$ {{ number_format($monthly * 4, 2, ',', '.') }}</p>
                    </div>
                @endif
                <p class="mt-7 text-xs leading-relaxed text-teal-100">Apenas componente de vínculo e acompanhamento de eSF 40h. Não inclui componente fixo, qualidade, eSB ou eMulti. Regras de habilitação e limites por equipe podem alterar o repasse real.</p>
            </div>
        </div>
        <p class="mt-4 text-xs leading-relaxed text-muted">Valores por classificação: <a href="https://www.gov.br/saude/pt-br/assuntos/noticias/2024/junho/ministerio-da-saude-cria-faq-para-esclarecer-sobre-o-novo-financiamento-da-atencao-primaria/1o-edicao-faq-aps" target="_blank" rel="noopener noreferrer" class="font-medium text-teal-800 underline hover:text-teal-950">tabela oficial do Ministério da Saúde</a>. A <a href="https://www.gov.br/saude/pt-br/centrais-de-conteudo/publicacoes/notas-tecnicas/2025/nota-tecnica-no-30-2025-cgesco-desco-saps-ms.pdf/@@download/file" target="_blank" rel="noopener noreferrer" class="font-medium text-teal-800 underline hover:text-teal-950">Nota Técnica 30/2025</a> descreve a metodologia de classificação.</p>
    @endif
</section>
