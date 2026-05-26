<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Введите email';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Генерация токена сброса пароля
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Сохранение токена в базе
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                // Отправка email
                $resetLink = SITE_URL . '/reset-password.php?token=' . $token;
                $subject = 'Сброс пароля - ' . SITE_NAME;
                $message = "
                    <h2>Сброс пароля</h2>
                    <p>Вы запросили сброс пароля для аккаунта {$user['email']}.</p>
                    <p>Для сброса пароля перейдите по ссылке:</p>
                    <p><a href='{$resetLink}'>{$resetLink}</a></p>
                    <p>Ссылка действительна в течение 1 часа.</p>
                    <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
                ";
                
                sendEmail($user['email'], $subject, $message);
                $success = 'Инструкция по сбросу пароля отправлена на ваш email';
            } else {
                // Не показываем что email не найден из соображений безопасности
                $success = 'Если этот email зарегистрирован, инструкция по сбросу пароля будет отправлена';
            }
        } catch (PDOException $e) {
            error_log('Forgot password error: ' . $e->getMessage());
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
    <title>Забыли пароль - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-form-wrapper">
                <div class="auth-form">
                    <h1>Забыли пароль?</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= escape($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= escape($success) ?></div>
                    <?php else: ?>
                        <p>Введите ваш email и мы отправим инструкцию по восстановлению пароля</p>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?= escape($_POST['email'] ?? '') ?>"
                                   placeholder="Введите ваш email">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Отправить инструкцию</button>
                        </div>
                        
                        <div class="auth-links">
                            <a href="login.php">Вернуться к авторизации</a>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="auth-links">
                            <a href="login.php">Вернуться к авторизации</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
