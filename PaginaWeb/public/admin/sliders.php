<?php
header('Location: inicio.php?tab=portada');
exit;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        if ($action === 'delete') {
            $deleteId = intval($_POST['id'] ?? 0);
            if ($deleteId > 0) {
                $existing = Storage::findById('sliders', $deleteId);
                if ($existing && isset($existing['imagen'])) {
                    $imgPath = '../' . $existing['imagen'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
                Storage::delete('sliders', $deleteId);
                $message = 'Slider eliminado.';
            }
            $action = 'list';

        } elseif ($action === 'upload') {
            $sliderDir = '../assets/img/sliders/';
            if (!is_dir($sliderDir)) {
                mkdir($sliderDir, 0755, true);
            }
            $allowedExts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            $uploaded = 0;

            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $ext = strtolower(pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExts) || $_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) {
                    continue;
                }
                $filename = 'slider_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $filepath = $sliderDir . $filename;

                if (move_uploaded_file($tmpName, $filepath)) {
                    $sliders = Storage::read('sliders');
                    $maxOrden = 0;
                    foreach ($sliders as $s) {
                        if (($s['orden'] ?? 0) > $maxOrden) $maxOrden = $s['orden'];
                    }
                    $item = array(
                        'imagen' => 'assets/img/sliders/' . $filename,
                        'titulo' => trim($_POST['titulo'] ?? ''),
                        'orden' => $maxOrden + 1
                    );
                    Storage::insert('sliders', $item);
                    $uploaded++;
                }
            }

            if ($uploaded > 0) {
                $message = "$uploaded imagen(es) subida(s) correctamente.";
            } else {
                $error = 'No se pudieron subir las imágenes.';
            }
            $action = 'list';

        } elseif ($action === 'update') {
            $updateId = intval($_POST['id'] ?? 0);
            if ($updateId > 0) {
                $existing = Storage::findById('sliders', $updateId);
                if ($existing) {
                    $existing['titulo'] = trim($_POST['titulo'] ?? '');
                    Storage::update('sliders', $updateId, $existing);
                    $message = 'Slider actualizado.';
                }
            }
            $action = 'list';

        } elseif ($action === 'reorder') {
            $order = $_POST['order'] ?? array();
            $sliders = Storage::read('sliders');
            foreach ($order as $idx => $idVal) {
                foreach ($sliders as &$s) {
                    if ($s['id'] === intval($idVal)) {
                        $s['orden'] = $idx + 1;
                        break;
                    }
                }
            }
            Storage::write('sliders', $sliders);
            $message = 'Orden actualizado.';
            $action = 'list';
        }
    }
}

$slider = null;
if ($action === 'edit' && $id > 0) {
    $slider = Storage::findById('sliders', $id);
    if (!$slider) {
        $action = 'list';
        $error = 'Slider no encontrado.';
    }
}

