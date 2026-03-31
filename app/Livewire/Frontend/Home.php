<?php

namespace App\Livewire\Frontend;

use App\Actions\Auth\ResolveHomeRedirectAction;
use App\Actions\Frontend\BuildWelcomePageDataAction;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.blank')]
class Home extends Component
{
    public function mount(): void
    {
        $redirect = app(ResolveHomeRedirectAction::class)->handle(request());

        if ($redirect !== null) {
            $this->redirect($redirect->getTargetUrl(), navigate: true);
        }
    }

    public function render(): View
    {
        return view('frontend.welcome', app(BuildWelcomePageDataAction::class)->handle());
    }
}
