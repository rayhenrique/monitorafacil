# Contrato de UX

## Contexto do produto

- Público: gestores municipais e operadores da Atenção Primária à Saúde.
- Trabalho principal: consultar consolidados, localizar pendências, processar dados e manter parâmetros municipais.
- Mercado e idioma: municípios brasileiros; interface e formatação em `pt-BR`; fuso definido pela configuração da aplicação.
- Acessibilidade: WCAG 2.2 AA como referência.

## Fontes de negócio

| Escopo | Fonte | Autoridade |
|---|---|---|
| Produto, público e módulos | `PRD.md` e `MVP-SCOPE.md` | Requisitos do produto |
| Dados, retenção e integrações | `DATABASE-SCHEMA.md` | Contrato de dados |
| Entregas e validações | `TASKS.md` | Histórico de implementação |
| Identidade visual | `DESIGN.md` | Contrato visual |

## Sistema visual e proprietários canônicos

- `DESIGN.md` documenta intenção e tokens; `resources/css/app.css` é a fonte de execução Tailwind.
- `app-page` é o contêiner canônico de páginas autenticadas.
- A scrollbar pertence globalmente a `resources/css/app.css`; regiões de tabela podem variar apenas em largura mínima e geometria.
- Abas são mantidas nos componentes `settings-tabs`, `help-tabs`, `family-health-tabs` e `territorial-bonding-tabs`.
- Formulários usam controles nativos, labels visíveis, mensagens inline e foco visível. Selects permanecem nativos porque o popup do sistema operacional é aceito.
- Feedback persistente é inline. Não existe provedor global de toast; mensagens críticas nunca dependem exclusivamente de toast.

## Navegação e responsividade

- A sidebar desktop transforma-se em cabeçalho e drawer abaixo de `lg`.
- Breadcrumbs e títulos podem quebrar em múltiplas linhas; ações adjacentes empilham no celular.
- Abas preservam uma linha e rolam horizontalmente dentro do próprio componente.
- Tabelas densas mantêm uma largura mínima legível e rolam horizontalmente dentro de uma região com scrollbar visível.
- Cards começam em uma coluna; métricas compactas usam duas colunas no celular e ampliam nos breakpoints documentados.
- Modais têm gutter mínimo, altura máxima baseada em `100dvh` e rolagem interna. O conteúdo atrás do modal não determina sua altura.
- Conteúdo textual variável pode quebrar; identificadores tabulares continuam em fonte monoespaçada e usam scroll/truncamento somente quando o valor completo permanece acessível.

## Comportamento de componentes

| Componente | Contrato |
|---|---|
| Botão | Elemento nativo, hover, foco visível, estado ocupado/desabilitado e alvo tátil de 44px em ponteiro grosso. |
| Campo | Label associado, largura fluida, erro textual junto ao campo e valor preservado após falha. |
| Busca | Debounce de 300 ms para Livewire; limpar restaura resultados e mantém o fluxo na mesma tela. |
| Tabela | Cabeçalho semântico, estado vazio, paginação quando aplicável e rolagem pertencente ao contêiner. |
| Diálogo | Superfície da aplicação, título acessível, ação de fechar, gutter responsivo e rolagem interna. |
| Processamento | Estado pessimista, prevenção de ação concorrente e progresso textual equivalente à barra visual. |

## Fluxos

| Operação | Pendente | Sucesso | Falha e recuperação |
|---|---|---|---|
| Criar/editar usuário | Controle ocupado no próprio modal | Fecha o modal e mostra feedback inline na lista | Preserva campos e mostra erros inline |
| Excluir usuário | Confirmação própria da aplicação | Atualiza a lista e mostra feedback inline | Mantém contexto e explica a falha |
| Buscar/filtrar | Atualização Livewire local | Atualiza lista/tabela na mesma rota | Mantém filtros para nova tentativa |
| Upload CNES/XML | Indicador junto ao campo | Exibe prévia antes da confirmação | Mantém a tela e descreve o erro |
| Processar dados | Barra estimada e texto de etapa | 100% somente após resposta do servidor | Estado de erro com mensagem e controles liberados |

## Resiliência e segurança

- Mutações são pessimistas e ações concorrentes são desabilitadas durante processamento.
- A interface web lê snapshots locais; PostgreSQL do PEC é acessado somente por processamento autorizado em CLI/serviço.
- Dados pessoais exibidos em tabelas nominais mantêm as máscaras e controles já definidos pelo módulo.
- A aplicação não usa `alert()`, `confirm()` ou `prompt()` nativos para ações de produto.

## Verificação

- Comandos obrigatórios: `npm run build`, `vendor/bin/pint --test`, `php artisan test`, `php artisan view:cache` e auditoria premium estrita.
- Matriz visual: 320x568, 390x844, 768x1024, 1024x768 e 1440x900; validar ausência de overflow no documento, drawer, abas, tabelas, formulários e modais.
- Fluxo irmão canônico: páginas de Configurações para cabeçalho/abas/formulários; Relação Nominal para tabelas densas e modais.
