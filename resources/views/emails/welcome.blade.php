@component('mail::message')
# 안녕하세요  {{$user->name}} 님

회원가입을 축하드립니다. 아래 링크를 이용하여 사용자인증을 해주시기 바랍니다
@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
