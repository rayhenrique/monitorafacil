<div class="px-4 py-6 sm:px-8 max-w-7xl mx-auto space-y-6">
    <x-help-tabs
        title="Guia de Preenchimento | Ministério da Saúde"
        subtitle="Orientações técnicas oficiais para registro qualificado no e-SUS APS (Componente de Qualidade)"
        activeTab="guide"
    />

    <!-- Banner Oficial de Acesso -->
    <div class="rounded-3xl border border-line bg-gradient-to-br from-[#0c1f1c] via-[#0f2d26] to-[#081714] text-white p-6 sm:p-8 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2 max-w-3xl">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/20 px-3 py-1 text-[11px] font-semibold text-teal-300 border border-teal-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                        Fonte Oficial do Ministério da Saúde
                    </span>
                    <span class="text-xs text-slate-400">Nota Técnica nº 30/2025 · Portaria GM/MS nº 3.493/2024</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    Guia de Preenchimento do Prontuário Eletrônico e-SUS APS e Aplicativos
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Instrumento técnico de apoio elaborado pela Secretaria de Atenção Primária à Saúde (SAPS/MS) para orientar os profissionais da APS sobre o registro qualificado das ações do Componente de Qualidade no âmbito do Cofinanciamento Federal.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                <a
                    href="https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-xs font-semibold text-white shadow-md shadow-teal-950/40 transition"
                >
                    <span>Abrir Portal Oficial SISAPS</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Navegação Interna por Modalidade de Equipe -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button
            type="button"
            wire:click="setCategory('esf')"
            class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $activeCategory === 'esf' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <span>Saúde da Família (eSF / eAP)</span>
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeCategory === 'esf' ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-600' }}">7 Indicadores</span>
        </button>

        <button
            type="button"
            wire:click="setCategory('esb')"
            class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $activeCategory === 'esb' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
            </svg>
            <span>Saúde Bucal (eSB)</span>
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeCategory === 'esb' ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-600' }}">6 Indicadores</span>
        </button>

        <button
            type="button"
            wire:click="setCategory('emulti')"
            class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $activeCategory === 'emulti' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <span>Equipes eMulti</span>
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeCategory === 'emulti' ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-600' }}">2 Indicadores</span>
        </button>

        <button
            type="button"
            wire:click="setCategory('cadastros')"
            class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $activeCategory === 'cadastros' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-line' }}"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <span>Cadastros (MICI / MICDT)</span>
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeCategory === 'cadastros' ? 'bg-teal-800 text-teal-200' : 'bg-slate-100 text-slate-600' }}">Base Estruturante</span>
        </button>
    </div>

    <!-- CONTEÚDO 1: SAÚDE DA FAMÍLIA (eSF / eAP) -->
    @if ($activeCategory === 'esf')
        <div class="space-y-4 animate-fade-in">
            <div class="flex items-center justify-between border-b border-line pb-3">
                <div>
                    <h3 class="text-base font-bold text-ink">Equipe de Atenção Primária e Saúde da Família (eSF / eAP)</h3>
                    <p class="text-xs text-muted">Diretrizes oficiais para o registro dos indicadores clínicos C1 a C7 no Prontuário Eletrônico</p>
                </div>
                <a
                    href="https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/equipeaps"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900 transition"
                >
                    <span>Ver no site oficial</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- C1 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C1</span>
                        <span class="text-[11px] font-semibold text-teal-700">Demanda Espontânea</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Mais Acesso à APS</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Avalia a capacidade da equipe em absorver a demanda espontânea no mesmo dia. Registre no módulo de Atendimento Individual com o motivo da consulta preenchido e tipo de atendimento "Demanda Espontânea" ou "Consulta no Dia".
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> Atendimento Individual · Campo "Tipo de Atendimento: Demanda Espontânea".
                    </div>
                </div>

                <!-- C2 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C2</span>
                        <span class="text-[11px] font-semibold text-teal-700">Puericultura</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Desenvolvimento Infantil</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Consultas de puericultura realizadas em crianças até 2 anos de idade com registro do crescimento (peso e altura), estado nutricional e avaliação do estado vacinal.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> Antropometria (Peso e Altura) + Campo "Vacinação em Dia" + CIAP-2 A98 / CID-10 Z76.2.
                    </div>
                </div>

                <!-- C3 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C3</span>
                        <span class="text-[11px] font-semibold text-teal-700">Maternidade Segura</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Cuidado à Gestante e Puérpera</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Mínimo de 6 consultas de pré-natal com a primeira realizada até a 12ª semana de gestação, realização de testes rápidos para Sífilis e HIV, e consulta no puerpério realizada até o 42º dia após o parto.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> RMP Gestante (DUM/DPP preenchidos) + Exames Rápidos (Sífilis e HIV) + Consulta Puerperal (CIAP-2 W90/W91).
                    </div>
                </div>

                <!-- C4 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C4</span>
                        <span class="text-[11px] font-semibold text-teal-700">Doenças Crônicas</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Cuidado à Pessoa com Diabetes</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Consulta médica ou de enfermagem para acompanhamento do diabetes com solicitação ou avaliação do exame de Hemoglobina Glicada (HbA1c) no período de referência.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> Problema/Condição Avaliada (CIAP-2 T89/T90 ou CID-10 E10-E14) + Registro de Exame de Hemoglobina Glicada.
                    </div>
                </div>

                <!-- C5 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C5</span>
                        <span class="text-[11px] font-semibold text-teal-700">Aferição de PA</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Cuidado à Pessoa com Hipertensão</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Consulta médica ou de enfermagem para acompanhamento da hipertensão arterial sistêmica com aferição e registro obrigatório dos valores da Pressão Arterial (sistólica e diastólica).
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> Problema/Condição Avaliada (CIAP-2 K86/K87 ou CID-10 I10) + Campo "Sinais Vitais: Pressão Arterial".
                    </div>
                </div>

                <!-- C6 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C6</span>
                        <span class="text-[11px] font-semibold text-teal-700">Saúde do Idoso</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Cuidado à Pessoa Idosa</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Avaliação multidimensional da pessoa idosa (60 anos ou mais) abordando a funcionalidade global, estratificação de vulnerabilidade e histórico de quedas.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> RMP Pessoa Idosa · Avaliação da Autonomia/Independência + CIAP-2 A98 / CID-10 Z00.0.
                    </div>
                </div>

                <!-- C7 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3 md:col-span-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">C7</span>
                        <span class="text-[11px] font-semibold text-teal-700">Rastreamento Oncológico</span>
                    </div>
                    <h4 class="text-sm font-bold text-ink">Cuidado à Saúde da Mulher (Citopatológico)</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Realização de exame citopatológico do colo do útero em mulheres de 25 a 64 anos de idade no intervalo recomendado de até 3 anos, conforme diretrizes do INCA/Ministério da Saúde.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Onde registrar:</strong> Módulo de Procedimentos ou Atendimento Individual · Procedimento SIGTAP 02.03.01.008-6 (Coleta de citopatológico).
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- CONTEÚDO 2: SAÚDE BUCAL (eSB) -->
    @if ($activeCategory === 'esb')
        <div class="space-y-4 animate-fade-in">
            <div class="flex items-center justify-between border-b border-line pb-3">
                <div>
                    <h3 class="text-base font-bold text-ink">Equipe de Saúde Bucal (eSB)</h3>
                    <p class="text-xs text-muted">Orientações de preenchimento do prontuário odontológico e indicadores B1 a B6</p>
                </div>
                <a
                    href="https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/equipeesb"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900 transition"
                >
                    <span>Ver no site oficial</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- B1 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B1</span>
                    <h4 class="text-sm font-bold text-ink">Primeira Consulta Odontológica Programática</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Identificação de necessidades em saúde bucal, anamnese odontológica e elaboração do plano de tratamento individual.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Atendimento Odontológico · Campo "Tipo de Consulta: Primeira Consulta Odontológica Programática".
                    </div>
                </div>

                <!-- B2 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B2</span>
                    <h4 class="text-sm font-bold text-ink">Proporção de Tratamentos Odontológicos Concluídos</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Conclusão integral do plano de tratamento odontológico acordado no início do cuidado clínico do paciente.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Atendimento Odontológico · Campo "Conduta: Tratamento Concluído".
                    </div>
                </div>

                <!-- B3 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B3</span>
                    <h4 class="text-sm font-bold text-ink">Razão entre Exodontias e Procedimentos Preventivos</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Estímulo a intervenções curativas conservadoras e preventivas, reduzindo a proporção de perdas dentárias precoces.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Odontograma + Procedimentos SIGTAP de prevenção e restauração.
                    </div>
                </div>

                <!-- B4 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B4</span>
                    <h4 class="text-sm font-bold text-ink">Escovação Dental Supervisionada</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Ações educativas e práticas de higiene bucal realizadas em escolas, creches, unidades de saúde ou espaços comunitários.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Ficha de Atividade Coletiva · Prática em Saúde Bucal: "Escovação Supervisionada".
                    </div>
                </div>

                <!-- B5 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B5</span>
                    <h4 class="text-sm font-bold text-ink">Procedimentos Odontológicos Preventivos</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Aplicação tópica de flúor em gel/verniz, selantes dentários e profilaxia/remoção de biofilme dental.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Procedimentos no atendimento odontológico individual.
                    </div>
                </div>

                <!-- B6 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">B6</span>
                    <h4 class="text-sm font-bold text-ink">Tratamento Restaurador Atraumático (ART)</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Uso de técnicas minimamente invasivas de remoção de tecido cariado com instrumentos manuais e restauração com ionômero de vidro.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Odontograma · Seleção da face/dente e procedimento de Tratamento Restaurador Atraumático.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- CONTEÚDO 3: EQUIPES EMULTI -->
    @if ($activeCategory === 'emulti')
        <div class="space-y-4 animate-fade-in">
            <div class="flex items-center justify-between border-b border-line pb-3">
                <div>
                    <h3 class="text-base font-bold text-ink">Equipes Multiprofissionais (eMulti)</h3>
                    <p class="text-xs text-muted">Orientações de registro para atendimentos compartilhados, matriciamento e indicadores M1 e M2</p>
                </div>
                <a
                    href="https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/equipeemulti"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900 transition"
                >
                    <span>Ver no site oficial</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- M1 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">M1</span>
                    <h4 class="text-sm font-bold text-ink">Média de Atendimentos por Pessoa</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Atendimentos individuais ou compartilhados realizados pelos profissionais das categorias eMulti (psicologia, fisioterapia, nutrição, serviço social, farmácia, etc.) vinculados às equipes da APS.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Atendimento Individual · Identificação do CBO e seleção de atendimento individual ou compartilhado com eSF.
                    </div>
                </div>

                <!-- M2 -->
                <div class="rounded-3xl border border-line bg-white p-5 shadow-sm space-y-3">
                    <span class="rounded-xl bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-900 font-mono">M2</span>
                    <h4 class="text-sm font-bold text-ink">Ações Interprofissionais e Coletivas</h4>
                    <p class="text-xs text-muted leading-relaxed">
                        Participação em reuniões de matriciamento, discussão de projetos terapêuticos singulares (PTS) e grupos de promoção à saúde e prevenção.
                    </p>
                    <div class="rounded-xl bg-slate-50 p-2.5 text-[11px] text-slate-700 border border-slate-100">
                        <strong>Registro:</strong> Ficha de Atividade Coletiva (Matriciamento de Equipes ou Reunião de Equipe).
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- CONTEÚDO 4: REGRAS ESTRUTURANTES DE CADASTRO -->
    @if ($activeCategory === 'cadastros')
        <div class="space-y-4 animate-fade-in">
            <div class="border-b border-line pb-3">
                <h3 class="text-base font-bold text-ink">Cadastros Individuais e Domiciliares (MICI e MICDT)</h3>
                <p class="text-xs text-muted">A base estruturante para o reconhecimento do território e financiamento federal da APS</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-teal-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <h4 class="text-sm font-bold text-ink">Cadastro Individual do Cidadão (MICI)</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed">
                        O cadastro individual identifica as condições sociodemográficas e de saúde de cada cidadão. O Ministério da Saúde reforça que o cadastro individual é ferramenta disponível para <strong>todos os profissionais da equipe</strong> (médicos, enfermeiros, dentistas e agentes de saúde), evitando duplicidades e fortalecendo o vínculo longitudinal.
                    </p>
                    <div class="rounded-2xl bg-slate-50 p-3 text-xs text-slate-700 border border-slate-200">
                        <strong>Validade:</strong> Um cadastro é considerado <em>atualizado</em> quando foi realizado ou atualizado nos últimos 24 meses (por visita ou consulta clínica).
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-white p-6 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-teal-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <h4 class="text-sm font-bold text-ink">Cadastro Domiciliar e Territorial (MICDT)</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed">
                        Mapeia as características físicas, saneamento e condições de moradia de cada domicílio sob responsabilidade da equipe de saúde da família. Permite a territorialização precisa e o cruzamento dos lares atendidos no município.
                    </p>
                    <div class="rounded-2xl bg-slate-50 p-3 text-xs text-slate-700 border border-slate-200">
                        <strong>Papel dos ACS:</strong> Levantamento contínuo das microáreas, localização geográfica e identificação de vulnerabilidades ambientais.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Card de Boas Práticas de Envio -->
    <div class="rounded-3xl border border-line bg-white p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-800 border border-teal-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-ink">Regras de Ouro para Não Perder Produção</h4>
                <ul class="text-xs text-muted space-y-1 list-disc list-inside leading-relaxed">
                    <li><strong>Registro no momento do atendimento:</strong> Inserir a conduta e procedimentos preferencialmente durante a consulta clínica.</li>
                    <li><strong>Sincronização diária:</strong> Assegurar que os computadores das UBS transmitam a produção para o servidor central do e-SUS PEC no encerramento do expediente.</li>
                    <li><strong>INE e CNES corretos:</strong> Conferir se todos os profissionais estão vinculados com o CNES e o INE homologados no CNES do Ministério da Saúde.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
