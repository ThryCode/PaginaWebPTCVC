<?php

function autoResumen($texto, $max = 200) {
    $limpio = trim(strip_tags($texto));
    if (mb_strlen($limpio) <= $max) return $limpio;
    return mb_substr($limpio, 0, $max) . '…';
}

function getNoticiaFolder($id) { return '../uploads/noticias/noticia_' . $id . '/'; }
function getNoticiaFolderUrl($id) { return 'uploads/noticias/noticia_' . $id . '/'; }
function getEventoFolder($id) { return '../uploads/eventos/evento_' . $id . '/'; }
function getEventoFolderUrl($id) { return 'uploads/eventos/evento_' . $id . '/'; }
function getProyectoFolder($id) { return '../uploads/proyectos/proyecto_' . $id . '/'; }
function getProyectoFolderUrl($id) { return 'uploads/proyectos/proyecto_' . $id . '/'; }

function ensureFolder($dir) { if (!is_dir($dir)) { mkdir($dir, 0755, true); } }

function deleteFolder($dir) {
    if (!is_dir($dir)) return;
    $files = glob($dir . '*');
    if ($files) { foreach ($files as $f) { if (is_file($f)) unlink($f); } }
    rmdir($dir);
}

function migrateOldImages(&$imagenes, $folderDir, $folderUrl) {
    $result = array();
    foreach ($imagenes as $img) {
        if (strpos($img, $folderUrl) === 0) { $result[] = $img; }
        elseif (preg_match('#^uploads/[^/]+$#', $img)) {
            $oldFile = '../' . $img;
            if (file_exists($oldFile)) {
                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (copy($oldFile, $folderDir . $newName)) { unlink($oldFile); $result[] = $folderUrl . $newName; }
                else { $result[] = $img; }
            } else { $result[] = $img; }
        } else { $result[] = $img; }
    }
    $imagenes = $result;
}
