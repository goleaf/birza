<?php

namespace App\Livewire\Frontend\Auth;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class RegisterSuccess extends Component
{
    public string $userType = 'buyer';
    public string $email = '';

    public function mount(): void
    {
        $segment = request()->segment(1);

        if (! in_array($segment, ['buyer', 'seller'], true)) {
            abort(404);
        }

        $this->userType = $segment;

        if (! session('registration_success')) {
            $this->redirectRoute("{$this->userType}.register", navigate: true);
            return;
        }

        $this->email = (string) session('registered_email');
    }

    public function resendVerification(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $modelClass = $this->userType === 'buyer' ? Buyer::class : Seller::class;
        $user = $modelClass::where('email', strtolower($this->email))->first();

        if (! $user) {
            $this->addError('email', __('auth_user_not_found'));
            return;
        }

        if ($user->is_verified) {
            session()->flash('success', __('messages_email_already_verified'));
            return;
        }

        if (RateLimiter::tooManyAttempts('verify:'.$user->id, 3)) {
            session()->flash('error', __('messages_verification_check'));
            return;
        }

        $user->remember_token = sha1(Str::random(40));
        $user->save();

        $verificationUrl = route("{$this->userType}.verification.verify", [
            'hash' => $user->remember_token,
        ]);

        $message = __('emails_verify_email_body') . "\n\n" . $verificationUrl;

        Mail::raw($message, function ($mail) use ($user) {
            $mail->to($user->email)->subject(__('emails_verify_email_subject'));
        });

        RateLimiter::hit('verify:'.$user->id);

        session()->flash('success', __('messages_verification_sent'));
    }

    public function render()
    {
        return view('livewire.frontend.auth.register-success');
    }
}


