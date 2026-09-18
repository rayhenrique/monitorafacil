<?php

namespace App\Services;

use App\Models\User;

class VersionService
{
    public const CURRENT_VERSION = 'v1.6.0';

    public const CURRENT_RELEASE_DATE = '18/09/2026';

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
                'version' => 'v1.6.0',
                'date' => '18/09/2026',
                'badge' => 'Versão Atual',
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
