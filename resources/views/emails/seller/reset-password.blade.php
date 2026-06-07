@component('mail::message')
# {{ __('emails.seller.password_reset.title') }}

{{ __('emails.seller.password_reset.intro') }}

@component('mail::button', ['url' => $resetUrl])
{{ __('emails.seller.password_reset.action') }}
@endcomponent

{{ __('emails.seller.password_reset.expiry') }}

{{ __('emails.seller.password_reset.ignore') }}

{{ __('common_regards') }},<br>
{{ config('app.name') }}
@endcomponent 
