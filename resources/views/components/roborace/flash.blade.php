{{--
    Shared flash messages for the role layouts. Every controller in the app
    redirects with `->with('status', ...)` or `->withErrors(...)`, so without
    this component the judge, operator and coach screens silently swallow
    success and validation messages.

    Styled by account.css under `.rr-flash` so it works in any role design.
--}}
@if (session('status'))
    <div class="rr-flash rr-flash-success" role="status">{{ session('status') }}</div>
@endif
@if (isset($errors) && $errors->any())
    <div class="rr-flash rr-flash-error" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
