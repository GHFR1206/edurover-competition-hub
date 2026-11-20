{{-- RoboRace mascot image. Props: $src (file in /assets), $class. --}}
<img class="mascot float {{ $class ?? '' }}" src="{{ asset('assets/'.$src) }}" alt="Maskot RoboRace">
