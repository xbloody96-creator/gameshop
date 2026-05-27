<?php
// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'gameskey_store'); // Имя твоей базы данных
define('DB_USER', 'gameshop');       // Пользователь БД
define('DB_PASS', 'My_Password123');
define('DB_CHARSET', 'utf8mb4');

// Настройки сайта
define('SITE_NAME', 'JustKey');
define('SITE_URL', 'https://justkey.ru');
define('ADMIN_EMAIL', 'support@justkey.ru');
define('SITE_DOMAIN', 'justkey.ru');
define('SUPPORT_PHONE', '+7 (999) 123-45-67');
define('SUPPORT_EMAIL', 'support@justkey.ru');

// Настройки безопасности
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 дней
define('PASSWORD_MIN_LENGTH', 6);

// Настройка сессии перед запуском
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', 1); // Только HTTPS
ini_set('session.cookie_httponly', 1); // Защита от XSS
ini_set('session.cookie_samesite', 'Lax'); // Защита от CSRF
ini_set('session.use_strict_mode', 1); // Строгий режим сессий

// Настройки загрузки файлов
define('UPLOAD_DIR', 'images/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Подключение к базе данных
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Ошибка подключения к базе данных: " . $e->getMessage());
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Настройка логирования ошибок
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Старт сессии с настройками
if (session_status() === PHP_SESSION_NONE) {
    // Устанавливаем параметры cookie перед стартом сессии
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => SITE_DOMAIN,
        'secure' => true, // Только HTTPS
        'httponly' => true, // Защита от XSS
        'samesite' => 'Lax' // Защита от CSRF
    ]);
    session_start();
    
    // Продлеваем время жизни сессии при каждом запросе авторизованного пользователя
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}

// Функция для защиты от XSS
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Функция проверки авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Функция проверки администратора
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Функция получения текущего пользователя
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Функция генерации случайной строки
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Функция отправки email
function sendEmail($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Функция для форматирования цены
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ₽';
}

// Функция для расчета скидки
function calculateDiscount($price, $old_price) {
    if ($old_price && $old_price > $price) {
        return round((($old_price - $price) / $old_price) * 100);
    }
    return 0;
}

// Функция получения времени назад
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "только что";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " " . plural($minutes, ['минуту', 'минуты', 'минут']) . " назад";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " " . plural($hours, ['час', 'часа', 'часов']) . " назад";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " " . plural($days, ['день', 'дня', 'дней']) . " назад";
    } else {
        return date('d.m.Y', $timestamp);
    }
}

// Функция для склонения слов
function plural($number, $forms) {
    $number = abs($number) % 100;
    $n1 = $number % 10;
    
    if ($number > 10 && $number < 20) {
        return $forms[2];
    }
    if ($n1 > 1 && $n1 < 5) {
        return $forms[1];
    }
    if ($n1 == 1) {
        return $forms[0];
    }
    return $forms[2];
}

// Функция для создания slug
function createSlug($string) {
    $converter = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $string = mb_strtolower($string);
    $string = strtr($string, $converter);
    $string = mb_ereg_replace('[^-0-9a-z]', '-', $string);
    $string = mb_ereg_replace('[-]+', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}
