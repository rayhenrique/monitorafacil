<?php

namespace App\Livewire\Help;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FillingGuide extends Component
{
    public string $activeCategory = 'esf'; // 'esf' | 'esb' | 'emulti' | 'cadastros'

    public string $search = '';

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    public function render(): View
    {
        return view('livewire.help.filling-guide');
    }
}
