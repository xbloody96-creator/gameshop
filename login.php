<?php
require_once '../config.php';

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Введите email и пароль';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR login = ?");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Проверка активности аккаунта
                if (!$user['is_active']) {
                    $error = 'Ваш аккаунт деактивирован';
                } else {
                    // Успешная авторизация
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['username'] = $user['login'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_login'] = $user['login'];
                    
                    // Обновление времени последнего входа
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Запись сессии в базу
                    $stmt = $pdo->prepare("INSERT INTO sessions (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $user['id'], 
                        $_SERVER['REMOTE_ADDR'], 
                        $_SERVER['HTTP_USER_AGENT']
                    ]);
                    
                    // Редирект в личный кабинет или на главную
                    header('Location: profile.php');
                    exit;
                }
            } else {
                $error = 'Неверный email или пароль';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-form-wrapper">
                <div class="auth-form">
                    <h1>Вход в аккаунт</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= escape($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= escape($success) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email или логин</label>
                            <input type="text" id="email" name="email" required 
                                   value="<?= escape($_POST['email'] ?? '') ?>"
                                   placeholder="Введите email или логин">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Введите пароль">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Войти</button>
                        </div>
                        
                        <div class="auth-links">
                            <a href="forgot-password.php">Забыли пароль?</a>
                            <span>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
