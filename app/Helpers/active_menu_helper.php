<?php

/**
 * This helper for help automatic generate active menu class for navbar menu
 *
 * $menuName is menu name for navbar menu
 */
function active_menu(string $menuName): ?string
{
    $request = \Config\Services::request();

    $activeMenuClass = 'navbar__link--active';
    $segments = $request->uri->getSegments();

    // $segment is determine menu name in now page
    if (count($segments) > 1) {
        $segment = $segments[1];
    } else {
        $segment = $segments[0];
    }

    // if $menuName = $segment
    if ($menuName == $segment) {
        return $activeMenuClass;
    }
    return null;
}
