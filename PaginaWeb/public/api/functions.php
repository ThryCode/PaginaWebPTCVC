<?php

function _cacheBust($path) {
    $abs = __DIR__ . '/../' . $path;
    $v = file_exists($abs) ? filemtime($abs) : time();
    return $path . '?v=' . $v;
}
