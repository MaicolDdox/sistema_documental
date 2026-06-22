<?php

namespace App\Livewire\Shared;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class UserNameDisplay extends Component
{
    public string $mode = 'sidebar'; // 'sidebar' | 'topbar'
    public string $displayName = '';
    public string $displayEmail = '';
    public string $initials = '';

    public function mount(string $mode = 'sidebar'): void
    {
        $this->mode = $mode;
        $this->refresh();
    }

    #[On('profile-updated')]
    public function refresh(): void
    {
        $user = Auth::user();
        $user->load('person');

        $this->displayName  = $user->name;
        $this->displayEmail = $user->email ?? '';
        $this->initials     = strtoupper(substr($this->displayName, 0, 2));
    }

    public function render()
    {
        return view('livewire.shared.user-name-display');
    }
}