$sliders = array();
if ($action === 'list') {
    $sliders = Storage::read('sliders');
    usort($sliders, function($a, $b) {
        return ($a['orden'] ?? 0) - ($b['orden'] ?? 0);
    });
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var w=window.innerWidth||document.documentElement.clientWidth,m=/Mobi|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);if(m&&w<1200){var v=document.createElement('meta');v.name='viewport';v.content='width=1200, initial-scale='+(w/1200)+', maximum-scale=1, user-scalable=no';document.head.insertBefore(v,document.head.querySelector('meta[name="viewport"]'))}document.documentElement.classList.toggle('is-mobile',m)})();</script>
    <title>Admin - Portada</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= filemtime(__DIR__ . '/css/admin.css') ?>">
    <style>
        .slider-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top: 20px; }
        .slider-item { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: grab; }
        .slider-item.dragging { opacity: 0.5; }
        .slider-item img { width: 100%; height: 150px; object-fit: cover; display: block; }
        .slider-item-info { padding: 12px; }
        .slider-item-info h4 { font-size: 0.85rem; margin-bottom: 2px; color: #1a1a2e; }
        .slider-item-info .orden-badge { display: inline-block; background: #e8f0fe; color: #1a73e8; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; margin-top: 4px; }
        .slider-item-actions { display: flex; gap: 6px; padding: 0 12px 12px; }
        .slider-item .drag-handle { display: block; text-align: center; padding: 4px 0; color: #ccc; font-size: 1rem; user-select: none; border-bottom: 1px solid #f0f0f0; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger" aria-label="Menu">☰</button>
                    <h1>Portada - Sliders</h1>
                </div>
                <?php if ($action === 'list'): ?>
                    <a href="?action=upload" class="btn btn-primary">+ Subir</a>
                <?php endif; ?>
            </header>
            <div class="content">
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <p style="color:#888;margin-bottom:4px;">Arrastra las imágenes para reordenar. Los cambios se guardan automáticamente.</p>

                    <?php if (empty($sliders)): ?>
                        <p class="empty">No hay sliders. Sube el primero.</p>
                    <?php else: ?>
                        <div class="slider-grid" id="sliderGrid">
                            <?php foreach ($sliders as $s): ?>
                            <div class="slider-item" data-id="<?php echo $s['id']; ?>">
                                <div class="drag-handle">☰</div>
                                <img src="../<?php echo htmlspecialchars($s['imagen']); ?>" alt="<?php echo htmlspecialchars($s['titulo'] ?? ''); ?>">
                                <div class="slider-item-info">
                                    <h4><?php echo htmlspecialchars($s['titulo'] ?: 'Sin título'); ?></h4>
                                    <span class="orden-badge">Orden: <?php echo intval($s['orden']); ?></span>
                                </div>
                                <div class="slider-item-actions">
                                    <a href="?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <form class="delete-form" method="POST" action="?action=delete" data-confirm="¿Eliminar este slider?">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php elseif ($action === 'upload'): ?>
                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="upload">
                            <div class="form-group">
                                <label for="titulo">Título (opcional, se aplica a todas)</label>
                                <input type="text" id="titulo" name="titulo">
                            </div>
                            <div class="form-group">
                                <label for="imagenes">Imágenes (seleccione varias)</label>
                                <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple required>
                                <small>Se guardan en assets/img/sliders/. Máx 5MB cada una.</small>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Subir</button>
                                <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php elseif ($action === 'edit' && $slider): ?>
                    <div class="form-card">
                        <div style="margin-bottom:20px;">
                            <img src="../<?php echo htmlspecialchars($slider['imagen']); ?>" style="max-width:400px; border-radius:8px;">
                        </div>
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $slider['id']; ?>">
                            <div class="form-group">
                                <label for="titulo">Título</label>
                                <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($slider['titulo'] ?? ''); ?>">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="?action=list" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    (function() {
        var grid = document.getElementById('sliderGrid');
        if (!grid) return;

        var dragItem = null;
        var csrfToken = '<?php echo $csrfToken; ?>';

        function onDragStart(e) {
            var target = e.target.closest('.slider-item');
            if (!target) return;
            dragItem = target;
            target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function onDragOver(e) {
            e.preventDefault();
            var target = e.target.closest('.slider-item');
            if (!target || target === dragItem) return;

            var rect = target.getBoundingClientRect();
            var midY = rect.top + rect.height / 2;
            if (e.clientY < midY) {
                grid.insertBefore(dragItem, target);
            } else {
                grid.insertBefore(dragItem, target.nextSibling);
            }
        }

        function onDragEnd() {
            if (!dragItem) return;
            dragItem.classList.remove('dragging');

            var items = grid.querySelectorAll('.slider-item');
            var order = [];
            items.forEach(function(item) {
                order.push(item.dataset.id);
            });

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '?action=reorder';
            var input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = '<?php echo CSRF_TOKEN_NAME; ?>';
            input1.value = csrfToken;
            form.appendChild(input1);
            order.forEach(function(id, idx) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'order[]';
                inp.value = id;
                form.appendChild(inp);
            });
            document.body.appendChild(form);
            form.submit();

            dragItem = null;
        }

        grid.addEventListener('dragstart', onDragStart);
        grid.addEventListener('dragover', onDragOver);
        grid.addEventListener('dragend', onDragEnd);

        document.querySelectorAll('.slider-item').forEach(function(el) {
            el.draggable = true;
        });
    })();
    </script>
</body>
</html>
