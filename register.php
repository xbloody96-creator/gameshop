<?php
require_once 'config.php';

$error = '';
$success = '';
$errors = [];

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $birthDate = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Валидация
    if (empty($email)) {
        $errors[] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    
    if (empty($login)) {
        $errors[] = 'Введите логин';
    } elseif (strlen($login) < 3) {
        $errors[] = 'Логин должен быть не менее 3 символов';
    }
    
    if (empty($fullName)) {
        $errors[] = 'Введите ФИО';
    }
    
    if (empty($nickname)) {
        $errors[] = 'Введите никнейм';
    }
    
    if (empty($birthDate)) {
        $errors[] = 'Укажите дату рождения';
    } else {
        $birthDateTime = new DateTime($birthDate);
        $today = new DateTime();
        $age = $today->diff($birthDateTime)->y;
        
        if ($age > 86) { // 2026 - 1940 = 86
            $errors[] = 'Дата рождения должна быть не старше 1940 года';
        } elseif ($age < 13) {
            $errors[] = 'Вам должно быть не менее 13 лет';
        }
    }
    
    if (empty($gender) || !in_array($gender, ['male', 'female', 'other'])) {
        $errors[] = 'Выберите пол';
    }
    
    if (empty($password)) {
        $errors[] = 'Введите пароль';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Пароль должен быть не менее ' . PASSWORD_MIN_LENGTH . ' символов';
    }
    
    if ($password !== $passwordConfirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Проверка на уникальность
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email уже зарегистрирован';
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors[] = 'Логин уже занят';
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = ?");
        $stmt->execute([$nickname]);
        if ($stmt->fetch()) {
            $errors[] = 'Никнейм уже занят';
        }
    } catch (PDOException $e) {
        $errors[] = 'Ошибка базы данных';
    }
    
    // Обработка аватарки
    $avatar = 'default-avatar.png';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            $errors[] = 'Недопустимый формат файла. Разрешены: jpg, jpeg, png, gif, webp';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Файл слишком большой. Максимум 5MB';
        } else {
            $uploadDir = UPLOAD_DIR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = uniqid() . '.' . $ext;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $avatar = $newFilename;
            }
        }
    }
    
    // Если нет ошибок, регистрируем
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (email, login, password, full_name, nickname, birth_date, gender, avatar) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $email, $login, $hashedPassword, $fullName, $nickname, $birthDate, $gender, $avatar
            ]);
            
            $success = 'Регистрация успешна! Теперь вы можете войти.';
            $_POST = []; // Очистка формы
        } catch (PDOException $e) {
            $errors[] = 'Ошибка регистрации';
        }
    }
    
    $error = implode('<br>', $errors);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-form-wrapper">
                <div class="auth-form register-form">
                    <h1>Регистрация</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= escape($success) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?= escape($_POST['email'] ?? '') ?>"
                                       placeholder="example@mail.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="login">Логин *</label>
                                <input type="text" id="login" name="login" required 
                                       value="<?= escape($_POST['login'] ?? '') ?>"
                                       placeholder="Придумайте логин" minlength="3">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">ФИО *</label>
                                <input type="text" id="full_name" name="full_name" required 
                                       value="<?= escape($_POST['full_name'] ?? '') ?>"
                                       placeholder="Иванов Иван Иванович">
                            </div>
                            
                            <div class="form-group">
                                <label for="nickname">Никнейм *</label>
                                <input type="text" id="nickname" name="nickname" required 
                                       value="<?= escape($_POST['nickname'] ?? '') ?>"
                                       placeholder="Придумайте никнейм">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_date">Дата рождения *</label>
                                <input type="date" id="birth_date" name="birth_date" required 
                                       value="<?= escape($_POST['birth_date'] ?? '') ?>"
                                       max="<?= date('Y-m-d', strtotime('-13 years')) ?>"
                                       min="1940-01-01">
                                <small>Не старше 1940 года</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Пол *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Выберите пол</option>
                                    <option value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Мужской</option>
                                    <option value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Женский</option>
                                    <option value="other" <?= (($_POST['gender'] ?? '') === 'other') ? 'selected' : '' ?>>Другой</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="avatar">Аватарка</label>
                            <div class="avatar-upload">
                                <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                                <div class="avatar-preview" id="avatar-preview">
                                    <span>📷 Выберите фото</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Пароль *</label>
                                <input type="password" id="password" name="password" required 
                                       minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                       placeholder="Минимум <?= PASSWORD_MIN_LENGTH ?> символов">
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirm">Повтор пароля *</label>
                                <input type="password" id="password_confirm" name="password_confirm" required 
                                       placeholder="Повторите пароль">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
                        </div>
                        
                        <div class="auth-links">
                            <span>Уже есть аккаунт? <a href="login.php">Войти</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
    <script>
        function previewAvatar(input) {
            const preview = document.getElementById('avatar-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
