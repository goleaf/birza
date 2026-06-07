<?php

namespace App\Livewire\Frontend\Auth;

use App\Actions\Cart\MergeGuestCartAction;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Register extends Component
{
    public string $userType = 'buyer';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?string $userType = null): void
    {
        $segment = $userType ?? request()->segment(1);

        if (! in_array($segment, ['buyer', 'seller'], true)) {
            abort(404);
        }

        $this->userType = $segment;

        if (Auth::guard($this->userType)->check()) {
            $this->redirectRoute("{$this->userType}.dashboard");
        }
    }

    public function register(MergeGuestCartAction $mergeGuestCartAction): void
    {
        if ($this->userType === 'buyer') {
            $validated = $this->validate([
                'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users_buyers,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $buyer = new Buyer;
            $buyer->forceFill([
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'is_verified' => true,
                'is_active' => true,
            ]);
            $buyer->save();

            Auth::guard('buyer')->login($buyer);
            session()->regenerate();

            if (session()->has('cart_guest_token')) {
                $mergeGuestCartAction->handle((string) session('cart_guest_token'), $buyer);
                session()->forget('cart_guest_token');
            }

            if (empty($buyer->company_name) || empty($buyer->company_code) || empty($buyer->address) || empty($buyer->phone)) {
                session()->flash('warning', __('profile_complete_profile'));
                $this->redirectRoute('buyer.profile.edit');

                return;
            }

            session()->flash('success', __('messages_registration_success'));
            $this->redirectRoute('buyer.dashboard');

            return;
        }

        // seller registration
        $validated = $this->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users_sellers,email'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $verificationToken = Str::random(64);

        $seller = new Seller;
        $seller->forceFill([
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'remember_token' => $verificationToken,
            'is_active' => true,
            'is_verified' => false,
        ]);
        $seller->save();

        $verificationUrl = route('seller.verification.verify', ['hash' => $verificationToken]);
        $verificationMessage = __('emails_verify_email_body')."\n\n".$verificationUrl;

        Mail::raw($verificationMessage, function ($mail) use ($seller) {
            $mail->to($seller->email)->subject(__('emails_verify_email_subject'));
        });

        session()->flash('registration_success', true);
        session()->flash('registered_email', $seller->email);

        $this->redirectRoute('seller.register.success');
    }

    public function render()
    {
        return view('livewire.frontend.auth.register');
    }
}
