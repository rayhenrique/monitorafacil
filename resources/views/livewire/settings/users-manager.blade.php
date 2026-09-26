<div class="app-page">
    <x-settings-tabs
        title="Usuários do Sistema"
        subtitle="Gerencie os operadores e administradores com permissão de acesso ao Monitora Fácil"
        activeTab="users"
    />

    <!-- Alerts / Flash Messages -->
    @if (session()->has('message') || session()->has('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-sm text-emerald-900 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('message') ?? session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-900 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="rounded-3xl border border-line bg-white shadow-sm overflow-hidden">
        <!-- Barra de Ações: Busca & Novo Usuário -->
        <div class="flex flex-col items-stretch justify-between gap-4 border-b border-line bg-slate-50/50 p-4 sm:flex-row sm:items-center sm:p-5">
            <div class="relative w-full sm:w-80">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nome, e-mail ou UBS..."
                    class="block w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                >
            </div>

            <button
                type="button"
                wire:click="create"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/30 cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Novo Usuário</span>
            </button>
        </div>

        <!-- Tabela de Usuários -->
        <div class="overflow-x-auto">
            <table class="min-w-[50rem] divide-y divide-line text-left text-xs text-ink">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Usuário</th>
                        <th scope="col" class="px-6 py-3.5">E-mail</th>
                        <th scope="col" class="px-6 py-3.5">Perfil & Acesso</th>
                        <th scope="col" class="px-6 py-3.5">Data de Criação</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-800 text-xs font-bold text-teal-100 shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-ink flex items-center gap-2">
                                            <span>{{ $user->name }}</span>
                                            @if ($user->id === auth()->id())
                                                <span class="inline-flex items-center rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-medium text-teal-800">Você</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-muted font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->isAdmin())
                                    <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Administrador</span>
                                        <span class="text-[10px] text-emerald-600 font-normal">· Acesso Municipal</span>
                                    </div>
                                @else
                                    <div class="space-y-0.5">
                                        <div class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 border border-teal-200/80 px-2.5 py-0.5 text-[11px] font-semibold text-teal-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                                            <span>Operador</span>
                                            @if ($user->cnes)
                                                <span class="font-mono text-[10px] text-teal-600">CNES {{ $user->cnes }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-medium text-slate-700 max-w-xs truncate" title="{{ $user->facility_name ?: 'UBS não informada' }}">
                                            {{ $user->facility_name ?: 'UBS vinculada' }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-muted tabular-nums">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="edit({{ $user->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition"
                                        title="Editar usuário"
                                    >
                                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    @if ($user->id !== auth()->id())
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $user->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 transition"
                                            title="Excluir usuário"
                                        >
                                            <svg class="h-3.5 w-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span>Excluir</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <p class="font-medium text-slate-600">Nenhum usuário encontrado</p>
                                    <p class="text-xs text-muted">Tente ajustar seus termos de busca.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if ($users->hasPages())
            <div class="border-t border-line px-6 py-3.5 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Criar / Editar Usuário -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4">
            <div class="app-modal-panel relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:rounded-3xl sm:p-6" @click.outside="$wire.closeModal()">
                <div class="flex items-center justify-between border-b border-line pb-4 mb-5">
                    <h3 class="text-lg font-bold text-ink">
                        {{ $isEditing ? 'Editar Usuário' : 'Novo Usuário' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="userName" class="block text-xs font-semibold text-ink mb-1">Nome Completo</label>
                        <input
                            type="text"
                            id="userName"
                            wire:model="name"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Ex: Carlos Silva"
                        >
                        @error('name') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="userEmail" class="block text-xs font-semibold text-ink mb-1">E-mail de Acesso</label>
                        <input
                            type="email"
                            id="userEmail"
                            wire:model="email"
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            placeholder="Ex: usuario@municipio.gov.br"
                        >
                        @error('email') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Perfil de Acesso -->
                    <div>
                        <label class="block text-xs font-semibold text-ink mb-1.5">Perfil de Acesso</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition {{ $role === 'operator' ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-500/20' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="role" value="operator" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <div class="text-xs font-semibold text-ink">Operador de UBS</div>
                                    <div class="text-[11px] text-muted">Acesso restrito a uma UBS e suas equipes</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition {{ $role === 'admin' ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-500/20' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="role" value="admin" class="mt-0.5 text-teal-600 focus:ring-teal-500">
                                <div>
                                    <div class="text-xs font-semibold text-ink">Administrador</div>
                                    <div class="text-[11px] text-muted">Acesso municipal irrestrito</div>
                                </div>
                            </label>
                        </div>
                        @error('role') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Vinculação à UBS (Obrigatório se Operador) -->
                    @if ($role === 'operator')
                        <div class="rounded-2xl border border-teal-200/90 bg-teal-50/50 p-4 space-y-2">
                            <label for="userCnes" class="block text-xs font-semibold text-teal-950">
                                Unidade Básica de Saúde vinculada (UBS / CNES) <span class="text-rose-600">*</span>
                            </label>
                            <select
                                id="userCnes"
                                wire:model.live="cnes"
                                class="w-full rounded-xl border border-teal-300 bg-white px-3.5 py-2.5 text-xs text-ink focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            >
                                <option value="">Selecione o estabelecimento de saúde...</option>
                                @foreach ($facilities as $facility)
                                    <option value="{{ $facility->cnes }}">
                                        {{ $facility->facility_name }} (CNES: {{ $facility->cnes }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="flex items-center gap-1.5 text-[11px] text-teal-800">
                                <svg class="h-3.5 w-3.5 text-teal-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>O operador enxerga todas as equipes e cadastros desta UBS.</span>
                            </div>
                            @error('cnes') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-600 flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-emerald-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Administradores possuem acesso total a todas as unidades, equipes, coortes e configurações de todo o município (não necessita vinculação).</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="userPassword" class="block text-xs font-semibold text-ink mb-1">
                                Senha {{ $isEditing ? '(opcional)' : '' }}
                            </label>
                            <input
                                type="password"
                                id="userPassword"
                                wire:model="password"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                                placeholder="{{ $isEditing ? 'Manter atual...' : 'Mínimo 8 dígitos' }}"
                            >
                            @error('password') <span class="text-[11px] text-rose-600 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="userPasswordConfirmation" class="block text-xs font-semibold text-ink mb-1">Confirmar Senha</label>
                            <input
                                type="password"
                                id="userPasswordConfirmation"
                                wire:model="password_confirmation"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-ink placeholder-slate-400 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                                placeholder="Repita a senha"
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-line pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition sm:w-auto"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-teal-700 px-5 py-2 text-xs font-semibold text-white hover:bg-teal-800 shadow-sm transition sm:w-auto"
                        >
                            {{ $isEditing ? 'Salvar Alterações' : 'Cadastrar Usuário' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Exclusão -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-xs animate-fade-in sm:p-4">
            <div class="app-modal-panel relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl sm:rounded-3xl sm:p-6" @click.outside="$wire.closeDeleteModal()">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-bold text-ink">Confirmar Exclusão</h3>
                        <p class="text-xs text-muted mt-1">
                            Tem certeza que deseja excluir o usuário <span class="font-semibold text-ink">{{ $userToDeleteName }}</span>? Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-line pt-4 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition sm:w-auto"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        class="w-full rounded-xl bg-rose-600 px-5 py-2 text-xs font-semibold text-white hover:bg-rose-700 shadow-sm transition sm:w-auto"
                    >
                        Excluir Usuário
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
