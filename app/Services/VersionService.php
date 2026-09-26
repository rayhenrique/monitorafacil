<?php

namespace App\Services;

use App\Models\User;

class VersionService
{
    public const CURRENT_VERSION = 'v1.32.0';

    public const CURRENT_RELEASE_DATE = '25/09/2026';

    /**
     * Retorna a versão mais recente do sistema.
     */
    public static function getLatestVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    /**
     * Verifica se o modal de novidades deve ser exibido para o usuário autenticado.
     */
    public static function shouldShowModal(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->last_seen_version !== self::CURRENT_VERSION;
    }

    /**
     * Marca a versão atual como visualizada pelo usuário.
     */
    public static function markAsSeen(User $user): void
    {
        $user->forceFill([
            'last_seen_version' => self::CURRENT_VERSION,
        ])->save();
    }

    /**
     * Retorna os detalhes da versão mais recente.
     *
     * @return array<string, mixed>
     */
    public static function getLatestRelease(): array
    {
        $releases = self::getAllReleases();

        return $releases[0];
    }

    /**
     * Retorna todas as versões lançadas em ordem decrescente (da mais recente para a mais antiga).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAllReleases(): array
    {
        return [
            [
                'version' => 'v1.32.0',
                'date' => '25/09/2026',
                'badge' => 'Versão Atual',
                'title' => 'Exibição da Data e Hora da Última Atualização no Menu Lateral e Rastreamento Analítico de Indicadores',
                'summary' => 'Inclusão da data e hora da última consolidação/processamento dos indicadores diretamente no card de status da "Base e-SUS PEC" no menu lateral (sidebar desktop e menu gaveta mobile). O rastreamento unificado consulta de forma resiliente as fontes de dados analíticos (Snapshots de Saúde Bucal B1 a B6, Saúde da Família C1 a C7, Coortes individuais C2 a C7, Logs de Sincronização do DW e Rotina Noturna). O sistema mantém cache otimizado de 60 segundos com invalidação imediata sob demanda ao término de qualquer processamento, exibindo a hora exata no fuso municipal com tooltip de precisão em segundos e suporte ao menu recolhido.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Exibição da linha "Última Atualização" no card de status do e-SUS PEC no menu lateral com data e horário no padrão brasileiro (dd/mm/aaaa hh:mm) e fuso municipal.'],
                    ['type' => 'melhoria', 'text' => 'Rastreamento analítico unificado em SettingsService::getLastIndicatorsProcessedAt() abrangendo Snapshots de Saúde da Família, Saúde Bucal, Coortes C2-C7 e SyncLogs.'],
                    ['type' => 'melhoria', 'text' => 'Cache de alto desempenho com chave `indicators:last_processed_at` e invalidação automática e em tempo real em todas as esteiras de processamento.'],
                    ['type' => 'interface', 'text' => 'Disponível tanto no menu lateral desktop quanto na gaveta mobile, com suporte a tooltip detalhado e modo compacto quando a barra lateral é recolhida.'],
                    ['type' => 'testes', 'text' => 'Cobertura completa de testes automatizados unitários e de interface Blade em SettingsTest.'],
                ],
            ],
            [
                'version' => 'v1.31.0',
                'date' => '25/09/2026',
                'badge' => 'Anterior',
                'title' => 'Rotina Noturna Automática das 03:00 (20 Etapas), Configuração no Módulo "Processar Dados" e Revisão do Deploy',
                'summary' => 'Implementação da esteira automatizada para execução noturna diária às 03:00 da manhã, configurável diretamente na interface de "Processar Dados" (/configuracoes/processar-dados). A rotina executa uma sequência oficial de 20 etapas: atualização Git (git pull), instalação de dependências (composer install), compilação de assets (npm run build), migrações (migrate), sincronização nominal CVAT, consolidação de todos os indicadores (C1 a C7, eSB Saúde Bucal B1 a B6 com --scope=oral-health e esus:process-oral-health), publicação de assets Livewire e otimização completa de caches (optimize:clear, config, route, view, queue). Inclui comando Artisan monitora:nightly-routine, script bash otimizado scripts/nightly-sync.sh, agendamento no routes/console.php, visualizador de log em tempo real e atualização dos scripts de deploy (deploy.sh e scripts/deploy.sh).',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Painel de configuração da Rotina Noturna no módulo "Processar Dados" com alternância de status, seletor de horário (padrão 03:00), limites de memória, disparador sob demanda e visualizador de log (nightly-sync.log).'],
                    ['type' => 'novo', 'text' => 'Comando Artisan `php artisan monitora:nightly-routine` e script bash dedicado `scripts/nightly-sync.sh` com 20 etapas atômicas e registro de status/duração no banco de dados.'],
                    ['type' => 'melhoria', 'text' => 'Atualização dos scripts `deploy.sh` e `scripts/deploy.sh` para 20 etapas oficiais, integrando tanto o escopo `oral-health` no `esus:process-data` quanto o `esus:process-oral-health`.'],
                    ['type' => 'novo', 'text' => 'Integração com o Agendador do Laravel em `routes/console.php` disparando às 03:00 com respeito dinâmico às preferências do gestor.'],
                    ['type' => 'melhoria', 'text' => 'Documentação completa de agendamento Crontab no `deploy.md` e suíte de testes automatizados em `SettingsTest.php`.'],
                ],
            ],
            [
                'version' => 'v1.30.0',
                'date' => '25/09/2026',
                'badge' => 'Anterior',
                'title' => 'Controle de Acesso por Perfis (Administrador e Operador UBS/CNES) e Escopo de Dados por Unidade de Saúde',
                'summary' => 'Implementação completa do sistema de perfis de usuário com controle de acesso granular e restrição de escopo de visualização por UBS/CNES. Dois níveis de acesso passam a operar no sistema: Administrador (acesso municipal irrestrito a todas as UBS, equipes e telas de configuração) e Operador (vinculado obrigatoriamente a uma Unidade Básica de Saúde / CNES, com visualização restrita a todas as equipes e munícipes daquela UBS). Operadores contam com aplicação automática do filtro de CNES no Painel Geral, Vínculo e Acompanhamento Territorial, Indicadores de Saúde da Família (C1 a C7) e Saúde Bucal (B1 a B6), impedindo a visualização de unidades e munícipes de outras unidades. Além disso, as rotas de configurações do sistema (/configuracoes/*) e links de navegação ficam restritos exclusivamente a Administradores via middleware EnsureUserIsAdmin.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Perfis de Usuário (RBAC): Administrador (gestão municipal completa e configurações) e Operador (vinculado a uma UBS / CNES específica).'],
                    ['type' => 'segurança', 'text' => 'Middleware EnsureUserIsAdmin e proteção em /configuracoes/* com resposta HTTP 403 Forbidden para acessos não autorizados e ocultação condicional nos menus.'],
                    ['type' => 'novo', 'text' => 'Cadastro e Gestão de Usuários com seletor interativo de perfil e vinculação mandatória da UBS/CNES para operadores com busca das unidades reais do município.'],
                    ['type' => 'melhoria', 'text' => 'Escopo de dados restrito e automático para Operadores no Painel Geral, Vínculo Territorial, Saúde da Família (C1 a C7) e Saúde Bucal (B1 a B6), com badge indicativo da unidade no topo e rodapé.'],
                    ['type' => 'melhoria', 'text' => 'Atualização metodológica do menu lateral para "Vínculo e Acomp." e suíte completa de testes automatizados de controle de acesso e escopo de dados.'],
                ],
            ],
            [
                'version' => 'v1.29.0',
                'date' => '25/09/2026',
                'badge' => 'Anterior',
                'title' => 'Integração da Saúde Bucal (B1 a B6) no Módulo "Processar Dados" e Automação CLI',
                'summary' => 'Inclusão oficial do módulo de Saúde Bucal (Indicadores B1 a B6 - eSB) na central de "Processar Dados" (/configuracoes/processar-dados) e no comando CLI esus:process-data. Agora o gestor e os profissionais podem disparar sob demanda a consolidação de Saúde Bucal com um clique em um card visual estilizado (azul celeste sky-50 com ícone odontológico oficial e badge B1 a B6), acompanhando o progresso em tempo real. O backend processa em lote as 19 eSB, indicadores mensais e a relação geral nominal de 38.509 munícipes. O comando de linha de comando agora suporta --scope=oral-health e o processamento geral (--scope=all) consolida automaticamente a Saúde Bucal junto aos indicadores C1 a C7, MICI e MICDT.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Card dedicado de Saúde Bucal (B1 a B6) na tela "Processar Dados" com feedback visual de progresso estimado e acionamento Livewire sob demanda.'],
                    ['type' => 'melhoria', 'text' => 'Grid de escopos rebalanceado para 10 cards em 2 linhas simétricas de 5 cards (2xl:grid-cols-5), otimizando a responsividade.'],
                    ['type' => 'novo', 'text' => 'Suporte ao parâmetro `--scope=oral-health` no comando Artisan `php artisan esus:process-data` para execuções automatizadas e via terminal.'],
                    ['type' => 'melhoria', 'text' => 'Inclusão da Saúde Bucal na rotina geral completa (`--scope=all`), consolidando B1 a B6, 19 eSB e 38.509 cidadãos nominais na rotina noturna.'],
                    ['type' => 'melhoria', 'text' => 'Suíte completa de testes automatizados em SettingsTest cobrindo o acionamento Livewire e execução CLI do escopo oral-health.'],
                ],
            ],
            [
                'version' => 'v1.28.0',
                'date' => '25/09/2026',
                'badge' => 'Anterior',
                'title' => 'Submódulos de Busca Geral Nominal e Dashboard Mensal de Equipes da Saúde Bucal (eSB), e Avaliação Estrita das 19 eSB',
                'summary' => 'Lançamento de dois novos submódulos analíticos de Saúde Bucal (eSB): a Busca Geral Nominal de Cidadãos (/saude-bucal/busca-nominal) com 38.509 munícipes, acompanhamento individual dos 6 indicadores odontológicos (B1 a B6), situação cadastral e linha do tempo de atendimentos; e o Dashboard Mensal de Equipes (/saude-bucal/mensal) com visão executiva das 19 equipes eSB (001 a 021) por competência mensal (M5 a M12), cards com pontuações, denominadores, barras de progresso proporcionais por nível de desempenho e modal de busca avançada. Adicionalmente, a regra de consolidação de Saúde Bucal foi ajustada para filtrar e avaliar estritamente equipes odontológicas eSB (tipos 87 e 88), eliminando equipes eSF/eAP/USF/PACS dos indicadores B1 a B6.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Submódulo de Busca Geral Nominal de Saúde Bucal (/saude-bucal/busca-nominal) com 38.509 munícipes, filtros por microárea, equipe e cumprimento de cada indicador (B1 a B6), mascaramento LGPD, exportação CSV e modal de auditoria clínica.'],
                    ['type' => 'novo', 'text' => 'Submódulo Dashboard Mensal de Equipes (/saude-bucal/mensal) com cards analíticos por eSB, evolução mensal (M5 a M12), pontuação, denominador, barras de progresso coloridas por faixa, busca avançada e exportação CSV.'],
                    ['type' => 'melhoria', 'text' => 'Filtro e consolidação estrita das 19 equipes de Saúde Bucal (eSB 001 a eSB 021) nos indicadores B1 a B6, excluindo equipes de Saúde da Família (eSF) da avaliação odontológica.'],
                    ['type' => 'melhoria', 'text' => 'Comando Artisan `php artisan esus:process-oral-health` integrando em execução atômica os snapshots quadrimestrais, evolução mensal e a sincronização nominal de todos os munícipes.'],
                    ['type' => 'melhoria', 'text' => 'Navegação lateral desktop e mobile atualizada com links dedicados aos novos submódulos e badges oficiais ("PEC" e "Mensal"), com conformidade integral à Política de Rodapé Único.'],
                ],
            ],
            [
                'version' => 'v1.27.0',
                'date' => '25/09/2026',
                'badge' => 'Anterior',
                'title' => 'Módulo de Saúde Bucal (eSB), Indicadores B1 a B6, Pontuação Municipal (10 pts) e Avaliação Estrita das Equipes eSB',
                'summary' => 'Implementação integral do novo módulo oficial de Saúde Bucal (eSB) no Monitora Fácil, em total conformidade com as Notas Metodológicas do Ministério da Saúde. O módulo contempla os 6 indicadores odontológicos da Atenção Primária: B1 (Primeira Consulta Odontológica Programática - Peso 2.0x), B2 (Tratamento Odontológico Concluído em até 12 meses - Peso 2.0x), B3 (Taxa de Exodontia de Dentes Permanentes - Polaridade Menor é Melhor - Peso 2.0x), B4 (Ação Coletiva de Escovação Supervisionada em crianças de 6 a 12 anos - Peso 1.0x), B5 (Procedimentos Odontológicos Preventivos Individuais - Faixa Central - Peso 2.0x) e B6 (Tratamento Restaurador Atraumático ART/TRA - Peso 1.0x), somando 10,0 pontos na pontuação municipal. A avaliação contempla estritamente as 19 equipes de Saúde Bucal (eSB 001 a eSB 021) do município, excluindo equipes de Saúde da Família (eSF) da avaliação odontológica. Inclui painel geral municipal (/saude-bucal), telas detalhadas (/saude-bucal/{b1..b6}) com layout padronizado de duas abas (Aba 1: Resumo das Equipes com 4 quadrantes e Aba 2: Busca Ativa Nominal com dados clínicos reais), comando Artisan esus:process-oral-health e suíte completa de testes automatizados.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Novo módulo de Saúde Bucal (eSB) com os 6 indicadores oficiais: B1 (1ª Consulta, Peso 2.0x), B2 (Trat. Concluído, Peso 2.0x), B3 (Taxa Exodontia, Menor-Melhor, Peso 2.0x), B4 (Escovação Supervisionada 6-12a, Peso 1.0x), B5 (Proced. Preventivos, Peso 2.0x) e B6 (Restauração ART/TRA, Peso 1.0x), somando 10,0 pontos municipais.'],
                    ['type' => 'melhoria', 'text' => 'Avaliação e consolidação estrita das 19 Equipes de Saúde Bucal (eSB 001 a eSB 021) ativas no município, com filtro que elimina equipes eSF/eAP/USF/PACS da avaliação odontológica.'],
                    ['type' => 'novo', 'text' => 'Painel Geral de Saúde Bucal (/saude-bucal) com Hero Card executivo, cálculo da Pontuação Municipal (ISB), status global e cards comparativos dos 6 indicadores com barras de progresso e contagem de equipes por classificação.'],
                    ['type' => 'novo', 'text' => 'Telas Analíticas Detalhadas (/saude-bucal/{b1..b6}) com Aba 1 ("Resumo das Equipes") com os 4 quadrantes oficiais e tabela padrão de 5 colunas, e Aba 2 ("Busca Ativa") com paginação debounced e auditoria clínica.'],
                    ['type' => 'novo', 'text' => 'Submódulo de Busca Geral Nominal de Saúde Bucal (/saude-bucal/busca-nominal) com 38.509 cidadãos municipais, cards KPI superiores (B1 a B6), alternância de colunas, mascaramento LGPD, modal de Busca Avançada multicritério e visualização detalhada do histórico clínico odontológico.'],
                    ['type' => 'novo', 'text' => 'Submódulo Dashboard Mensal de Equipes da Saúde Bucal (/saude-bucal/mensal) com visão unificada das 19 equipes eSB por competência mensal (M5 a M12), cards executivos com os 6 indicadores B1 a B6, pontuações, denominadores, barras de progresso coloridas, modal de Busca Avançada e exportação CSV.'],
                    ['type' => 'melhoria', 'text' => 'Motor de extração do DW do e-SUS PEC (B1DwService a B6DwService) de alta performance e comando Artisan `php artisan esus:process-oral-health` com alocação otimizada de memória e transações atômicas.'],
                    ['type' => 'melhoria', 'text' => 'Navegação lateral desktop e mobile atualizada com o novo grupo expansível "Saúde Bucal" e badges metodológicos, preservando 100% a Política de Rodapé Único.'],
                ],
            ],
            [
                'version' => 'v1.26.0',
                'date' => '24/09/2026',
                'badge' => 'Anterior',
                'title' => 'Indicador C7 (Cuidado da Mulher na Prevenção do Câncer), 4 Boas Práticas Clínicas (A–D, 100 pts), Peso 2.0x e Busca Ativa',
                'summary' => 'Implementação integral do Indicador C7 (Cuidado da Mulher na Prevenção do Câncer do Colo do Útero e de Mama na Atenção Primária) em estrita conformidade com a Nota Metodológica oficial e Nota Técnica nº 06/2025-CVAT. O módulo contempla o acompanhamento de 16.869 mulheres e homens transgêneros vinculados às 19 equipes da APS municipal (idade de 9 a 69 anos), aferição das 4 Boas Práticas Clínicas oficiais somando 100 pontos: Rastreamento do Câncer do Colo do Útero (Prática A, 25 a 64 anos, citopatológico em 36 meses ou molecular DNA-HPV em 60 meses, 20 pts); Vacinação contra HPV (Prática B, 9 a 14 anos, pelo menos 1 dose registrada na vida, 30 pts); Atenção à Saúde Sexual e Reprodutiva (Prática C, 14 a 69 anos, atendimento médico/enfermagem nos últimos 12 meses, 30 pts); e Rastreamento do Câncer de Mama (Prática D, 50 a 69 anos, mamografia bilateral nos últimos 24 meses, 20 pts). O indicador possui Peso 2.0x no Componente III (até 2,00 pontos de pontuação final). Inclui extração de alto desempenho no DW e-SUS PEC (resolvida em 15.8 segundos para toda a rede), busca ativa nominal otimizada, auditoria clínica individual da mulher, filtros avançados, exportação CSV, card dedicado no módulo Processar Dados e expansão dos scripts de deploy para 18 etapas.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Indicador C7 com as 4 Boas Práticas Clínicas oficiais somando 100 pontos: Colo do Útero (A, 20 pts), Vacina HPV (B, 30 pts), Saúde Sexual e Reprodutiva (C, 30 pts) e Câncer de Mama (D, 20 pts) conforme Nota Metodológica C7.'],
                    ['type' => 'novo', 'text' => 'Componente III com Peso 2.0x (até 2,00 pontos na pontuação final do município e de cada equipe) conforme Nota Técnica nº 06/2025-CVAT.'],
                    ['type' => 'melhoria', 'text' => 'Motor de extração do DW e-SUS PEC com pré-resolução dimensional de chaves primárias, consolidando 16.869 mulheres e 19 equipes em apenas 15.8 segundos.'],
                    ['type' => 'novo', 'text' => 'Busca Ativa Nominal com 16.869 mulheres e homens trans vinculados, paginação rápida, filtros por faixa etária, unidade, equipe, microárea, cumprimento de cada prática e exportação em CSV com UTF-8.'],
                    ['type' => 'novo', 'text' => 'Modal de Auditoria Clínica Individual da Mulher com linha do tempo clínica, datas de exames, tipo de imunobiológico contra HPV e diagnósticos/procedimentos de saúde reprodutiva.'],
                    ['type' => 'melhoria', 'text' => 'Módulo Processar Dados com Card 8 dedicado para acionamento pontual do Indicador C7 (--scope=c7) e integração no botão "Processar Tudo".'],
                    ['type' => 'melhoria', 'text' => 'Scripts de deploy (deploy.sh e scripts/deploy.sh) expandidos para 18 etapas completas com alocação otimizada de memória e documentação atualizada em deploy.md.'],
                ],
            ],
            [
                'version' => 'v1.25.1',
                'date' => '24/09/2026',
                'badge' => 'Anterior',
                'title' => 'Sincronização dos Cards de Vínculo e Acompanhamento na Visão Geral com o Módulo CVAT Nominal (NT 30/2025)',
                'summary' => 'Ajuste e sincronização da seção de Vínculo e Acompanhamento na tela Visão Geral com as métricas reais auditadas da Relação Nominal da NT 30/2025 (módulo Vínculo e Acompanhamento Territorial). Os cards passam a exibir a competência real (2026 / M09), Total MICI atualizados (36.705 com 99,20% de 37.002), Total MICI + MICDT atualizados (35.939 com 99,04% de 36.289 com MICDT) e contagem exata de desatualizados (297 e 116), eliminando dados legados divergentes e mantendo consistência total com a tabela oficial cvat_nominal_metrics e consolidation_registrations.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Consistência total entre os cards de Vínculo e Acompanhamento da Visão Geral e a Relação Nominal do CVAT (37.002 cidadãos reais no município).'],
                    ['type' => 'melhoria', 'text' => 'Card Total MICI atualizados exibe 36.705 (99,20%) com 37.002 no total geral e 297 desatualizados na competência real 2026 / M09.'],
                    ['type' => 'melhoria', 'text' => 'Card Total MICI + MICDT atualizados exibe 35.939 (99,04%) com 36.289 no total geral com domicílio e 116 desatualizados na competência real 2026 / M09.'],
                    ['type' => 'correcao', 'text' => 'Atualização automática e bidirecional de ConsolidationRegistration nas rotinas de processamento de dados do e-SUS PEC e consolidação do CVAT.'],
                ],
            ],
            [
                'version' => 'v1.25.0',
                'date' => '24/09/2026',
                'badge' => 'Anterior',
                'title' => 'Indicador C6 (Cuidado da Pessoa Idosa na APS), 4 Boas Práticas Clínicas (A–D, 100 pts) e Busca Ativa Nominal',
                'summary' => 'Implementação integral do Indicador C6 (Cuidado da Pessoa Idosa na APS) conforme a Nota Metodológica oficial e Nota Técnica nº 06/2025-CVAT. O módulo contempla o acompanhamento da coorte de 5.780 pessoas com 60 anos ou mais vinculadas às 19 equipes ativas da APS municipal, aferição das 4 Boas Práticas Clínicas oficiais (25 pontos cada = 100 pontos): Consulta Médica/Enfermagem nos últimos 12 meses (A, 25 pts), Antropometria com Peso e Altura na mesma data (B, 25 pts), no mínimo 2 Visitas Domiciliares de ACS com intervalo mínimo de 30 dias (C, 25 pts, com isenção eAP) e Vacinação de Influenza nos últimos 12 meses (D, 25 pts). Inclui busca ativa nominal, auditoria clínica individual, filtros avançados e integração ao pipeline do e-SUS PEC DW.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Indicador C6 com as 4 Boas Práticas Clínicas oficiais somando 100 pontos (25 pts cada) conforme Nota Metodológica C6.'],
                    ['type' => 'melhoria', 'text' => 'Isenção da Prática C para equipes de Atenção Primária (eAP tipo 76) com normalização automática para a base 100 (fator 100/75 sem D).'],
                    ['type' => 'melhoria', 'text' => 'Auditoria de vacina Influenza nos últimos 12 meses a partir de tb_fat_vacinacao_vacina e registro de peso e altura simultâneos em atendimentos individuais.'],
                    ['type' => 'novo', 'text' => 'Busca Ativa Nominal da Pessoa Idosa com 5.780 registros 100% reais, filtros por faixa etária (60-69, 70-79, 80+), raça/cor, status de cada prática, exportação em CSV com BOM UTF-8 e ficha clínica individual.'],
                    ['type' => 'novo', 'text' => 'Modal de Auditoria Clínica Individual da Pessoa Idosa com detalhamento das 4 práticas, dados antropométricos, vacina e visitas de ACS.'],
                    ['type' => 'melhoria', 'text' => 'Módulo Processar Dados com card dedicado e acionamento pontual do C6, e scripts de deploy (deploy.sh e scripts/deploy.sh) expandidos para 17 etapas completas e documentação atualizada em deploy.md.'],
                    ['type' => 'melhoria', 'text' => 'Processamento analítico integrado via `php artisan esus:process-data --scope=c6` e consolidação no Painel Executivo e Visão Geral.'],
                ],
            ],
            [
                'version' => 'v1.24.0',
                'date' => '24/09/2026',
                'badge' => 'Anterior',
                'title' => 'Indicador C5 (Hipertensão Arterial Sistêmica), 4 Boas Práticas Clínicas (A–D, 100 pts) e Busca Ativa Nominal',
                'summary' => 'Implementação integral do Indicador C5 (Cuidado da Pessoa com Hipertensão na APS) conforme a Nota Metodológica oficial e Nota Técnica nº 08/2026-DEAPS/SAPS/MS. O módulo inclui o cálculo auditado das 4 Boas Práticas Clínicas (25 pontos cada = 100 pontos), exclusão normativa estrita de ACS para aferição de Pressão Arterial, normalização de equipes eAP (fator 100/75 sem D), extração de dados 100% reais do DW e-SUS PEC, persistência de snapshots e coorte nominal de 11.032 hipertensos com busca ativa e ficha clínica individual.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Indicador C5 com as 4 Boas Práticas Clínicas oficiais: Consulta Médica/Enfermagem semestral (A, 25 pts), Aferição de PA semestral por profissional habilitado (B, 25 pts), Antropometria Peso + Altura anual (C, 25 pts) e 2 Visitas Domiciliares de ACS anual com intervalo ≥ 30 dias (D, 25 pts).'],
                    ['type' => 'correcao', 'text' => 'Cumprimento estrito da regra normativa da Nota Metodológica C5 (Quadro 03 e Rodapé 4): CBO 5151-05 (ACS) foi expressamente excluído de pontuar a prática de Aferição de Pressão Arterial.'],
                    ['type' => 'melhoria', 'text' => 'Tratamento de equipes eAP (tipo 76): a prática D (Visitas de ACS) não condiciona o resultado e a pontuação é automaticamente normalizada para a base 100 (fator 100/75).'],
                    ['type' => 'novo', 'text' => 'Busca Ativa e Lista Nominal de Hipertensos com filtros rápidos (CNS, CPF, Nome, CNES, INE, Microárea), customizador de colunas visíveis, badges circulares de cumprimento das 4 práticas e exportação CSV.'],
                    ['type' => 'novo', 'text' => 'Modal de Auditoria Clínica Individual da Pessoa com Hipertensão com status do e-SUS PEC, histórico de diagnósticos (CIAP-2/CID-10) e detalhamento linha a linha das 4 práticas.'],
                    ['type' => 'melhoria', 'text' => 'Integração completa do C5 na tela de Processar Dados (card dedicado e botão de processamento pontual) e consolidação no Visão Geral e Painel Executivo.'],
                ],
            ],
            [
                'version' => 'v1.23.16',
                'date' => '23/09/2026',
                'badge' => 'Anterior',
                'title' => 'Indicador C4 (Diabetes Mellitus), Extração Real de Exames, Resumo de 19 Equipes e Deploy Atualizado',
                'summary' => 'Implementação integral do Indicador C4 (Cuidado da Pessoa com Diabetes Mellitus na APS) com 6 Boas Práticas Clínicas (A–F, 100 pontos), extração no e-SUS PEC DW de exames laboratoriais de Hemoglobina Glicada (HbA1c) e procedimentos clínicos, correção do agrupamento de equipes por INE (19 equipes oficiais sem duplicidade), botão Processar Tudo com agendamento do CVAT e deploy padronizado em 15 etapas.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Indicador C4 com 6 Boas Práticas Clínicas oficiais (A a F somando 100 pontos) conforme Nota Metodológica C4 e Nota Técnica nº 08/2026.'],
                    ['type' => 'melhoria', 'text' => 'Captura de exames laboratoriais de Hemoglobina Glicada no DW (tb_fat_atd_ind_exames e tb_fat_atd_ind_procedimentos), elevando a cobertura real para 81,67% na coorte municipal.'],
                    ['type' => 'correcao', 'text' => 'Agrupamento estrito de equipes por INE no C2, C3 e C4, eliminando duplicações decorrentes de CNES histórico divergente e consolidando exatamente as 19 equipes ativas.'],
                    ['type' => 'novo', 'text' => 'Lista Nominal e Coorte de Diabéticos com busca rápida, modal de busca avançada com filtros por práticas e modal da ficha clínica individual.'],
                    ['type' => 'melhoria', 'text' => 'Botão "Processar Tudo" no módulo Processar Dados executa consolidação geral (C1 a C4, MICI e MICDT) e agenda a sincronização nominal do CVAT na fila.'],
                    ['type' => 'melhoria', 'text' => 'Scripts de deploy (deploy.sh e scripts/deploy.sh) com 15 etapas, -d memory_limit=1024M e documentação do serviço systemd de fila em deploy.md.'],
                ],
            ],
            [
                'version' => 'v1.23.15',
                'date' => '23/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Indicador C3 (Gestação e Puerpério), Processamento e Deploy Automatizado',
                'summary' => 'Implementação integral do Indicador C3 (Cuidado na Gestação e Puerpério) da Saúde da Família com 11 Boas Práticas Clínicas oficiais (A–K) somando 100 pontos e multiplicador 2.0×, lista nominal de busca ativa com coorte real de gestantes/puérperas, resumo mensal das equipes por classificação ministerial, além de suporte ao escopo C3 no comando CLI esus:process-data, auditoria no módulo Processar Dados e inclusão da etapa no deploy automatizado.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Indicador C3 com 11 Boas Práticas Clínicas (A a K, 100 pontos) e multiplicador 2.0× conforme Nota Metodológica C3 e NT 08/2026.'],
                    ['type' => 'novo', 'text' => 'Lista Nominal e Coorte de Gestantes e Puérperas com busca ativa, filtros avançados, 17 colunas de boas práticas e modal clínico de detalhes individuais.'],
                    ['type' => 'novo', 'text' => 'Subaba Resumo Mensal das Equipes no C3 da Saúde da Família com distribuição analítica por classificação ministerial e tabela expansível por equipe.'],
                    ['type' => 'melhoria', 'text' => 'Comando Artisan esus:process-data com suporte explícito ao escopo --scope=c3 e isolamento da rotina para evitar execução desnecessária de outros indicadores.'],
                    ['type' => 'interface', 'text' => 'Módulo "Processar Dados" em Configurações com card C3 assíncrono e auditoria detalhada com badges de status e ícones para sucesso, falha e ignorado.'],
                    ['type' => 'melhoria', 'text' => 'Scripts de deploy (deploy.sh e scripts/deploy.sh) atualizados para 14 etapas com consolidação automática dos dados reais do C3.'],
                ],
            ],
            [
                'version' => 'v1.23.14',
                'date' => '23/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Resumo Mensal das Equipes nos Indicadores C1 e C2 da Saúde da Família',
                'summary' => 'Implementação da aba de Resumo Mensal das Equipes no detalhe dos indicadores C1 (Mais Acesso) e C2 (Cuidado no Desenvolvimento Infantil) da Saúde da Família, apresentando hero card da competência mensal, distribuição analítica das 19 equipes por classificação (Regular, Suficiente, Bom e Ótimo) e tabela expansível com desempenho de todas as equipes avaliadas com dados 100% reais.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Aba "Resumo Mensal das Equipes" no C1 e C2 da Saúde da Família com visualização executiva consolidada de todas as 19 equipes ativas.'],
                    ['type' => 'interface', 'text' => 'Cards analíticos de Distribuição das Equipes por Classificação com percentuais proporcionais, contadores e barras de progresso temáticas por nível ministerial.'],
                    ['type' => 'melhoria', 'text' => 'Tabela expansível de Lista de Equipes por Classificação detalhando Unidade (CNES), Equipe (INE), Numerador, Denominador, Pontuação calculada e Badge oficial.'],
                    ['type' => 'recurso', 'text' => 'Alternância fluida entre visão de resumo mensal consolidado e visões analíticas por equipe e coorte nominal.'],
                ],
            ],
            [
                'version' => 'v1.23.13',
                'date' => '23/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Painel do Indicador C2 (Cuidado no Desenvolvimento Infantil) com Microdados Reais do PEC DW',
                'summary' => 'Reestruturação e alinhamento visual e de dados da tela /saude-da-familia/c2 para o Indicador C2 (Desenvolvimento Infantil) com 1.042 crianças reais extraídas do PostgreSQL do PEC e-SUS municipal, exibindo síntese das 5 Boas Práticas Clínicas (A, B, C, D, E), filtros rápidos, personalizador de colunas, busca avançada e modal de detalhes clínicos individuais.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Microdados 100% reais da coorte municipal de crianças (0 a 24 meses) extraídos do PostgreSQL do e-SUS PEC e processados conforme Nota Metodológica C2 e NT 08/2026.'],
                    ['type' => 'interface', 'text' => 'Banner superior com 3 blocos: Mês da coorte (2026 / M9), Síntese das 5 Boas Práticas Clínicas (A, B, C, D, E com quantitativos e percentuais em verde) e Denominador Total da coorte.'],
                    ['type' => 'recurso', 'text' => 'Barra de filtros de consulta rápida por CNS, CPF, Nome, CNES, INE e seletor de paginação (30 itens por padrão).'],
                    ['type' => 'recurso', 'text' => 'Menu suspenso de colunas visíveis exibindo "Colunas visíveis: 10 itens selecionados" por padrão, com suporte a toggle e restauração de colunas padrão.'],
                    ['type' => 'recurso', 'text' => 'Tabela nominal com máscaras oficiais de CNS e CPF, visualização/cópia, idade em meses, raça/cor, CNS profissional com tooltip de nome, mês da coorte, microárea, status MICI e badges coloridos para as 5 práticas (verde para cumprida, vermelho para pendente).'],
                    ['type' => 'recurso', 'text' => 'Modal de Busca Avançada interativo com filtros de equipe, microárea, cidadão, responsável, competência, profissional, faixa etária e status de cada prática clínica.'],
                    ['type' => 'recurso', 'text' => 'Modal de Detalhes Clínicos da Criança exibindo ficha completa de vínculo territorial, dados cadastrais e status individualizado das 5 boas práticas conforme Portaria GM/MS nº 3.493/2024.'],
                ],
            ],
            [
                'version' => 'v1.23.12',
                'date' => '23/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Painel do Indicador C1 (Mais Acesso Mensal) com Microdados Reais',
                'summary' => 'Reestruturação da tela de detalhe do Indicador C1 (Mais Acesso à Atenção Primária) em /saude-da-familia/c1 com microdados reais do município, abas de equipes, filtros analíticos, cabeçalho de classificação ministerial e exportação CSV/Relatório.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Exibição fiel da tela do Indicador C1 (Mensal) com todas as 19 equipes ativas do município, atendimentos programados, atendimentos espontâneos, denominador total, status de avaliação e barras de progresso.'],
                    ['type' => 'interface', 'text' => 'Cabeçalho com legenda oficial das 4 faixas de classificação (Regular <= 10% ou > 70%, Suficiente > 10% e <= 30%, Bom > 30% ou <= 50%, Ótimo > 50% ou <= 70%).'],
                    ['type' => 'recurso', 'text' => 'Subabas "Resumo por Equipe" e "Sem Equipe", permitindo acompanhamento individualizado por equipe ou de atendimentos desvinculados.'],
                    ['type' => 'recurso', 'text' => 'Filtros analíticos por Distrito, Unidade (CNES), Equipe (INE), Mês de competência, Quadrimestre e Classificação de desempenho, com botão Carregar.'],
                    ['type' => 'recurso', 'text' => 'Menu suspenso de Relatório com opções de Imprimir / Salvar em PDF e Exportação de planilha em formato CSV com codificação UTF-8 BOM e delimitador ponto-e-vírgula.'],
                ],
            ],
            [
                'version' => 'v1.23.11',
                'date' => '23/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Painel Municipal da Saúde da Família e Busca Avançada',
                'summary' => 'Reestruturação visual do Painel Municipal da Saúde da Família (C1 a C7) com agregação real de equipes por faixas de desempenho (Ótimo, Bom, Suficiente, Regular), seleção padrão do quadrimestre avaliado e seleção de períodos via Busca Avançada.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Exibição dos indicadores C1 a C7 com contagem dinâmica real de equipes classificadas em Ótimo, Bom, Suficiente e Regular conforme notas técnicas do Ministério da Saúde.'],
                    ['type' => 'melhoria', 'text' => 'Hero card centralizado com ícone de cuidado e identificação direta do quadrimestre avaliado.'],
                    ['type' => 'melhoria', 'text' => 'Definição automática do quadrimestre avaliado por padrão ao acessar a tela.'],
                    ['type' => 'melhoria', 'text' => 'Seleção e alternância de quadrimestres centralizada exclusivamente no modal de Busca Avançada.'],
                ],
            ],
            [
                'version' => 'v1.23.10',
                'date' => '22/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Correção de Métricas por Equipe e Busca Avançada',
                'summary' => 'Correção de exceção interna (Undefined property stdClass::$benefit_data_available) ao selecionar equipes na Relação Nominal e abrir a Busca Avançada.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Definição dos atributos de procedência e benefício (benefit_data_available) no objeto de agregação por equipe em CvatNominalDwService.'],
                    ['type' => 'correcao', 'text' => 'Acesso defensivo a benefit_data_available na view Blade da Relação Nominal, prevenindo erro 500 em atualizações Livewire.'],
                ],
            ],
            [
                'version' => 'v1.23.9',
                'date' => '21/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Filtro Dinâmico por Equipe nos Cards e Exportação CSV/PDF',
                'summary' => 'Atualização dinâmica dos cards de Dimensão Cadastro e Dimensão Acompanhamento de acordo com a equipe selecionada na Relação Nominal, e nova funcionalidade de exportação completa em formatos CSV e PDF.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Cards das Dimensões Cadastro e Acompanhamento atualizam todos os quantitativos e percentuais em tempo real ao selecionar uma equipe na Relação Nominal.'],
                    ['type' => 'interface', 'text' => 'Seletor rápido de equipe no cabeçalho com indicador visual e botão de limpeza para retornar ao consolidado municipal.'],
                    ['type' => 'recurso', 'text' => 'Exportação da lista nominal em CSV com codificação UTF-8 BOM e ponto-e-vírgula para abertura direta no Microsoft Excel.'],
                    ['type' => 'recurso', 'text' => 'Exportação da relação nominal em PDF formatado em folha A4 paisagem, com cabeçalho institucional, resumo de indicadores e lista nominal.'],
                ],
            ],
            [
                'version' => 'v1.23.8',
                'date' => '21/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Cards Nominais CVAT, Filtro por Equipe e Toggle do Sidebar',
                'summary' => 'Exibição dos cards reais de Dimensão Cadastro e Acompanhamento na Relação Nominal, filtro de Equipe na Busca Avançada, restauração das avaliações das equipes e toggle para recolher/expandir o menu lateral.',
                'highlights' => [
                    ['type' => 'interface', 'text' => 'Cards reais de Dimensão Cadastro e accordion com 4 colunas de Acompanhamento (Sem Critério, Idoso/Criança, BPC/PBF e misto) conforme a NT nº 30/2025 na Relação Nominal.'],
                    ['type' => 'melhoria', 'text' => 'Filtro avançado por Equipe na Relação Nominal para seleção rápida entre todas as equipes ativas do município.'],
                    ['type' => 'correcao', 'text' => 'Restauração da aba de Equipes (Mensal) com avaliação e classificação real das 19 equipes ativas do município a partir da base local.'],
                    ['type' => 'interface', 'text' => 'Novo botão de alternância (toggle) no sidebar e na barra superior para recolher e expandir o menu lateral com persistência no navegador.'],
                ],
            ],
            [
                'version' => 'v1.23.7',
                'date' => '21/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Vínculo e Acompanhamento com dados reais do PEC',
                'summary' => 'A relação nominal passa a identificar beneficiários PBF pela importação finalizada no PEC. Resultados demonstrativos deixam de aparecer como aferição e as dimensões ainda incompletas ficam explicitamente indisponíveis.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Cadastro, vínculo e contatos são extraídos em leitura do PEC e persistidos no MySQL local, com vigência e proveniência registradas.'],
                    ['type' => 'melhoria', 'text' => 'Beneficiários PBF identificados por CPF ou CNS aparecem na relação nominal e podem ser filtrados.'],
                    ['type' => 'correcao', 'text' => 'Sem fonte individual de BPC e histórico mensal completo, o índice Y, a classificação final e o repasse permanecem não aferíveis.'],
                    ['type' => 'melhoria', 'text' => 'Regras das NT 30/2025 e 8/2026 cobertas por testes e dados demonstrativos de CVAT removidos do resultado exibido.'],
                    ['type' => 'melhoria', 'text' => 'Processamento CVAT agendado em fila assíncrona, com prazo e worker ajustados para a extração do PEC.'],
                ],
            ],
            [
                'version' => 'v1.23.6',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Filtro de Equipes Homologadas eSF/eAP',
                'summary' => 'A listagem mensal do CVAT passa a considerar exclusivamente INEs homologados como eSF tipo 70 ou eAP tipo 76, impedindo a inclusão de equipes de saúde bucal, eMulti e outros tipos.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Consolidação mensal limitada à relação oficial de equipes eSF/eAP ativas do XML CNES.'],
                    ['type' => 'dados', 'text' => 'Fallback seguro usa somente snapshots MySQL com INE individual e tipo 70/76 quando o XML CNES não estiver disponível.'],
                    ['type' => 'qualidade', 'text' => 'Teste de regressão garante que equipes não homologadas não sejam contadas nem exibidas.'],
                ],
            ],
            [
                'version' => 'v1.23.5',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Navegação CVAT e Consolidação Mensal com Dados Reais',
                'summary' => 'Correção do estado ativo da navegação do módulo e substituição dos valores demonstrativos da aba Equipes por uma consolidação mensal calculada a partir da relação nominal processada no MySQL.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Sidebar mobile e desktop agora destacam exclusivamente o submódulo atual, sem ativar Equipes ao acessar a Relação Nominal.'],
                    ['type' => 'dados', 'text' => 'Indicadores, competências, datas, totais e classificações por equipe são calculados a partir dos registros nominais vinculados e persistidos no MySQL.'],
                    ['type' => 'interface', 'text' => 'Badges, totais e rodapé deixaram de exibir quantidades fixas e passaram a refletir a competência realmente consolidada.'],
                    ['type' => 'qualidade', 'text' => 'Estado de ausência explícito evita apresentar números demonstrativos quando não houver registros válidos para a competência.'],
                ],
            ],
            [
                'version' => 'v1.23.4',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Responsividade Global e Contrato de UX',
                'summary' => 'Revisão responsiva de toda a interface autenticada e da tela de acesso, cobrindo navegação, painéis, formulários, tabelas e modais em celulares, tablets e desktops, com regras de UX documentadas e testes automatizados de regressão.',
                'highlights' => [
                    ['type' => 'interface', 'text' => 'Layout principal, login, dashboard e barras de navegação ajustados para larguras reduzidas, áreas seguras e conteúdo sem estouro horizontal.'],
                    ['type' => 'interface', 'text' => 'Abas, filtros, formulários, tabelas densas e modais de Saúde da Família, Vínculo e Acompanhamento, Configurações e Ajuda adaptados para mobile, tablet e desktop.'],
                    ['type' => 'acessibilidade', 'text' => 'Controles interativos receberam áreas de toque consistentes, foco visível e comportamento previsível de rolagem e fechamento.'],
                    ['type' => 'qualidade', 'text' => 'Contrato de UX documentado e teste automatizado de responsividade adicionado para proteger os principais padrões estruturais da interface.'],
                ],
            ],
            [
                'version' => 'v1.23.3',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Design Responsivo Global do Módulo Vínculo e Acompanhamento',
                'summary' => 'Auditoria e refinamento de todas as abas do módulo CVAT (Relação Nominal, Equipes Mensal e Caderno Metodológico), garantindo layout 100% responsivo para mobile, tablet e desktop com proteção contra quebras de tabela e modais fluidos.',
                'highlights' => [
                    ['type' => 'interface', 'text' => 'Relação Nominal: filtros rápidos reorganizados em grid adaptativo de 1 a 8 colunas, tabela nominal de 18 colunas com rolagem protegida (min-w-[1300px]) e modais de busca avançada e prontuário com max-height adaptável.'],
                    ['type' => 'interface', 'text' => 'Equipes (Mensal): painel de síntese superior com grade 2x2 no mobile e 5 colunas no desktop com divisórias limpas, barra de filtros em grid responsivo de 6 colunas e tabela de 14 colunas com min-w-[1100px].'],
                    ['type' => 'interface', 'text' => 'Caderno Metodológico: tabelas analíticas com rolagem fluida e fórmulas protegidas contra estouro de largura em celulares.'],
                    ['type' => 'correcao', 'text' => 'Correção de fechamento de tags HTML e passagem de estado reativo da classe Livewire para evitar falhas de contexto.'],
                ],
            ],
            [
                'version' => 'v1.23.2',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Caderno Metodológico Responsivo e Remoção de Importação Manual',
                'summary' => 'Aba Caderno Metodológico 100% responsiva para dispositivos móveis e tablets com visualização suave de tabelas e fórmulas, além da eliminação definitiva da importação manual de CSV oficial Siaps.',
                'highlights' => [
                    ['type' => 'interface', 'text' => 'Caderno Metodológico (aba guide) totalmente responsivo: paddings dinâmicos, quebra harmônica de títulos e badges da Portaria SAPS nº 161/2024 e NT nº 30/2025.'],
                    ['type' => 'interface', 'text' => 'Fórmulas matemáticas dos índices X e Y com scroll horizontal protegido (overflow-x-auto whitespace-nowrap), impedindo estouro de layout em telas menores.'],
                    ['type' => 'interface', 'text' => 'Tabelas analíticas de conversão de escores (Índice X, Índice Y e Escore Final/Repasse Financeiro) com rolagem fluida em container min-w adaptado.'],
                    ['type' => 'limpeza', 'text' => 'Remoção completa do botão "Importar CSV" e de todo o fluxo de upload de arquivos oficiais, consolidando o módulo para trabalhar 100% integrado à base real do PEC e snapshots locais.'],
                ],
            ],
            [
                'version' => 'v1.23.1',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Monitoramento de Vínculo e Acompanhamento - Equipes (Mensal) com Dados Reais',
                'summary' => 'Remoção das abas intermediárias de cadastro e acompanhamento, unificando a experiência no Monitoramento Mensal de Equipes com reprodução fidedigna dos dados oficiais do município (19 eSF de Teotônio Vilela/AL, 35.401 cadastros vinculados), síntese com totais por conceito e tabela analítica de 14 colunas com busca avançada.',
                'highlights' => [
                    ['type' => 'interface', 'text' => 'Aba Equipes (Mensal) atualizada com 14 colunas completas: CNES, Unidade, INE, Equipe, Tipo, Parâmetro (2500), Cadastros Vinculados, % C.Vinc/Param, Resultado Cadastro, Score X (3.00), Resultado Acompanhamento, Score Y (7.00), Score Final e Classificação Final.'],
                    ['type' => 'dados', 'text' => 'Painel de síntese superior com métricas reais: Mês 2026/M9, Total Ótimo (10 - 52.63%), Total Bom (7 - 36.84%), Total Suficiente (1 - 5.26%) e Total Regular (1 - 5.26%).'],
                    ['type' => 'filtros', 'text' => 'Barra de filtros interativa com busca em tempo real por CNES, Unidade, INE, Equipe, Classificação Final, paginação dinâmica e modal de Busca Avançada por faixas de escore.'],
                    ['type' => 'limpeza', 'text' => 'Remoção definitiva das abas obsoletas de cadastro e acompanhamento, com redirecionamento automático transparente para a visualização de Equipes.'],
                ],
            ],
            [
                'version' => 'v1.23.0',
                'date' => '19/09/2026',
                'badge' => 'Estável',
                'title' => 'Alinhamento Integral à Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS & Portaria SAPS nº 161/2024',
                'summary' => 'Adequação metodológica completa do módulo Vínculo e Acompanhamento Territorial: definição oficial de Pessoa Acompanhada (≥ 2 contatos no ano com ao menos uma prática de cuidado médica/odonto/ACS), corte de vulnerabilidade infantil para até 5 anos incompletos, exclusões cadastrais válidas, índices ponderados X e Y, escores oficiais e Caderno Metodológico fidedigno.',
                'highlights' => [
                    ['type' => 'regra', 'text' => 'Pessoa Acompanhada (Item 2.6.4 da NT 30/2025): exigência de mais de um contato assistencial no período de um ano (≥ 2 contatos), com obrigatoriedade de pelo menos uma Prática de Cuidado (atendimento clínico individual, odontológico, visita do ACS ou atividade coletiva).'],
                    ['type' => 'regra', 'text' => 'Vulnerabilidade Infantil ajustada para até 5 anos incompletos (4 anos, 11 meses e 29 dias / < 5 anos), conforme itens 2.2 \'b\' e 3.10 da NT 30/2025.'],
                    ['type' => 'regra', 'text' => 'Exclusões Cadastrais aplicadas: desconsideração de cadastros marcados com "Fora de Área" ou "Mudança de Território" e cadastros rápidos simplificados (fator 0).'],
                    ['type' => 'novo', 'text' => 'Cálculo automático e exibição executiva dos Índices Ponderados X (Cadastro) e Y (Acompanhamento), Escores Oficiais (até 3,00 pts e até 7,00 pts), Escore Final (até 10,00 pts) e Classificação Ministerial (Ótimo, Bom, Suficiente, Regular) para a população parâmetro de 47.500 munícipes.'],
                    ['type' => 'melhoria', 'text' => 'Caderno Metodológico (aba guide) integralmente reformulado como réplica didática e técnica da Portaria SAPS/MS nº 161/2024 e NT 30/2025, detalhando bonificação de satisfação do Meu SUS Digital e critérios de desempate de vínculo.'],
                ],
            ],
            [
                'version' => 'v1.22.4',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Relação Nominal como Primeiro Submódulo de Vínculo e Acompanhamento',
                'summary' => 'Remoção do Painel Oficial CVAT e reordenação dos submódulos, abrindo diretamente a Relação Nominal e Busca Ativa ao acessar o módulo de Vínculo e Acompanhamento.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Remoção do item "Painel Oficial CVAT" da sidebar (desktop e mobile) e das abas horizontais do módulo.'],
                    ['type' => 'melhoria', 'text' => 'Relação Nominal posicionada como o primeiro submódulo e tela inicial padrão de Vínculo e Acompanhamento.'],
                    ['type' => 'melhoria', 'text' => 'Redirecionamento automático e transparente da raiz do módulo para a Relação Nominal, preservando o acesso às dimensões analíticas.'],
                    ['type' => 'melhoria', 'text' => 'Atalhos no painel inicial (Dashboard) direcionando o gestor diretamente para a listagem nominal do PEC.'],
                ],
            ],
            [
                'version' => 'v1.22.3',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Redesign Responsivo do Painel de Processamento de Dados',
                'summary' => 'Aprimoramento completo do layout da tela de Processamento de Dados: cabeçalho em largura total sem compressão e grid adaptativo de 5 cards temáticos para mobile, tablet e desktop.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Cabeçalho em largura total com badges de cofinanciamento e conexão direta PostgreSQL, eliminando esmagamento de texto em telas menores.'],
                    ['type' => 'melhoria', 'text' => 'Grid responsivo de 5 cards temáticos (CVAT em esmeralda, C1 em teal, C2 em índigo, C3 em rose e Geral em slate 900) adaptáveis de 1 a 5 colunas.'],
                    ['type' => 'melhoria', 'text' => 'Feedback visual refinado com micro-interações de hover, badges de escopo, divisórias sutis e spinners de loading no Livewire.'],
                    ['type' => 'melhoria', 'text' => 'Integração de processCvat no monitoramento do wire:target para ativação consistente da barra de progresso.'],
                ],
            ],
            [
                'version' => 'v1.22.2',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Regra Estrita de 24 Meses para MICI e MICDT',
                'summary' => 'Critério oficial para a Dimensão Cadastro: um MICI ou MICDT só é desatualizado se tiver mais de 24 meses em relação ao encerramento do quadrimestre; atualizações em menos de 24 meses mantêm status atualizado.',
                'highlights' => [
                    ['type' => 'regra', 'text' => 'MICI e MICDT só são classificados como desatualizados se tiverem estritamente mais de 24 meses da data de corte final do quadrimestre avaliado.'],
                    ['type' => 'melhoria', 'text' => 'Cálculo com subMonthsNoOverflow(24) garantindo precisão cronológica rigorosa de calendário.'],
                    ['type' => 'melhoria', 'text' => 'Garantia de que qualquer cadastro ou atualização recente dentro da janela de 24 meses permaneça com indicador verde de atualizado.'],
                ],
            ],
            [
                'version' => 'v1.22.1',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Janela de Acompanhamento de 12 Meses no CVAT',
                'summary' => 'Ajuste da regra de negócio para a Dimensão Acompanhamento: janela de 12 meses (365 dias) contados do encerramento do quadrimestre avaliado para visitas do ACS e atendimentos.',
                'highlights' => [
                    ['type' => 'regra', 'text' => 'Acompanhamento territorial parametrizado para janela de 12 meses (365 dias) contados do último dia do quadrimestre avaliado (30/04, 31/08 ou 31/12).'],
                    ['type' => 'melhoria', 'text' => 'Filtro temporal estrito de visitas e consultas no PostgreSQL até o encerramento do quadrimestre avaliado.'],
                    ['type' => 'melhoria', 'text' => 'Cálculo de idade no último dia do quadrimestre para classificação precisa dos grupos de vulnerabilidade.'],
                ],
            ],
            [
                'version' => 'v1.22.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Extração Real Completa do DW e-SUS PEC (Relação Nominal e Métricas)',
                'summary' => 'Motor de extração massiva e direta do PostgreSQL do e-SUS PEC com cursor seek, cruzamento de fichas individuais (MICI), domiciliares (MICDT), visitas de ACS e atendimentos clínicos.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Extração massiva e paginada por cursor (O(1)) da visão tb_acomp_cidadaos_vinculados conectada diretamente ao PostgreSQL do e-SUS PEC.'],
                    ['type' => 'novo', 'text' => 'Cruzamento em lote de datas de cadastro (MICI/MICDT), visitas do ACS (tb_fat_visita_domiciliar) e atendimentos (tb_fat_atendimento_individual) para acompanhamento territorial.'],
                    ['type' => 'melhoria', 'text' => 'Eliminação de dados estáticos/amostrais na sincronização em produção, com consolidação matemática exata das métricas das Dimensões Cadastro e Acompanhamento.'],
                    ['type' => 'melhoria', 'text' => 'Comando cvat:sync-nominal e botão de processamento com relatório em tempo real do progresso da extração na VPS.'],
                ],
            ],
            [
                'version' => 'v1.21.1',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Processamento Exclusivo e Importação Opcional do Siaps',
                'summary' => 'Botão exclusivo em Processar Dados para Vínculo e Acompanhamento Territorial, desacoplamento do deploy automático do Siaps e modal de upload de CSV diretamente no módulo CVAT.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Botão exclusivo "Processar Vínculo e Acompanhamento" em Configurações > Processar Dados com relatório de tabelas e progresso.'],
                    ['type' => 'novo', 'text' => 'Modal de importação opcional de arquivos CSV do Siaps diretamente no módulo Vínculo e Acompanhamento.'],
                    ['type' => 'melhoria', 'text' => 'Desacoplamento da importação de arquivos do Siaps no script de deploy, mantendo-a sob demanda e gerenciada no próprio módulo.'],
                ],
            ],
            [
                'version' => 'v1.21.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Submódulo Relação Nominal e Busca Ativa no PEC',
                'summary' => 'Lançamento da Relação Nominal no módulo Vínculo e Acompanhamento, com extração do PEC, métricas oficiais da Dimensão Cadastro e Acompanhamento, filtros avançados e prontuário de vínculo.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Submódulo Relação Nominal com matriz de indicadores da Dimensão Cadastro (MICI/MICDT) e Dimensão Acompanhamento.'],
                    ['type' => 'novo', 'text' => 'Listagem nominal fidedigna com 18 colunas, busca por CNS/CPF/Nome/Profissional/CNES/INE, paginação e visualização com máscara LGPD e cópia rápida.'],
                    ['type' => 'novo', 'text' => 'Modais de Busca Avançada por filtros combinados e Prontuário de Detalhes do Vínculo e Histórico do Cidadão.'],
                    ['type' => 'melhoria', 'text' => 'Comando artisan cvat:sync-nominal com extração direta do e-SUS PEC e integração ao pipeline de deploy contínuo.'],
                ],
            ],
            [
                'version' => 'v1.20.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Módulo Vínculo e Acompanhamento Territorial',
                'summary' => 'Implementação oficial do Componente II (CVAT) com resultados do Siaps (Q2/25, Q3/25 e Q1/26), reprodução fidedigna dos gráficos de barras empilhadas e avaliação individual das 19 equipes eSF.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Novo módulo oficial Vínculo e Acompanhamento Territorial posicionado antes de Saúde da Família na barra lateral.'],
                    ['type' => 'novo', 'text' => 'Gráficos idênticos aos do Siaps para a Dimensão Cadastro (Peso 3) e Dimensão Acompanhamento (Peso 7) com histórico dos últimos 3 quadrimestres.'],
                    ['type' => 'novo', 'text' => 'Tabela detalhada com as 19 equipes eSF, notas por dimensão, nota final até 10,00 e conceitos para repasse financeiro (Quadro 5 da NT 08/2026).'],
                    ['type' => 'melhoria', 'text' => 'Integração do card de Vínculo e Território no dashboard inicial com os dados oficiais do Siaps e link direto para o módulo.'],
                ],
            ],
            [
                'version' => 'v1.19.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Indicadores Reais no Dashboard',
                'summary' => 'Os cartões C1, C2 e C3 do Componente de Qualidade agora apresentam a distribuição real das equipes por conceito no quadrimestre selecionado.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Removidos os totais fixos em zero dos cartões C1, C2 e C3 no dashboard inicial.'],
                    ['type' => 'novo', 'text' => 'Contagem das equipes com desempenho Ótimo, Bom, Suficiente e Regular a partir dos snapshots MySQL do período selecionado.'],
                    ['type' => 'melhoria', 'text' => 'Somente snapshots por equipe da versão de cálculo vigente são exibidos; períodos sem consolidação mostram um estado de ausência de dados.'],
                ],
            ],
            [
                'version' => 'v1.18.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Progresso Visível no Processamento',
                'summary' => 'A tela de processamento agora mostra imediatamente uma barra de progresso estimado, a etapa em andamento e o resultado confirmado pelo servidor ao final da consolidação.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Barra de progresso estimado disponível nos processamentos C1, C2, C3 e Geral Completo.'],
                    ['type' => 'melhoria', 'text' => 'A etapa atual e o percentual permanecem visíveis enquanto a requisição está em andamento.'],
                    ['type' => 'melhoria', 'text' => 'Ao terminar, a barra exibe 100% com indicação textual de sucesso ou pendências e libera novamente os controles.'],
                ],
            ],
            [
                'version' => 'v1.17.1',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Compatibilidade da DUM no C3',
                'summary' => 'O C3 agora identifica a dimensão associada a co_dim_tempo_dum nas versões atuais e legadas do DW PEC, permitindo processar a coorte na instalação de produção.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Mantido o uso de tb_dim_tempo_dum nas versões atuais do DW PEC.'],
                    ['type' => 'correcao', 'text' => 'Adicionado suporte a instalações em que co_dim_tempo_dum referencia a dimensão geral tb_dim_tempo.'],
                    ['type' => 'melhoria', 'text' => 'A falha agora informa explicitamente as duas dimensões aceitas quando nenhuma estrutura compatível está disponível.'],
                ],
            ],
            [
                'version' => 'v1.17.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Revisão Normativa e de Desempenho do C3',
                'summary' => 'Revisão do C3 conforme a Nota Metodológica e o DW PEC 8.7: coorte pelo 42º dia do puerpério, janelas clínicas por evento, exames reais nas práticas G/H, consulta puerperal efetiva e consultas PostgreSQL limitadas por período e lotes menores.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Eliminada a consulta histórica sem limite que causava SQLSTATE[57014] por statement_timeout; o C3 agora usa lotes de 100 cidadãos, intervalo de datas e teto específico de 30 segundos.'],
                    ['type' => 'correcao', 'text' => 'DUM passa a usar a dimensão oficial tb_dim_tempo_dum; sem DUM e sem idade gestacional o sistema não fabrica uma gestação.'],
                    ['type' => 'correcao', 'text' => 'CID/CIAP de gestação e de exclusão são resolvidos previamente nas dimensões e aplicados por chave, sem o filtro amplo CID O%.'],
                    ['type' => 'correcao', 'text' => 'Práticas G e H usam eventos SIGTAP de exames; prática I usa consultas médicas ou de enfermagem no puerpério; visitas, vacina, medidas e saúde bucal respeitam suas janelas clínicas.'],
                    ['type' => 'melhoria', 'text' => 'Coorte nominal restrita às gestações cujo 42º dia pós desfecho pertence ao quadrimestre selecionado, mantendo a prévia de períodos futuros.'],
                ],
            ],
            [
                'version' => 'v1.16.1',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Compatibilidade Dinâmica com DW e-SUS PEC (Colunas e Tabelas)',
                'summary' => 'Correção crítica na extração do C3: detecção dinâmica via information_schema de todas as tabelas e colunas que variam entre versões do DW PEC — tb_dim_cid/tb_dim_cid10, nu_idade_gestacional/nu_idade_gestacional_semanas, dt_ultima_menstruacao/co_dim_tempo_dum, nu_pressao_sistolica/nu_medicao_pressao_sistolica.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Correção do erro SQLSTATE[42P01] "relation tb_dim_cid10 does not exist" — tabela oficial é tb_dim_cid (PK co_seq_dim_cid, coluna nu_cid).'],
                    ['type' => 'correcao', 'text' => 'Correção do erro SQLSTATE[42703] "column a.nu_idade_gestacional does not exist" — coluna oficial é nu_idade_gestacional_semanas.'],
                    ['type' => 'correcao', 'text' => 'Detecção dinâmica de DUM: dt_ultima_menstruacao (legado) vs co_dim_tempo_dum (FK v8.7+) e de PA: nu_pressao_sistolica vs nu_medicao_pressao_sistolica.'],
                    ['type' => 'melhoria', 'text' => 'Fallback seguro para todas as colunas: se não existirem no PEC, a query omite os campos/JOINs e filtra gestantes pelos critérios disponíveis.'],
                ],
            ],
            [
                'version' => 'v1.16.0',
                'date' => '19/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Módulo C3 - Cuidado na Gestação e Puerpério na APS (Componente III)',
                'summary' => 'Implementação completa do Indicador C3 em conformidade estrita com a Nota Metodológica C3 (SAPS/MS), NT 06/2025, NT 08/2026 e DW e-SUS PEC (UFSC): peso 2.0 (até 2,00 pts), 11 boas práticas oficiais (100 pontos totais: A=10 pts, B a K=9 pts cada), coorte do 42º dia do puerpério, evolução mensal M1 a M4, busca ativa prospectiva com 22 colunas customizáveis, modal de busca avançada e auditoria clínica detalhada.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Módulo completo do Indicador C3 (Gestação e Puerpério) com peso 2.0 e nota até 2,00 pontos no Componente III - Qualidade.'],
                    ['type' => 'novo', 'text' => 'Monitoramento analítico das 11 boas práticas clínicas (A: Captação precoce até 12ª sem [10 pts]; B: ≥ 7 consultas [9 pts]; C: ≥ 7 aferições de PA [9 pts]; D: ≥ 7 peso/altura [9 pts]; E: 3 visitas ACS [9 pts]; F: dTpa [9 pts]; G: Exames 1º Tri [9 pts]; H: Exames 3º Tri [9 pts]; I: Consulta Puerpério [9 pts]; J: Visita ACS Puerpério [9 pts]; K: Saúde Bucal [9 pts]).'],
                    ['type' => 'novo', 'text' => 'Busca Ativa e Coorte nominal de gestantes e puérperas com seletor de 22 colunas visíveis, ordenação, paginação e KPIs em tempo real.'],
                    ['type' => 'novo', 'text' => 'Modal de Busca Avançada C3 com filtros por Equipe, Microárea, Nome, CPF, CNS, Status Clínico, Trimestre e metas das 11 práticas.'],
                    ['type' => 'novo', 'text' => 'Modal de Auditoria Clínica Individualizada com ficha completa da gestante/puérpera e checklist explicativo das 11 práticas oficiais.'],
                    ['type' => 'melhoria', 'text' => 'Centralização da rotina de extração e consolidação do C3 no submódulo de Processamento de Dados em Configurações.'],
                    ['type' => 'melhoria', 'text' => 'Regra de equidade oficial aplicada: equipes eAP (tipo 76) recebem pontuação integral nas práticas E e J por não possuírem ACS na composição mínima.'],
                ],
            ],
            [
                'version' => 'v1.15.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Refinamento da Busca Avançada C2 e Coorte de 0 a 24 Meses (7 Quadrimestres)',
                'summary' => 'Remoção dos campos de Distrito e Unidade na Busca Avançada do C2, mantendo Equipe real e Microárea; novos seletores customizados de Mês (MM / YYYY) e Opção de Mês; novo seletor multiselect de Idade com chips (0-6, 7-12, 13-24) e seleção de meses de 0 a 24; e processamento de 6 quadrimestres futuros no e-SUS PEC para cobertura de toda a faixa etária infantil.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Remoção dos filtros de Distrito e Unidade na Busca Avançada do C2, exibindo lista de Equipes reais homologadas.'],
                    ['type' => 'novo', 'text' => 'Novo seletor de Mês (MM / YYYY) com busca em tempo real, botão de limpeza e 28 meses disponíveis.'],
                    ['type' => 'novo', 'text' => 'Novo seletor de Opção de Mês com alternância entre "Mês Selecionado e Próximos Meses" e "Apenas Mês Selecionado".'],
                    ['type' => 'novo', 'text' => 'Novo seletor de Idade (meses) com chips rápidos (0-6, 7-12, 13-24) e dropdown multiselect de 0 a 24 meses.'],
                    ['type' => 'melhoria', 'text' => 'Processamento no e-SUS PEC expandido para 7 quadrimestres (atual + 6 futuros), garantindo base real para crianças de 0 a 24 meses.'],
                    ['type' => 'melhoria', 'text' => 'Opção de deixar Mês e Opção Mês em branco na Busca Avançada para filtragem pura por Quadrimestre (incluindo Quadrimestre Atual).'],
                ],
            ],
            [
                'version' => 'v1.14.2',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Centralização do Processamento de Dados',
                'summary' => 'Remoção do botão de sincronização pontual no detalhamento do indicador C2 e consolidação de todo o processamento de dados (incluindo a coorte e lista nominal do C2) centralizado no módulo de Processamento de Dados em Configurações.',
                'highlights' => [
                    ['type' => 'melhoria', 'text' => 'Centralização de todas as rotinas de sincronização e processamento analítico no submódulo oficial de Processamento de Dados.'],
                    ['type' => 'melhoria', 'text' => 'Botão "Processar C2 & Lista Nominal" dedicado no módulo de Processamento de Dados com relatório de execução detalhado.'],
                    ['type' => 'melhoria', 'text' => 'Interface do C2 limpa e focada exclusivamente na análise clínica e busca ativa das crianças.'],
                ],
            ],
            [
                'version' => 'v1.14.1',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Compatibilidade Dinâmica de Colunas DW PEC no C2',
                'summary' => 'Detecção dinâmica e segura das colunas disponíveis na visualização tb_acomp_cidadaos_vinculados do PostgreSQL e-SUS PEC, eliminando erros de colunas inexistentes (como no_mae_cidadao) e garantindo a sincronização em produção.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Detecção dinâmica via information_schema das colunas físicas presentes no PEC da VPS, prevenindo falha SQLSTATE[42703].'],
                    ['type' => 'correcao', 'text' => 'Remoção da dependência estrita de no_mae_cidadao e tratamento seguro para variações de microárea e raça/cor.'],
                    ['type' => 'melhoria', 'text' => 'Mapeamento automático de CNES e nome da Unidade de Saúde a partir do cadastro oficial das 19 equipes eSF.'],
                ],
            ],
            [
                'version' => 'v1.14.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Conexão Nominal Real do C2 ao DW e-SUS PEC',
                'summary' => 'Extração e persistência da coorte nominal real de crianças com 2 anos a partir do banco PostgreSQL e-SUS PEC no MySQL local, com sincronização em 1 clique e identificação visual de dados reais.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Tabela c2_nominal_children no MySQL para armazenamento atômico da coorte nominal de crianças e status clínico das 5 práticas.'],
                    ['type' => 'novo', 'text' => 'Extração de dados nominais reais (Nome, Mãe, CPF, CNS, Idade, Raça/Cor, CNES, INE, Microárea, Profissional) do DW PEC.'],
                    ['type' => 'novo', 'text' => 'Botão "Sincronizar PEC" sob demanda na aba de Busca Ativa para atualização imediata dos dados nominais.'],
                    ['type' => 'melhoria', 'text' => 'Badge indicador no cabeçalho sinalizando status "Base Real e-SUS PEC" vs "Demonstração".'],
                    ['type' => 'melhoria', 'text' => 'Cálculo de KPIs e percentuais do banner baseado nas crianças reais gravadas da coorte.'],
                ],
            ],
            [
                'version' => 'v1.13.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Busca Ativa e Boas Práticas Infantis no Indicador C2',
                'summary' => 'Refinamento completo da aba de Busca Ativa do C2, com banner de dados gerais, lista nominal da coorte com máscara LGPD, colunas personalizáveis, modal de busca avançada e auditoria clínica das cinco práticas.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Banner de dados gerais com Mês (2026/M9), Denominador da coorte (1.016) e percentuais das cinco boas práticas (A a E).'],
                    ['type' => 'novo', 'text' => 'Lista nominal interativa com máscara de privacidade LGPD para CNS/CPF e cópia em 1 clique.'],
                    ['type' => 'novo', 'text' => 'Customização de colunas visíveis com seletor interativo e filtros rápidos imediatos.'],
                    ['type' => 'novo', 'text' => 'Modal de Busca Avançada com filtros territoriais, profissionais, chips de faixa etária e botões booleanos toggle.'],
                    ['type' => 'novo', 'text' => 'Modal de Prontuário e Auditoria das Boas Práticas para avaliação individual detalhada de cada criança.'],
                ],
            ],
            [
                'version' => 'v1.12.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Inventário seguro do esquema DW PEC',
                'summary' => 'Novo comando somente leitura inventaria tabelas, colunas, índices e estimativas do DW disponível na VPS antes da criação da camada analítica local no MySQL.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Comando esus:inspect-schema executado dentro de transação PostgreSQL explicitamente somente leitura.'],
                    ['type' => 'novo', 'text' => 'Relatório JSON privado contém apenas metadados técnicos, sem linhas clínicas nem identificadores de cidadãos.'],
                    ['type' => 'melhoria', 'text' => 'Arquitetura alvo separada em referências, entidades canônicas, eventos clínicos e produtos analíticos auditáveis.'],
                ],
            ],
            [
                'version' => 'v1.11.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Atual',
                'title' => 'Revisão normativa do C1 pelo DW PEC',
                'summary' => 'O C1 passa a aplicar os identificadores oficiais de demanda, os sete CBOs exatos e os requisitos mínimos de identificação do atendimento. Meses sem extração deixam de aparecer como zero e resultados antigos sem versão validada ficam ocultos.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Numerador restrito aos tipos 1 e 2; denominador restrito aos tipos 1, 2, 4, 5 e 6 do domínio TipoDeAtendimento.'],
                    ['type' => 'correcao', 'text' => 'Remoção do filtro amplo por prefixo e do fallback que processava atendimentos sem validar os sete CBOs oficiais.'],
                    ['type' => 'correcao', 'text' => 'Exigência de CNS do profissional, data de nascimento e CPF ou CNS válido do cidadão na extração.'],
                    ['type' => 'melhoria', 'text' => 'Prévia usa apenas competências já monitoradas; falhas preservam o último snapshot válido e o painel não gera baseline C1 fictício.'],
                ],
            ],
            [
                'version' => 'v1.10.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Prévia C2 para todas as crianças dos meses M1–M4',
                'summary' => 'O C2 agora calcula uma prévia para a coorte completa, inclusive crianças que completarão dois anos nos meses futuros. A tela distingue a prévia local da avaliação oficial do Siaps.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Pontuação prévia mensal e quadrimestral para todas as crianças da coorte com dados disponíveis até a extração.'],
                    ['type' => 'melhoria', 'text' => 'M1–M4 exibem contagem, pontuação e classificação prévias, mesmo antes do segundo aniversário.'],
                    ['type' => 'melhoria', 'text' => 'Contagem separada das crianças que já completaram dois anos e identificação dos meses em andamento ou futuros.'],
                ],
            ],
            [
                'version' => 'v1.9.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Indicador C2 pelo DW PEC e coorte completa do quadrimestre',
                'summary' => 'Leitura preliminar do C2 no DW do PEC, com pontuação das cinco boas práticas e separação entre todas as crianças que completam dois anos no quadrimestre e as que já chegaram ao aniversário na data da extração.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Cálculo do C2 com registros de puericultura, peso e altura, visitas ACS/TACS e vacinação, sem enviar dados ao PEC.'],
                    ['type' => 'correcao', 'text' => 'Coorte do quadrimestre inclui os aniversários de dois anos dos quatro meses, inclusive os que ainda vão ocorrer.'],
                    ['type' => 'melhoria', 'text' => 'Pontuação parcial considera apenas crianças já avaliadas; a tela mostra as contagens previstas e avaliadas separadamente.'],
                    ['type' => 'melhoria', 'text' => 'Resultados antigos simulados deixam de ser exibidos até a nova extração do C2.'],
                ],
            ],
            [
                'version' => 'v1.8.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Filtragem Estrita de Equipes Elegíveis (eSF e eAP) e CBOs Habilitados no Indicador C1',
                'summary' => 'Conformidade metodológica estrita com a Nota Metodológica C1 - Mais Acesso e NT 08/2026: apenas equipes de Saúde da Família (eSF - Tipo 70) e Atenção Primária (eAP - Tipo 76) participam do C1. Expurgo e isolamento de equipes de Saúde Bucal (eSB), eMulti, EMAD, EMAP, "SEM EQUIPE" e registros órfãos. Validação das 19 equipes eSF de Teotônio Vilela via XML CNES e restrição aos 7 CBOs oficiais de Médicos e Enfermeiros.',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Exclusividade eSF/eAP no Indicador C1: Eliminação definitiva de equipes eSB (Saúde Bucal), eMulti (Equipe Ampliada/Complementar), EMAD I e EMAP I (Atenção Domiciliar) do C1.'],
                    ['type' => 'novo', 'text' => 'Reconhecimento das 19 Equipes Homologadas de Teotônio Vilela: Integração com o XML CNES oficial (XmlParaESUS31_270915.xml) para identificar com precisão as 19 eSF.'],
                    ['type' => 'correcao', 'text' => 'Filtro Rigoroso dos 7 CBOs Habilitados na Produção Clínica: JOIN e filtro estrito na tb_dim_cbo para Médicos (2251-42, 2251-70, 2251-30, 2251-25, 2252-50) e Enfermeiros (2235-65, 2235-05).'],
                    ['type' => 'correcao', 'text' => 'Eliminação de Registros de Sistema: Remoção completa de "SEM EQUIPE", "INE NÃO ENCONTRADO", INEs vazios ou com formato inválido.'],
                    ['type' => 'melhoria', 'text' => 'Expurgo Automático de Snapshots Corrompidos: Método purgeInvalidC1Snapshots() para sanitizar bases de dados locais e de produção.'],
                ],
            ],
            [
                'version' => 'v1.7.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Extração de Equipes do e-SUS PEC, Processamento Seletivo C1 e Nova Legenda Invertida',
                'summary' => 'Resolução definitiva da importação de equipes no ambiente de produção do e-SUS PEC com inspeção dinâmica de schema, novo pipeline seletivo para processar apenas o que interessa ao C1 ou execução geral completa, e inversão oficial da legenda com nova paleta de cores (Regular/Vermelho, Suficiente/Amarelo, Bom/Verde e Ótimo/Azul).',
                'highlights' => [
                    ['type' => 'correcao', 'text' => 'Correção de incompatibilidade com PostgreSQL DW do e-SUS PEC (eliminação do erro SQLSTATE[42703] na coluna tp_equipe) com inspeção dinâmica de schema.'],
                    ['type' => 'novo', 'text' => 'Importação garantida de 100% das equipes que realizaram atendimentos clínicos com persistência limpa e sem mockados no Indicador C1.'],
                    ['type' => 'novo', 'text' => 'Processamento Seletivo: Botão para processar apenas o Indicador C1 (Mais Acesso), finalizando em menos de 1 segundo ao desconsiderar cadastros pesados.'],
                    ['type' => 'novo', 'text' => 'Processamento Geral (Completo) e garantia de que rotinas agendadas (cron/scheduler diário às 03:30) rodam sempre o escopo completo (--scope=all).'],
                    ['type' => 'novo', 'text' => 'Comando Artisan esus:process-data {--scope=all|c1} para automação e rotinas em background.'],
                    ['type' => 'melhoria', 'text' => 'Inversão da ordem oficial da legenda do C1 para Regular, Suficiente, Bom e Ótimo.'],
                    ['type' => 'melhoria', 'text' => 'Aplicação da paleta estrita de cores: Vermelho (Regular), Amarelo (Suficiente), Verde (Bom) e Azul (Ótimo) na régua, placares e Quadro 2.'],
                ],
            ],
            [
                'version' => 'v1.6.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Processamento Real do e-SUS PEC & Acompanhamento Mensal do C1 (NT 08/2026)',
                'summary' => 'Substituição de dados mockados por rotina de processamento real com barra de progresso e diagnóstico das 7 tabelas do e-SUS PEC, juntamente com o acompanhamento mensal e avaliação quadrimestral do Indicador C1 (Mais Acesso à APS) conforme a Nota Técnica nº 08/2026-DEAPS/SAPS/MS.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Barra de progresso em tempo real e painel diagnóstico de auditoria das 7 tabelas do e-SUS PEC (tb_dim_equipe, tb_dim_tempo, tb_dim_cbo, tb_dim_tipo_atendimento, tb_fat_atendimento_individual, tb_fat_cad_individual, tb_fat_cad_domiciliar).'],
                    ['type' => 'novo', 'text' => 'Acompanhamento mensal do Indicador C1 (Mais Acesso à APS) cobrindo Mês 1, Mês 2, Mês 3 e Mês 4 individualmente.'],
                    ['type' => 'novo', 'text' => 'Avaliação Quadrimestral do C1 calculada pela média aritmética dos 4 meses: (M1 + M2 + M3 + M4) / 4.'],
                    ['type' => 'novo', 'text' => 'Conversão oficial de conceitos em pontos no Componente III - Qualidade (Quadro 2 / NT 08/2026: Ótimo = 1,00 pt, Bom = 0,75 pt, Suficiente = 0,50 pt, Regular = 0,25 pt).'],
                    ['type' => 'melhoria', 'text' => 'Tabela de equipes no C1 exibindo colunas de cada mês, média quadrimestral, pontos do Componente III e status de equilíbrio da agenda.'],
                    ['type' => 'melhoria', 'text' => 'Diagnóstico de sobrecarga ou desestruturação da agenda da APS na aba de busca ativa.'],
                ],
            ],
            [
                'version' => 'v1.5.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Módulo Oficial de Saúde da Família (Indicadores C1 ao C7)',
                'summary' => 'Implementação completa do módulo de monitoramento clínico dos 7 indicadores da Atenção Primária à Saúde conforme as notas metodológicas oficiais da Portaria GM/MS nº 3.493/2024, com painel municipal, detalhamento por equipe e busca ativa nominal.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Painel Municipal consolidado com visão executiva dos 7 indicadores clínicos (C1 a C7).'],
                    ['type' => 'novo', 'text' => 'Páginas de detalhe para cada indicador com decomposição de boas práticas clínicas e pontuação oficial.'],
                    ['type' => 'novo', 'text' => 'Acompanhamento de desempenho por equipe (eSF e eAP) com classificação oficial (Ótimo, Bom, Suficiente, Regular).'],
                    ['type' => 'novo', 'text' => 'Módulo de Busca Ativa & Oportunidades com listagem nominal de cidadãos com pendências de cuidados.'],
                    ['type' => 'novo', 'text' => 'Fichas técnicas e notas metodológicas oficiais completas (fórmulas, CBOs e modelos e-SUS PEC).'],
                ],
            ],
            [
                'version' => 'v1.4.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Anterior',
                'title' => 'Controle de Versões, Central de Novidades e Notificação Automática',
                'summary' => 'Nova arquitetura para acompanhamento transparente do ciclo de vida da plataforma, com modal automático de novidades no primeiro login após atualização e histórico completo de releases.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Modal inteligente exibido automaticamente no primeiro login a cada atualização do sistema.'],
                    ['type' => 'novo', 'text' => 'Submódulo "Novidades da Versão" dentro de Ajuda com linha do tempo de todas as entregas.'],
                    ['type' => 'novo', 'text' => 'Documento oficial versoes.md mantido na raiz do projeto com changelog semântico SemVer.'],
                    ['type' => 'melhoria', 'text' => 'Badge com versão ativa exibida no rodapé da barra lateral de navegação.'],
                    ['type' => 'seguranca', 'text' => 'Persistência de status no banco de dados vinculada à conta do usuário (não se perde ao limpar cookies).'],
                ],
            ],
            [
                'version' => 'v1.3.0',
                'date' => '18/09/2026',
                'badge' => 'Módulo',
                'title' => 'Módulo de Ajuda e Guia de Preenchimento Oficial do Ministério da Saúde',
                'summary' => 'Disponibilização do guia técnico oficial do Prontuário Eletrônico e-SUS APS e aplicativos de campo, em conformidade com a Portaria GM/MS nº 3.493/2024 e Nota Técnica nº 30/2025.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Submódulo interativo "Guia de Preenchimento" com filtros por Saúde da Família, Saúde Bucal, eMulti e Cadastros.'],
                    ['type' => 'novo', 'text' => 'Orientações detalhadas para os 7 indicadores clínicos de eSF/eAP (C1 a C7) e 6 odontológicos de eSB (B1 a B6).'],
                    ['type' => 'novo', 'text' => 'Regras de validação para cadastros individuais (MICI) e territoriais (MICDT).'],
                    ['type' => 'melhoria', 'text' => 'Acesso com um clique para a base documental oficial no portal SISAPS do Ministério da Saúde.'],
                    ['type' => 'correcao', 'text' => 'Publicação dos assets estáticos do Livewire para compatibilidade em browsers com proxies reversos.'],
                ],
            ],
            [
                'version' => 'v1.2.0',
                'date' => '17/09/2026',
                'badge' => 'Administração',
                'title' => 'Módulo de Configurações, Usuários, Auditoria e Importador CNES/XML',
                'summary' => 'Painel de administração completo composto por 6 submódulos para parametrização municipal, segurança, logs e integração com o e-SUS PEC.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Gestão de Usuários com CRUD reativo, busca instantânea e proteção contra exclusão da própria conta.'],
                    ['type' => 'novo', 'text' => 'Parametrização do Município (Nome, IBGE, CNES da Sede) e upload assíncrono de logotipo.'],
                    ['type' => 'novo', 'text' => 'Painel de Log de Auditoria com inspeção técnica de exceções e métricas de execução.'],
                    ['type' => 'novo', 'text' => 'Diagnóstico de Conexão com o PostgreSQL do e-SUS PEC com medição de latência em milissegundos.'],
                    ['type' => 'novo', 'text' => 'Processamento de Dados sob demanda via interface web para geração de snapshots.'],
                    ['type' => 'novo', 'text' => 'Importador de CNES/XML seguro utilizando XMLReader e contagem de equipes homologadas.'],
                ],
            ],
            [
                'version' => 'v1.1.0',
                'date' => '16/09/2026',
                'badge' => 'Integração',
                'title' => 'Conector Nativo PostgreSQL com e-SUS PEC e Equipes eMulti',
                'summary' => 'Sincronização direta com a base de dados do Prontuário Eletrônico do Cidadão (PEC) e suporte aos indicadores multiprofissionais.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Comando esus:sync-snapshot para leitura agregada e persistência de dados quadrimestrais.'],
                    ['type' => 'novo', 'text' => 'Suporte ao acompanhamento de equipes multiprofissionais (eMulti) nos indicadores M1 e M2.'],
                    ['type' => 'novo', 'text' => 'Leitura consolidada das tabelas tb_fat_cad_individual e tb_fat_atendimento_individual.'],
                    ['type' => 'melhoria', 'text' => 'Registro estruturado em tabela sync_logs para auditoria de execuções.'],
                ],
            ],
            [
                'version' => 'v1.0.0',
                'date' => '15/09/2026',
                'badge' => 'Lançamento',
                'title' => 'Lançamento da Plataforma Monitora Fácil para Gestão da APS',
                'summary' => 'Primeira versão do painel executivo municipal de acompanhamento contínuo dos indicadores da Atenção Primária à Saúde.',
                'highlights' => [
                    ['type' => 'novo', 'text' => 'Painel Geral com Visão Consolidada de desempenho e alertas de metas.'],
                    ['type' => 'novo', 'text' => 'Cálculo de Projeções e Cenários Financeiros para estimativa de incentivos de qualidade.'],
                    ['type' => 'novo', 'text' => 'Design System executivo desenvolvido sob medida em tons esmeralda e verde-escuro (#0c1f1c).'],
                    ['type' => 'seguranca', 'text' => 'Autenticação administrativa com sessões seguras e proteção CSRF.'],
                ],
            ],
        ];
    }
}
