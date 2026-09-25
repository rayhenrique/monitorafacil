---
name: livewire-screen-generator
description: >
  Criação e refatoração padronizada de componentes Livewire v3 e views Blade
  no Monitora Fácil. Garante conformidade total com o UX-CONTRACT.md, DESIGN.md
  e as diretrizes institucionais do projeto. Implementa WithPagination (Tailwind),
  validação #[Validate], busca com debounce de 300ms, componente x-processing-progress,
  tabelas com scroll horizontal acessível, modais responsivos e a aplicação
  estrita da Política de Rodapé Único (NUNCA duplicar rodapé).
---

# Livewire Screen Generator

Esta skill padroniza o design, a experiência do usuário (UX) e o código para novos componentes e refatorações de telas em **Livewire v3** no **Monitora Fácil**, garantindo conformidade rigorosa com `UX-CONTRACT.md` e `DESIGN.md`.

---

## 1. Gatilhos de Ativação

Ative esta skill quando a tarefa envolver:
- Criação de novas telas ou páginas completas da aplicação (ex: nova tela de busca ativa, nova aba em Saúde da Família, configurações).
- Refatoração de componentes Livewire (`app/Livewire/...`) e suas respectivas views Blade (`resources/views/livewire/...`).
- Adição de novos fluxos de filtros, paginação, exportação e modais de diálogo.
- Inclusão de indicadores visuais de progresso para tarefas demoradas (DW sync, snapshots).

---

## 2. Regras Mandatórias de Arquitetura e UX

### Regra 1: Política de Rodapé Único (NUNCA DUPLICAR O RODAPÉ)
- **CRÍTICO:** O único rodapé permitido em todo o sistema é o rodapé global renderizado no layout principal em `resources/views/layouts/app.blade.php`.
- **NUNCA** insira rodapés institucionais, de créditos (como "PWDEV_"), de versão ou de copyright dentro de páginas, views filhas ou componentes Livewire/Blade.
- Se for identificada duplicidade de rodapé em qualquer tela gerada ou refatorada, remova o rodapé interno imediatamente.

### Regra 2: Contêiner Canônico `app-page`
- Todas as rotas autenticadas e views de página devem encapsular o conteúdo dentro de:
  `<div class="app-page"> ... </div>`
- O contêiner aplica os gutters responsivos documentados:
  - Mobile: `1rem` (`p-4`)
  - Tablet: `1.5rem` (`sm:p-6`)
  - Desktop: `2rem` (`lg:p-8`) com max-width `80rem` (`max-w-7xl`).

### Regra 3: Tokens de Design (`DESIGN.md`)
Utilize as classes utilitárias do Tailwind CSS correspondentes à identidade visual institucional:
- **Canvas / Fundo:** `#f5f7f6` (`bg-[#f5f7f6]` ou `bg-slate-50`)
- **Superfícies de Cards:** `#ffffff` com borda fina `#dce6e2` (`bg-white border border-slate-200/80 rounded-xl shadow-xs`)
- **Texto Principal (Ink):** `#16302c` (`text-slate-900` ou `text-ink`)
- **Texto Secundário / Rótulos (Muted):** `#58716b` (`text-slate-500` ou `text-muted`)
- **Acentos / Primária:** Esmeralda / Petróleo `#0f766e` (`text-teal-700`, `bg-teal-700`, `hover:bg-teal-800`, `focus:ring-teal-500`)
- **Perigo / Erro:** `#b91c1c` (`text-rose-700`, `bg-rose-50`, `border-rose-200`)

### Regra 4: Busca com Debounce de 300ms
- Sempre que houver campo de texto para busca dinâmica em coleções:
  `wire:model.live.debounce.300ms="search"`
- O método de atualização deve resetar a paginação (`$this->resetPage()`).

### Regra 5: Componente de Progresso `x-processing-progress`
- Para operações assíncronas ou de processamento de dados (ex: consolidação DW, snapshots), integre o componente de progresso existente:
  ```blade
  <x-processing-progress 
      :percent="$processingPercent" 
      :step="$processingStep" 
      :running="$isProcessing" 
      :status="$processingStatus" 
  />
  ```

### Regra 6: Tabelas Densas e Responsividade
- Tabelas de listagem nominal nunca devem quebrar a página horizontalmente.
- Encapsule a tabela em uma região com rolagem horizontal dedicada:
  `<div class="overflow-x-auto scrollbar-gutter-stable min-w-0 rounded-lg border border-slate-200">`
- Identificadores (CPF, CNS, INE, CNES) devem usar fonte monoespaçada: `font-mono text-xs`.

---

