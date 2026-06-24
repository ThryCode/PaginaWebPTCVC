<?php
// Simple minifier for CSS and JS used for local optimization.
// Usage: php public/tools/minify.php

function minify_css($src) {
    $css = file_get_contents($src);
    if ($css === false) return false;
    // Remove comments
    $css = preg_replace('#/\*.*?\*/#s', '', $css);
    // Remove whitespace
    $css = preg_replace('/\s+/', ' ', $css);
    // Remove spaces around tokens
    $css = preg_replace('/\s*([{};:,])\s*/', '$1', $css);
    // Remove final semicolon before }
    $css = preg_replace('/;}/', '}', $css);
    return trim($css);
}

function minify_js($src) {
    $js = file_get_contents($src);
    if ($js === false) return false;
    // Remove block comments
    $js = preg_replace('#/\*.*?\*/#s', '', $js);
    // Remove single-line comments (best-effort)
    $js = preg_replace('/(^|[^:\\"])\s*\/\/.*$/m', '$1', $js);
    // Collapse whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    // Remove space around common punctuators
    $js = preg_replace('/\s*([=\+\-\*\/\{\}\(\)\[\];,:<>])\s*/', '$1', $js);
    return trim($js);
}

$base = __DIR__ . '/../';
$cssSrc = $base . 'css/style.css';
$jsSrc = $base . 'js/main.js';
$cssDst = $base . 'css/style.min.css';
$jsDst = $base . 'js/main.min.js';

$out = [];
if (file_exists($cssSrc)) {
    $m = minify_css($cssSrc);
    if ($m !== false) {
        file_put_contents($cssDst, $m);
        $out[] = "Wrote: css/style.min.css";
    } else {
        $out[] = "Failed to read css/style.css";
    }
} else {
    $out[] = "css/style.css not found";
}

if (file_exists($jsSrc)) {
    $m = minify_js($jsSrc);
    if ($m !== false) {
        file_put_contents($jsDst, $m);
        $out[] = "Wrote: js/main.min.js";
    } else {
        $out[] = "Failed to read js/main.js";
    }
} else {
    $out[] = "js/main.js not found";
}

foreach ($out as $l) echo $l . PHP_EOL;

// Exit code 0
exit(0);
