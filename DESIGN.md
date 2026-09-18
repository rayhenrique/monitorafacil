---
version: alpha
name: "Saúde Brasil 360 Monitor"
description: "Painel municipal de atenção primária com leitura rápida dos consolidados quadrimestrais."
colors:
  primary: "#0f766e"
  canvas: "#f5f7f6"
  ink: "#16302c"
  muted: "#58716b"
  line: "#dce6e2"
  white: "#ffffff"
  danger: "#b91c1c"
typography:
  sans:
    fontFamily: "Segoe UI, ui-sans-serif, system-ui, sans-serif"
  mono:
    fontFamily: "ui-monospace, monospace"
rounded:
  DEFAULT: "0.75rem"
  sm: "0.5rem"
  md: "0.75rem"
  lg: "1rem"
spacing:
  section-gap: "2.5rem"
  page-max: "80rem"
components:
  button: {}
  card: {}
  input: {}
  badge: {}
---

# Saúde Brasil 360 · Direção visual

## Overview

### Creative North Star

Uma folha de acompanhamento de saúde pública: dados claros, rótulos explícitos e espaço para comparar períodos sem ruído visual.

### Product context and register

- **Audience and primary job:** gestores municipais que consultam equipes, cadastros e projeções consolidadas.
- **Target market:** municípios brasileiros, conforme PRD.md e MVP-SCOPE.md.
- **Locale and language:** interface em português brasileiro; números e moedas seguem `pt-BR`.
- **Usage scene:** consulta recorrente em desktop e celular, com pouco tempo para interpretar resultados.
- **Register:** ferramenta institucional; o login e o painel compartilham a identidade municipal.
- **Memorable signature:** faixa de período e indicadores agrupados por tema.
- **Restraint:** números e contexto prevalecem sobre ornamentos.
- **Anti-references:** visual de marketing, gradientes decorativos e semáforos sem legenda.
- **Token ownership/runtime mapping:** este arquivo documenta os tokens implementados em `resources/css/app.css` com Tailwind 4; mudanças de cor ou tipografia devem atualizar ambos.

## Colors

Canvas claro e superfícies brancas. `ink` é o texto principal, `muted` os rótulos, `line` separa cartões. Verde petróleo indica ações e foco. Alertas usam texto, ícone e cor juntos. Não há modo escuro nesta fase.

## Typography

Segoe UI e fallbacks de sistema permitem leitura rápida em português. Cabeçalhos são curtos. Contagens têm algarismos destacados e unidades sempre visíveis.

## Layout

Largura máxima de 80rem. Header flexível, conteúdo com margem lateral responsiva, cartões em grade quando houver espaço. Estados vazios preservam dimensões legíveis e explicam a ausência de dados.

## Elevation & Depth

Bordas leves definem agrupamentos. A sombra `shadow-panel` é sutil e reservada para cartões de conteúdo. Não usar sombra em cada elemento interno.

## Shapes

Cartões de 1rem; campos e botões de 0.5rem. Logotipo ou monograma em bloco de canto arredondado.

## Components

### Foundational visual states

Padrão em fundo branco; hover altera borda ou superfície; foco visível tem contorno de 2px em verde. Seleção combina cor e texto. Desabilitado reduz contraste somente junto de texto explicativo. Carregamento usa estado textual estável; erro e sucesso são descritos por texto.

### Buttons and actions

Botão principal sólido; secundário com borda. Ação de sair é secundária. Campos e botões têm alvo confortável e indicador de foco.

### Navigation and data display

Uma rota principal do painel. Grupos de dados usam título, período e unidade. Comparações não dependem apenas da cor.

### Forms and overlays

Labels externos e mensagens de erro abaixo do campo. Formulários mostram estado de submissão sem mover o layout. Sem overlays nesta fase.

### Iconography

Símbolos simples e poucos; texto acompanha cada ação e indicador essencial.

### Motion

Transições discretas de hover. Respeitar `prefers-reduced-motion`; não animar números financeiros.

### Content and data visualization

Tom objetivo e institucional. Usar separadores de milhar e moeda `pt-BR`, ano e quadrimestre completos e fontes de dados identificadas.

## Do's and Don'ts

- **Do:** manter explícitos período, unidade e origem dos números.
- **Do:** mostrar fallback para nome e logo ainda não configurados.
- **Don't:** esconder ausência de consolidação atrás de zero.
- **Don't:** usar cor como único sinal de classificação.
- **REGRA INVIOLÁVEL (ORIGINALIDADE DO DESIGN):** Em nenhuma hipótese copiar ou replicar o design, cores, fontes, cabeçalhos ou componentes visuais de sistemas ou páginas de referência externas (ex: DashSaúde). Manter estritamente a identidade e o design system próprio do **Monitora Fácil** (sidebar executiva escura `#0c1f1c`, degradê esmeralda `#0f766e`/`#10b981`, canvas limpo `#f5f7f6` e badges `MF`). Páginas de referência servem **apenas e exclusivamente** como inspiração para descoberta de módulos e requisitos funcionais, sendo implementados de forma compassiva e gradual.

