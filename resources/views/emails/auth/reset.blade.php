@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

<p>pin code is {{$code}}</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
