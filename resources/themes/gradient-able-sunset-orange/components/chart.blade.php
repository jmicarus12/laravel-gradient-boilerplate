{{--
    Chart (ApexCharts). The page passes only data; the theme's js/charts.js draws it.
    type: line|area|bar|donut|pie
    line/area/bar: :series="[['name' => 'Sales', 'data' => [10, 20, 15]]]"  :labels="['Jan', 'Feb', 'Mar']"
    donut/pie:     :series="[60, 30, 10]"                                  :labels="['Active', 'Pending', 'Off']"
--}}
@props(['type' => 'line', 'series' => [], 'labels' => [], 'height' => 300])
<div {{ $attributes->class(['ui-chart']) }}
     data-ui-chart="{{ json_encode(['type' => $type, 'series' => $series, 'labels' => $labels, 'height' => (int) $height]) }}"
     style="min-height: {{ (int) $height }}px"></div>