## 3. Template Base da Classe Livewire (`app/Livewire/...`)

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\NominalCitizen;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NominalListScreen extends Component
{
    use WithPagination;

    #[Validate('string|max:100')]
    public string $search = '';

    #[Validate('string|nullable')]
    public ?string $teamIne = null;

    #[Validate('string|in:all,pending,done')]
    public string $statusFilter = 'all';

    public bool $isProcessing = false;
    public int $processingPercent = 0;
    public string $processingStep = '';
    public ?string $processingStatus = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTeamIne(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'teamIne', 'statusFilter']);
        $this->resetPage();
    }

    public function processBatch(): void
    {
        $this->isProcessing = true;
        $this->processingPercent = 10;
        $this->processingStep = 'Iniciando extração do DW...';

        try {
            // Executa chamada ao serviço...
            $this->processingPercent = 100;
            $this->processingStep = 'Consolidação concluída com sucesso!';
            $this->processingStatus = 'success';
            $this->dispatch('notify', message: 'Processamento concluído com sucesso.');
        } catch (\Throwable $e) {
            $this->processingStatus = 'error';
            $this->processingStep = 'Erro: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render(): View
    {
        $query = NominalCitizen::query()
            ->when($this->teamIne, fn ($q, $ine) => $q->where('team_ine', $ine))
            ->when($this->search, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'ilike', "%{$term}%")
                        ->orWhere('cpf', 'like', "%{$term}%")
                        ->orWhere('cns', 'like', "%{$term}%");
                });
            });

        return view('livewire.nominal-list-screen', [
            'records' => $query->paginate(15),
        ]);
    }
}
```

---

## 4. Template Base da View Blade (`resources/views/livewire/...`)

```blade
<div class="app-page space-y-6">
    {{-- Cabeçalho da Página --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-muted mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-ink">Início</a>
                <span>/</span>
                <span class="text-ink font-medium">Busca Ativa</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Lista Nominal de Cidadãos</h1>
            <p class="mt-1 text-sm text-muted">Acompanhamento nominal e pendências prioritárias das equipes de Saúde da Família.</p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="processBatch"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-teal-800 focus:outline-hidden focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 transition-colors"
            >
                <span wire:loading.remove wire:target="processBatch">Atualizar Indicadores</span>
                <span wire:loading wire:target="processBatch">Processando...</span>
            </button>
        </div>
    </div>

    {{-- Indicador de Progresso (quando houver sincronização ativa) --}}
    @if ($isProcessing || $processingStatus)
        <x-processing-progress
            :percent="$processingPercent"
            :step="$processingStep"
            :running="$isProcessing"
            :status="$processingStatus"
        />
    @endif

    {{-- Barra de Filtros e Busca --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Buscar Cidadão</label>
                <div class="relative">
                    <input
                        id="search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nome, CPF ou CNS..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-ink placeholder-slate-400 focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600"
                    />
                </div>
            </div>

            <div>
                <label for="statusFilter" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Situação</label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-ink focus:border-teal-600 focus:outline-hidden focus:ring-1 focus:ring-teal-600"
                >
                    <option value="all">Todos os Cidadãos</option>
                    <option value="pending">Com Pendências Clínicas</option>
                    <option value="done">Em Dia (Todas as Práticas)</option>
                </select>
            </div>

            <div class="flex items-end">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-teal-500"
                >
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Tabela Nominal Responsiva com Rolagem Própria --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
        <div class="overflow-x-auto min-w-0">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Cidadão</th>
                        <th class="py-3 px-4">CPF / CNS</th>
                        <th class="py-3 px-4">Equipe (INE)</th>
                        <th class="py-3 px-4">Status Clínico</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($records as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-ink">{{ $item->name }}</div>
                                <div class="text-xs text-muted">{{ $item->birth_date?->format('d/m/Y') }} ({{ $item->age_years }} anos)</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                <div>{{ $item->cpf ?: '—' }}</div>
                                <div class="text-[11px] text-muted">{{ $item->cns ?: '—' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-700">
                                <div class="font-medium">{{ $item->team_name }}</div>
                                <div class="font-mono text-muted">INE: {{ $item->team_ine }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($item->is_up_to_date)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800 border border-emerald-200">
                                        Em Dia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800 border border-amber-200">
                                        {{ $item->pending_count }} Pendência(s)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" class="text-teal-700 hover:text-teal-900 font-semibold text-xs">
                                    Ver Ficha
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted">
                                <p class="text-sm">Nenhum cidadão encontrado para os filtros selecionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if ($records->hasPages())
            <div class="border-t border-slate-200 p-4">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- ATENÇÃO: NENHUM RODAPÉ DEVE SER INSERIDO AQUI. O RODAPÉ É EXCLUSIVO DO app.blade.php --}}
</div>
```

---

## 5. Checklist de Revisão de UI/UX

Ao finalizar a tela, valide:
- [ ] O contêiner canônico `app-page` está na raiz da view?
- [ ] O rodapé NÃO foi duplicado (regra fundamental do projeto)?
- [ ] O input de busca possui `wire:model.live.debounce.300ms`?
- [ ] As tabelas utilizam `overflow-x-auto` e classes de tipografia monoespaçada em identificadores?
- [ ] O componente `x-processing-progress` é exibido em tarefas assíncronas demoradas?
- [ ] Cores e tipografia respeitam `DESIGN.md` (acentos esmeralda `#0f766e`, fundo `#f5f7f6`).
