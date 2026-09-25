# Skill: design-system-spec (MonitoraFácil / Saúde Brasil 360)

## Propósito
Fornecer regras estritas de UI/UX em Tailwind CSS 4 e Blade/Livewire v3 em total consonância com as regras corporativas de `DESIGN.md`.

## 1. Tokens de Cores Rigorosos
- **Primary / Action:**
  - Principal (Botões primários, links ativos, destaques): `bg-teal-700 text-white hover:bg-teal-800` (`#0f766e`)
  - Foco / Contorno: `ring-2 ring-teal-600`
  - Fundo sutil / seleção: `bg-teal-50 text-teal-800 border-teal-200`
- **Surface & Neutros:**
  - Canvas (fundo da página): `bg-[#f5f7f6]`
  - Superfícies (Cards, Containers, Tabelas): `bg-white border border-[#dce6e2]`
  - Tipografia Principal: `text-[#16302c]` (Ink)
  - Rótulos e Metadados: `text-[#58716b]` (Muted)
  - Divisores e Linhas: `border-[#dce6e2]` (Line)
- **Ações Secundárias e Controles:**
  - Botão Voltar / Cancelar: `bg-white text-[#16302c] border border-[#dce6e2] hover:bg-slate-50` (PROIBIDO uso de laranja).
  - Botão Busca / Filtros: `bg-white text-teal-800 border border-teal-300 hover:bg-teal-50` ou `bg-teal-700 text-white` (PROIBIDO azul royal genérico).
  - Botão Exportar (CSV/PDF): `bg-slate-100 text-[#16302c] border border-[#dce6e2] hover:bg-slate-200`.

## 2. Regras de Componentes e Layout
- **Container Canônico:** Todo conteúdo deve estar envolto pela classe de página canônica com padding responsivo: `px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto`.
- **Shapes e Raios:**
  - Cartões (`Cards` / `Containers`): `rounded-2xl` (ou `rounded-xl`), com borda sutil `border border-[#dce6e2]` e `shadow-sm`.
  - Botões, Inputs e Badges: `rounded-lg` (8px).
- **Abas de Navegação (Sub-nav):**
  - Estilo container pill limpo: `bg-slate-100 p-1 rounded-xl inline-flex gap-1`.
  - Aba ativa: `bg-white text-teal-800 font-semibold shadow-xs rounded-lg px-4 py-2 text-sm`.
  - Aba inativa: `text-[#58716b] hover:text-[#16302c] px-4 py-2 text-sm font-medium`.
- **Tabelas de Dados (Data Tables):**
  - Wrapper: `bg-white rounded-2xl border border-[#dce6e2] shadow-xs overflow-hidden`.
  - Thead: `bg-[#f5f7f6] text-[#58716b] text-xs font-semibold uppercase tracking-wider border-b border-[#dce6e2] px-4 py-3`.
  - Linhas (Tr): `hover:bg-teal-50/30 transition-colors border-b border-[#dce6e2] text-sm text-[#16302c]`.
  - Paginação: Integrada no card footer, sem quebrar layout em telas móveis.
- **Badges de Classificação e-SUS (Semáforo Institucional):**
  - Ótimo: `bg-emerald-50 text-emerald-700 border border-emerald-200`
  - Bom: `bg-teal-50 text-teal-700 border border-teal-200`
  - Suficiente: `bg-amber-50 text-amber-700 border border-amber-200`
  - Regular: `bg-rose-50 text-rose-700 border border-rose-200`
  - Sempre com texto explícito do conceito (nunca apenas uma bolinha de cor sem rótulo).

## 3. Regras Invioláveis do DESIGN.md
1. **Sem cópias externas:** Preservar a identidade do MonitoraFácil (paleta teal/petróleo `#0f766e` e canvas `#f5f7f6`).
2. **Rodapé Único:** O rodapé é exclusivo de `layouts/app.blade.php`. Nenhuma view ou componente filho deve renderizar notas de rodapé ou número de versão.