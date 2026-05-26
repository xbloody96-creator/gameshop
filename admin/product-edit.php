<?php
require_once '../config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';
$product = null;
$is_edit = false;

// Получение ID товара если это редактирование
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        error_log("Товар с ID $id не найден");
        header('Location: products.php?error=not_found');
        exit;
    }
    error_log("Товар найден: " . print_r($product, true));
    $is_edit = true;
} else {
    error_log("ID товара не указан или равен 0");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $platform = trim($_POST['platform']);
    $stock = intval($_POST['stock']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Обработка загрузки изображения
    $image_url = $_POST['current_image'] ?? '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Недопустимый формат изображения. Разрешены: JPG, PNG, GIF, WebP';
        } elseif ($file_size > $max_size) {
            $error = 'Файл слишком большой. Максимальный размер: 5MB';
        } else {
            // Генерация уникального имени файла
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('product_') . '_' . time() . '.' . $extension;
            $upload_path = '../uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = '/uploads/' . $new_filename;
                
                // Удаляем старое изображение если оно было локальным
                if (!empty($product['image_url']) && strpos($product['image_url'], '/uploads/') === 0) {
                    $old_path = '..' . $product['image_url'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
        }
    }
    
    if (empty($error)) {
        if ($name && $price > 0 && $category_id) {
            if ($is_edit) {
                // Обновляем и image_url, и image для совместимости, а также title
                $stmt = $pdo->prepare("UPDATE products SET name=?, title=?, description=?, price=?, category_id=?, platform=?, stock=?, image_url=?, image=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $name, $description, $price, $category_id, $platform, $stock, $image_url, basename($image_url), $is_active, $id]);
                error_log("Товар обновлен: ID=$id, name=$name, title=$name");
                header('Location: products.php?success=updated');
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, title, description, price, category_id, platform, stock, image_url, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $name, $description, $price, $category_id, $platform, $stock, $image_url, basename($image_url)]);
                header('Location: products.php?success=added');
                exit;
            }
        } else {
            $error = 'Заполните обязательные поля';
        }
    }
}

// Получение категорий
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Редактировать товар' : 'Добавить товар' ?> - Админ-панель</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <header class="admin-header">
        <h1>
            <?= $is_edit ? '<svg class="icon-svg icon-sm" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Редактирование товара' : '<svg class="icon-svg icon-sm" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Добавить новый товар' ?>
        </h1>
        <?php include 'includes/theme-toggle.php'; ?>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-form" style="max-width: 900px;">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $is_edit ? 'edit_product' : 'add_product' ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>
            
            <div class="form-section">
                <h3>Основная информация</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Название товара *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? $product['title'] ?? '') ?>" required placeholder="Например: The Witcher 3: Wild Hunt">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Цена (₽) *</label>
                        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price'] ?? '') ?>" required placeholder="1999.00">
                    </div>
                    <div class="form-group">
                        <label>Остаток на складе</label>
                        <input type="number" name="stock" value="<?= htmlspecialchars($product['stock'] ?? '100') ?>" min="0" placeholder="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Категория *</label>
                        <select name="category_id" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Платформа</label>
                        <select name="platform">
                            <option value="">Любая</option>
                            <option value="PC" <?= (isset($product['platform']) && $product['platform'] === 'PC') ? 'selected' : '' ?>>PC</option>
                            <option value="PlayStation" <?= (isset($product['platform']) && $product['platform'] === 'PlayStation') ? 'selected' : '' ?>>PlayStation</option>
                            <option value="Xbox" <?= (isset($product['platform']) && $product['platform'] === 'Xbox') ? 'selected' : '' ?>>Xbox</option>
                            <option value="Nintendo" <?= (isset($product['platform']) && $product['platform'] === 'Nintendo') ? 'selected' : '' ?>>Nintendo</option>
                            <option value="Mobile" <?= (isset($product['platform']) && $product['platform'] === 'Mobile') ? 'selected' : '' ?>>Mobile</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Изображение товара</h3>
                
                <div class="image-upload-container">
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="current-image">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Текущее изображение">
                            <p>Текущее изображение</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="upload-area">
                        <label for="image-upload" class="upload-label">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Выберите файл или перетащите сюда</span>
                            <span class="upload-hint">JPG, PNG, GIF, WebP (макс. 5MB)</span>
                        </label>
                        <input type="file" id="image-upload" name="image" accept="image/*" onchange="previewImage(this)">
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">
                    </div>
                    
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Предпросмотр">
                        <button type="button" class="btn btn-sm btn-danger" onclick="clearPreview()">✕ Удалить</button>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Описание</h3>
                <div class="form-group">
                    <textarea name="description" rows="6" placeholder="Подробное описание товара..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>
            
            <?php if ($is_edit): ?>
            <div class="form-section">
                <h3>Статус</h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" <?= (isset($product['is_active']) && $product['is_active'] == 1) ? 'checked' : '' ?>>
                        <span>Товар активен (отображается на сайте)</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <a href="products.php" class="btn btn-secondary">← Назад к списку</a>
                <button type="submit" class="btn btn-primary">
                    <?= $is_edit ? '💾 Сохранить изменения' : '✨ Добавить товар' ?>
                </button>
            </div>
        </form>
    </div>
