<?php
/**
 * Страница выхода из аккаунта
 */
require_once 'config.php';

// Уничтожаем все данные сессии
$_SESSION = array();

// Удаляем cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', SITE_DOMAIN, true, true);
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную страницу
header('Location: index.php');
exit;
