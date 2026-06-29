<?php

function autoResumen($texto, $max = 200) {
    $limpio = trim(strip_tags($texto));
    if (mb_strlen($limpio) <= $max) return $limpio;
    return mb_substr($limpio, 0, $max) . '…';
}

function getNoticiaFolder($id) { $id = preg_replace('/[^0-9]/', '', $id); return '../uploads/noticias/noticia_' . $id . '/'; }
function getNoticiaFolderUrl($id) { $id = preg_replace('/[^0-9]/', '', $id); return 'uploads/noticias/noticia_' . $id . '/'; }
function getEventoFolder($id) { $id = preg_replace('/[^0-9]/', '', $id); return '../uploads/eventos/evento_' . $id . '/'; }
function getEventoFolderUrl($id) { $id = preg_replace('/[^0-9]/', '', $id); return 'uploads/eventos/evento_' . $id . '/'; }
function getProyectoFolder($id) { $id = preg_replace('/[^0-9]/', '', $id); return '../uploads/proyectos/proyecto_' . $id . '/'; }
function getProyectoFolderUrl($id) { $id = preg_replace('/[^0-9]/', '', $id); return 'uploads/proyectos/proyecto_' . $id . '/'; }

function ensureFolder($dir) { if (!is_dir($dir)) { mkdir($dir, 0755, true); } }

function deleteFolder($dir) {
    if (!is_dir($dir)) return;
    $files = glob($dir . '*');
    if ($files) { foreach ($files as $f) { if (is_file($f)) unlink($f); } }
    rmdir($dir);
}

function validateUploadedImage($file, $maxSize = 10485760) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Error al subir el archivo.';
    }
    $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMime)) {
        return 'Formato de imagen no v&aacute;lido (JPG/PNG/GIF/WEBP).';
    }
    if ($file['size'] > $maxSize) {
        return 'La imagen excede el tama&ntilde;o m&aacute;ximo de ' . ($maxSize / 1048576) . 'MB.';
    }
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        return 'El archivo no es una imagen v&aacute;lida.';
    }
    return null;
}

function moveUploadedImage($file, $targetDir, $prefix = 'img') {
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $filepath = $targetDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    return null;
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
