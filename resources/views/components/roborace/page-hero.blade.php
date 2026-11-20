{{--
    RoboRace page hero for secondary public pages.
    Props: $title, $description, $mascot, $page.
--}}
<section class="page-hero">
    <div>
        <x-roborace.live-badge :date="$date ?? null" :series="$series ?? null" />
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </div>
    <x-roborace.mascot :src="$mascot" />
</section>
