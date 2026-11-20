{{--
    Realtime meta tags. A page opts into live updates by passing $liveEvent,
    $liveCategory and/or $liveArena. Without them no websocket is opened.
--}}
<meta name="reverb-key" content="{{ config('realtime.key') }}">
<meta name="reverb-host" content="{{ config('realtime.host') }}">
<meta name="reverb-port" content="{{ config('realtime.port') }}">
<meta name="reverb-scheme" content="{{ config('realtime.scheme') }}">
@isset($liveEvent)
    <meta name="live-event" content="{{ $liveEvent }}">
@endisset
@isset($liveCategory)
    <meta name="live-category" content="{{ $liveCategory }}">
@endisset
@isset($liveArena)
    <meta name="live-arena" content="{{ $liveArena }}">
@endisset
