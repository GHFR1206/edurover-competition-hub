<?php

/**
 * The ported designs were built as fixed ~1120px canvases, while the Blade
 * layouts render navigation as anchors. These guards keep the two known
 * regressions from coming back: a "dead zone" of tablet widths with a forced
 * wide canvas, and unstyled nav links in the role layouts.
 */
function roleCss(string $name): string
{
    $path = resource_path("css/roborace/{$name}.css");

    expect(file_exists($path))->toBeTrue();

    return file_get_contents($path);
}

it('lets the judge layout shrink for tablet widths', function () {
    $css = roleCss('juri');

    // The desktop canvas is fixed at 1120px, so every width below that has to
    // be handled by the responsive block.
    expect($css)->toContain('@media (max-width: 1119px)')
        ->and($css)->not->toContain('@media (max-width: 600px)');

    // 768px must fall inside a responsive block that removes the fixed width.
    preg_match_all('/@media \(max-width: (\d+)px\)/', $css, $matches);

    $breakpoints = array_map('intval', $matches[1]);

    expect(min($breakpoints))->toBeGreaterThan(768);
});

it('styles the nav anchors rendered by the role layouts', function (string $role) {
    $css = roleCss($role);

    expect($css)->toContain('.navlinks a');
})->with(['juri', 'chief', 'super-admin']);

it('styles the event admin nav anchors', function () {
    $css = roleCss('event-admin');

    expect($css)->toContain('.navbar nav a')
        ->and($css)->toContain('.navbar nav a.active');
});

it('forces the mobile nav onto its own full-width row', function (string $role, string $selector) {
    $css = roleCss($role);

    // Find the media block that actually contains the nav override and assert
    // it beats the desktop `flex: 1` (basis 0) rule. Without that the links
    // stay on the brand row and the last items are clipped.
    $found = false;

    preg_match_all('/@media \(max-width: \d+px\) \{(.*?)\n\}/s', $css, $matches);

    foreach ($matches[1] as $block) {
        if (str_contains($block, $selector) && str_contains($block, 'flex: 1 1 100%')) {
            $found = true;
            break;
        }
    }

    expect($found)->toBeTrue("{$role}.css has no mobile nav row override for {$selector}");
})->with([
    ['juri', '.navlinks'],
    ['chief', '.navlinks'],
    ['super-admin', '.navlinks'],
    ['event-admin', '.navbar nav'],
]);
