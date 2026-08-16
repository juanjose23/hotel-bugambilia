<x-mail::message>
# {{ $datos->title }}

{{ $datos->body }}

@if (! empty($datos->actions))
@foreach ($datos->actions as $action)
@if (method_exists($action, 'getUrl') && $action->getUrl() !== null)
<x-mail::button :url="$action->getUrl()">
{{ $action->getLabel() }}
</x-mail::button>
@endif
@endforeach
@endif

Saludos,<br>
**{{ config('app.name') }}** — Hotel Bugambilias
</x-mail::message>
