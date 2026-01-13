<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('getActiveSidebarTitle')) {
  function getActiveSidebarTitle()
  {
    $menu = config('sidebar');

    foreach ($menu as $section) {

      if (!isset($section['items'])) continue;

      foreach ($section['items'] as $item) {

        if (request()->is($item['pattern'])) {
          return $section['title']
            ? $section['title'] . ' / ' . $item['label']
            : $item['label'];
        }
      }
    }
    return 'Dashboard';
  }
}
