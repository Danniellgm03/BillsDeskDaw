@component('mail::message')
    # You Are Invited!

    You have been invited to join our platform. Click the button below to create your account.

    @component('mail::button', ['url' => $url])
        Register
    @endcomponent

    Thanks,
    Iludesk
@endcomponent
