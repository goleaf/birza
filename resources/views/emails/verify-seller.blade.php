@component('mail::message')
# Verify Your Seller Account

Thank you for registering as a seller! Please click the button below to verify your email address.

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

If you did not create this seller account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 