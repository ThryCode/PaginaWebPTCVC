<?php
// Duplicate of root minify to run inside PaginaWeb/public when needed.
// Usage: php public/tools/minify.php

function minify_css($src) {
    $css = file_get_contents($src);
    if ($css === false) return false;
    $css = preg_replace('#/\*.*?\*/#s', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{};:,])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return trim($css);
}

// Safer JS handling: do not remove line comments as that can break regexes/strings.
function minify_js($src) {
    $js = file_get_contents($src);
    if ($js === false) return false;
    // Remove only block comments (/* ... */) which are safe to strip in most cases
    $js = preg_replace('#/\*.*?\*/#s', '', $js);
    // Collapse multiple whitespace characters but keep single newlines to avoid
    // collapsing code that depends on automatic semicolon insertion.
    $js = preg_replace('/[\t\r ]+/', ' ', $js);
    $js = preg_replace('/\n{2,}/', "\n", $js);
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

exit(0);
