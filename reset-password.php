<?php
require_once 'config.php';

$error = '';
$success = '';
$token_valid = false;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Неверный токен';
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $token_valid = true;
        } else {
            $error = 'Токен недействителен или истек срок его действия';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка базы данных';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            
            $success = 'Пароль успешно изменен. Теперь вы можете войти.';
            $token_valid = false;
        } catch (PDOException $e) {
            $error = 'Ошибка при смене пароля';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-form-wrapper">
                <div class="auth-form">
                    <h1>Новый пароль</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= escape($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= escape($success) ?></div>
                        <a href="login.php" class="btn btn-primary btn-block">Войти</a>
                    <?php elseif ($token_valid): ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="password">Новый пароль</label>
                                <input type="password" id="password" name="password" required 
                                       placeholder="Введите новый пароль (минимум 6 символов)">
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirm">Подтвердите пароль</label>
                                <input type="password" id="password_confirm" name="password_confirm" required 
                                       placeholder="Повторите пароль">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Изменить пароль</button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="auth-links">
                        <a href="login.php">Вернуться к авторизации</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
