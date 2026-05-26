<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <h1>Контакты</h1>
            <div class="info-content">
                <h2>Свяжитесь с нами</h2>
                <p><strong>Адрес:</strong> Россия, Стерлитамак</p>
                <p><strong>Телефон:</strong> <?= SUPPORT_PHONE ?></p>
                <p><strong>Email:</strong> <?= SUPPORT_EMAIL ?></p>
                
                <h2>Режим работы</h2>
                <p>Поддержка работает круглосуточно 24/7</p>
                
                <h2>Мы в социальных сетях</h2>
                <div class="social-links">
                    <a href="https://t.me/justkey_support" class="social-link">Telegram</a>
                    <a href="https://discord.gg/justkey" class="social-link">Discord</a>
                    <a href="https://vk.com/justkey" class="social-link">VK</a>
                    <a href="https://youtube.com/@justkey" class="social-link">YouTube</a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
