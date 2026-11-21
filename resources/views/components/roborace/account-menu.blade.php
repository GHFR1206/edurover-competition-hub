@php
    $accountUser = auth()->user();
    $initial = $accountUser ? mb_strtoupper(mb_substr($accountUser->name, 0, 1)) : '?';
@endphp

{{--
    Shared account menu for every role layout: Dashboard, Profil, Keluar.
    Uses <details> so it works without JavaScript. Styled by account.css
    under the .rr-account namespace so it never collides with role styles.
--}}
<details class="rr-account">
    <summary aria-label="Menu akun {{ $accountUser?->name }}">
        <span class="rr-account-avatar">{{ $initial }}</span>
        <span class="rr-account-name">{{ $accountUser?->name }}</span>
    </summary>
    <div class="rr-account-menu">
        <a href="{{ route('dashboard') }}">Dasbor</a>
        @if ($accountUser?->isStaff())
            <a href="/admin">Panel panitia</a>
        @endif
        <a href="{{ route('profile.edit') }}">Profil &amp; keamanan</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </div>
</details>
