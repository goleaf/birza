<?php

namespace App\Livewire\Frontend\Auth;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class ForgotPassword extends Component
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
    }

    public function sendResetLink(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
        ]);

        $modelClass = $this->userType === 'buyer' ? Buyer::class : Seller::class;

        /** @var Buyer|Seller|null $user */
        $user = $modelClass::where('email', strtolower($validated['email']))->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => __('passwords_user'),
            ]);
        }

        $token = sha1(Str::random(40));
        $user->remember_token = $token;
        $user->password_reset_at = now();
        $user->save();

        $resetUrl = route("{$this->userType}.password.reset", ['hash' => $token]);
        $message = __('emails_reset_password_body') . "\n\n" . $resetUrl;

        Mail::raw($message, function ($mail) use ($user) {
            $mail->to($user->email)->subject(__('emails_reset_password_subject'));
        });

        session()->flash('status', __('passwords_reset_for_email', ['email' => $user->email]));
    }

    public function render()
    {
        return view('livewire.frontend.auth.forgot-password');
    }
}


