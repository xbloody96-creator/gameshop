<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Гарантии - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <h1>Гарантии</h1>
            <div class="info-content">
                <h2>Наши гарантии</h2>
                <p>Мы гарантируем:</p>
                <ul>
                    <li><strong>100% работоспособность ключей:</strong> Все ключи проверяются перед продажей</li>
                    <li><strong>Официальные ключи:</strong> Мы работаем только с официальными поставщиками</li>
                    <li><strong>Возврат средств:</strong> Если ключ не работает, мы вернем деньги в течение 30 дней</li>
                    <li><strong>Безопасность данных:</strong> Ваши персональные данные защищены</li>
                    <li><strong>Поддержка 24/7:</strong> Мы всегда готовы помочь с любым вопросом</li>
                </ul>
                
                <h2>Что делать если ключ не работает?</h2>
                <ol>
                    <li>Проверьте правильность ввода ключа</li>
                    <li>Убедитесь, что ключ подходит для вашего региона</li>
                    <li>Свяжитесь с нашей поддержкой support@justkey.ru</li>
                </ol>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
