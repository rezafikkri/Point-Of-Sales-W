<?php

/**
 * This helper for help automatic generate active menu class for navbar menu
 *
 * @param array $menuNames Contains menu name list from navbar menu
 *
 * @return string|null
 */
function active_menu(array $menuNames): ?string
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

    // if $segment is in $menuNames
    if (in_array($segment, $menuNames)) {
        return $activeMenuClass;
    }
    return null;
}
