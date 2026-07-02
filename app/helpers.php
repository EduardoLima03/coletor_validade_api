<?php
<<<<<<< HEAD
// Helper functions
=======

if (!function_exists('sortUrl')) {
    function sortUrl($column, $currentSort, $currentDir)
    {
        $params = request()->query();
        $params['sort'] = $column;
        $params['direction'] = ($column === $currentSort && $currentDir === 'asc') ? 'desc' : 'asc';
        return url()->current() . '?' . http_build_query($params);
    }
}

if (!function_exists('sortIcon')) {
    function sortIcon($column, $currentSort, $currentDir)
    {
        if ($column !== $currentSort) {
            return '<i class="bi bi-arrow-down-up text-white-50" style="font-size:0.7rem"></i>';
        }
        $icon = $currentDir === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
        return '<i class="' . $icon . '"></i>';
    }
}
>>>>>>> develop
