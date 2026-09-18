<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UsersManager extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $showDeleteModal = false;

    public ?int $userToDeleteId = null;

    public string $userToDeleteName = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->isEditing
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'O nome do usuário é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['userId', 'name', 'email', 'password', 'password_confirmation']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->isEditing && $this->userId !== null) {
            $user = User::findOrFail($this->userId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if (filled($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);
            session()->flash('success', 'Usuário atualizado com sucesso!');
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            session()->flash('success', 'Novo usuário cadastrado com sucesso!');
        }

        $this->showModal = false;
        $this->reset(['userId', 'name', 'email', 'password', 'password_confirmation']);
    }

    public function confirmDelete(int $id): void
    {
        if ($id === Auth::id()) {
            session()->flash('error', 'Você não pode excluir o usuário atualmente conectado.');

            return;
        }

        $user = User::findOrFail($id);
        $this->userToDeleteId = $user->id;
        $this->userToDeleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->userToDeleteId === Auth::id()) {
            session()->flash('error', 'Você não pode excluir a si mesmo.');
            $this->showDeleteModal = false;

            return;
        }

        if ($this->userToDeleteId !== null) {
            User::where('id', $this->userToDeleteId)->delete();
            session()->flash('success', 'Usuário excluído com sucesso.');
        }

        $this->showDeleteModal = false;
        $this->reset(['userToDeleteId', 'userToDeleteName']);
    }

    public function create(): void
    {
        $this->openCreateModal();
    }

    public function edit(int $id): void
    {
        $this->openEditModal($id);
    }

    public function delete(): void
    {
        $this->deleteUser();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['userId', 'name', 'email', 'password', 'password_confirmation']);
        $this->resetValidation();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['userToDeleteId', 'userToDeleteName']);
    }

    public function render(): View
    {
        $users = User::query()
            ->when(filled($this->search), function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.settings.users-manager', [
            'users' => $users,
        ]);
    }
}
