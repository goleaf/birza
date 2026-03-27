<?php

namespace App\Livewire\Frontend\Auth;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Auth\AuthenticationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class VerifyEmail extends Component
{
    public string $userType = 'buyer';

    public function mount(string $hash): void
    {
        $segment = request()->segment(1);

        if (! in_array($segment, ['buyer', 'seller'], true)) {
            abort(404);
        }

        $this->userType = $segment;

        $modelClass = $this->userType === 'buyer' ? Buyer::class : Seller::class;
        $user = $modelClass::where('remember_token', $hash)->firstOrFail();

        if (! hash_equals((string) $hash, (string) $user->remember_token)) {
            throw new AuthenticationException(__('messages_verification_required'));
        }

        if ($user->is_verified) {
            session()->flash('success', __('messages_email_already_verified'));
            $this->redirectRoute("{$this->userType}.login", navigate: true);
            return;
        }

        $user->is_active = true;
        $user->is_verified = true;
        $user->remember_token = null;
        $user->save();

        session()->flash('success', __('messages_verification_success'));
        $this->redirectRoute("{$this->userType}.login", navigate: true);
    }

    public function render()
    {
        return view('livewire.frontend.auth.verify-email');
    }
}


