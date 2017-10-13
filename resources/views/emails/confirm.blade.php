@component('mail::message')
# 안녕하세요  {{$user->name}} 님

이메일이 변경되어 재 인증이 필요합니다 아래 링크를 이용하여 인증 해주시기 바랍니다

@component('mail::button', ['url' => route('verify', $user->verification_token)])
    Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
