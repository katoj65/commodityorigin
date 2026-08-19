@component('mail::message')
{!! $body !!}

@if($actionText && $actionUrl)
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
