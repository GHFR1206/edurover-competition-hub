{{--
    RoboRace role footer (dark band + wave + checkerboard).
    Props: $variant — 'default' (strong/span/div/small), 'juri' (hr + wave asset),
    'operator' (footer-wave + content wrapper). Defaults to 'default'.
--}}
@php($v = $variant ?? 'default')

@if ($v === 'operator')
    <div class="footer-wave">
        <img src="{{ asset('assets/c9747.svg') }}" alt="">
    </div>
    <footer>
        <div class="footer-content">
            <strong>RoboRace</strong>
            <span>Powered by Creativity. Driven by Technology.</span>
            <hr>
            <small>© 2026 RoboRace. All Rights Reserved.</small>
        </div>
        <div class="checker"></div>
    </footer>
@elseif ($v === 'wave')
    <div class="footer-wave" aria-hidden="true">
        <img src="{{ asset('assets/c9747.svg') }}" alt="">
    </div>
    <footer>
        <strong>RoboRace</strong>
        <span>Powered by Creativity. Driven by Technology.</span>
        <div></div>
        <small>© 2026 RoboRace. All Rights Reserved.</small>
    </footer>
    <div class="checkerboard" aria-hidden="true"></div>
@elseif ($v === 'juri')
    <footer class="{{ ($dark ?? false) ? 'dark' : '' }}">
        <strong>RoboRace</strong>
        <span>Powered by Creativity. Driven by Technology.</span>
        <hr>
        <small>© 2026 RoboRace. All Rights Reserved.</small>
        <div class="checker"></div>
    </footer>
@else
    <footer>
        <strong>RoboRace</strong>
        <span>Powered by Creativity. Driven by Technology.</span>
        <i></i>
        <small>© 2026 RoboRace. All Rights Reserved.</small>
        <div class="checker"></div>
    </footer>
@endif
