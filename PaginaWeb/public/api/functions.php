<?php

function _cacheBust($path) {
    static $cache = [];
    if (!isset($cache[$path])) {
        $abs = __DIR__ . '/../' . $path;
        $cache[$path] = file_exists($abs) ? filemtime($abs) : time();
    }
    return $path . '?v=' . $cache[$path];
}
