<?php

namespace App\Services;

use App\Models\User;

class VersionService
{
    public const CURRENT_VERSION = 'v1.23.6';

    public const CURRENT_RELEASE_DATE = '19/09/2026';

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
                'version' => 'v1.23.6',
                'date' => '19/09/2026',
                'badge' => 'Versão Atual',
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