</main>

<style>
.form-section {
    background: var(--bg-surface);
    padding: 25px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.form-section h3 {
    margin: 0 0 20px;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-light);
}

.image-upload-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.current-image {
    text-align: center;
}

.current-image img {
    max-width: 300px;
    max-height: 300px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    object-fit: contain;
}

.current-image p {
    margin-top: 10px;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.upload-area {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 40px;
    text-align: center;
    transition: all var(--transition-fast);
    background: var(--bg-surface-2);
}

.upload-area:hover {
    border-color: var(--primary);
    background: var(--primary-light);
}

.upload-label {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.upload-icon {
    font-size: 3rem;
    margin-bottom: 10px;
}

.upload-text {
    font-weight: 600;
    color: var(--text-primary);
}

.upload-hint {
    font-size: 0.85rem;
    color: var(--text-muted);
}

#image-upload {
    display: none;
}

.image-preview {
    text-align: center;
    padding: 20px;
    background: var(--bg-surface-2);
    border-radius: var(--radius);
}

.image-preview img {
    max-width: 300px;
    max-height: 300px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    object-fit: contain;
    margin-bottom: 15px;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-size: 1rem;
    color: var(--text-primary);
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
</style>

<script>
function previewImage(input) {
    console.log('Файл выбран:', input.files ? input.files.length : 0);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        console.log('Имя файла:', file.name, 'Тип:', file.type, 'Размер:', file.size);
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            console.log('Файл загружен в FileReader');
            const previewImg = document.getElementById('preview-img');
            const imagePreview = document.getElementById('image-preview');
            const uploadArea = document.querySelector('.upload-area');
            const currentImage = document.querySelector('.current-image');
            
            if (previewImg && imagePreview && uploadArea) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                uploadArea.style.display = 'none';
                
                if (currentImage) {
                    currentImage.style.display = 'none';
                }
                
                console.log('Предпросмотр отображен');
            } else {
                console.error('Не найдены элементы для предпросмотра');
            }
        };
        
        reader.onerror = function(error) {
            console.error('Ошибка чтения файла:', error);
            alert('Ошибка при чтении файла');
        };
        
        reader.readAsDataURL(file);
    } else {
        console.warn('Файл не выбран или файлы недоступны');
    }
}

function clearPreview() {
    console.log('Очистка предпросмотра');
    
    const imageUpload = document.getElementById('image-upload');
    const imagePreview = document.getElementById('image-preview');
    const uploadArea = document.querySelector('.upload-area');
    const currentImage = document.querySelector('.current-image');
    
    if (imageUpload) {
        imageUpload.value = '';
    }
    
    if (imagePreview) {
        imagePreview.style.display = 'none';
    }
    
    if (uploadArea) {
        uploadArea.style.display = 'block';
    }
    
    if (currentImage) {
        currentImage.style.display = 'block';
    }
    
    console.log('Предпросмотр очищен');
}

// Дополнительный обработчик для drag-and-drop
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.querySelector('.upload-area');
    const imageUpload = document.getElementById('image-upload');
    
    if (uploadArea && imageUpload) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--primary)';
                uploadArea.style.background = 'var(--primary-light)';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = 'var(--border)';
                uploadArea.style.background = 'var(--bg-surface-2)';
            }, false);
        });
        
        uploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files && files[0]) {
                imageUpload.files = files;
                previewImage(imageUpload);
            }
        }, false);
        
        console.log('Обработчики drag-and-drop инициализированы');
    }
});
</script>

</body>
</html>
