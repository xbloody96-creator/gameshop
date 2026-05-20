# Настройка платежной системы NicePay для JustKey.ru

## 1. Регистрация в NicePay

1. Перейдите на сайт [NicePay](https://nicepay.ru) и зарегистрируйтесь как мерчант
2. Пройдите верификацию и получите доступ к личному кабинету
3. В личном кабинете получите:
   - **Merchant ID** (ID мерчанта)
   - **Secret Key** (Секретный ключ API)

## 2. Настройка проекта

### Вариант A: Через переменные окружения (рекомендуется)

1. Создайте файл `.env` в корне проекта:
```bash
cp .env.example .env
```

2. Отредактируйте `.env` и укажите ваши данные:
```
NICEPAY_MERCHANT_ID=ваш_merchant_id
NICEPAY_SECRET_KEY=ваш_secret_key
```

3. В PHP добавьте загрузку переменных окружения в `config.php`:
```php
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
```

### Вариант B: Прямое указание в коде

Откройте `checkout.php` и замените:
```php
define('NICEPAY_MERCHANT_ID', 'your_merchant_id_here');
define('NICEPAY_SECRET_KEY', 'your_secret_key_here');
```

На ваши реальные данные:
```php
define('NICEPAY_MERCHANT_ID', 'ВАШ_MERCHANT_ID');
define('NICEPAY_SECRET_KEY', 'ВАШ_SECRET_KEY');
```

## 3. Настройка callback URLs в NicePay

В личном кабинете NicePay настройте следующие URL:

- **Return URL**: `https://justkey.ru/payment_return.php`
- **Callback URL**: `https://justkey.ru/nicepay_callback.php`

## 4. Проверка работы

### Тестовый режим
Для тестирования используйте тестовый режим NicePay:
```php
$nicePay = new NicePay(null, null, true); // true = тестовый режим
```

Тестовый API: `https://test-api.nicepay.ru/v1`

### Боевой режим
Для продакшена:
```php
$nicePay = new NicePay(null, null, false); // false = боевой режим
```

Боевой API: `https://api.nicepay.ru/v1`

## 5. Обработчики платежей

### Успешная оплата (`nicepay_callback.php`)
- Статусы: `SUCCESS`, `COMPLETED`, `PAID`
- Обновляет статус заказа на `paid`
- Отправляет email покупателю с ключами активации

### Неудачная оплата
- Статусы: `FAILED`, `CANCELLED`, `DECLINED`
- Обновляет статус заказа на `cancelled`
- Позволяет пользователю попробовать снова

### В обработке
- Статусы: `PENDING`, `PROCESSING`
- Обновляет статус заказа на `processing`

## 6. Логирование

Все события платежной системы логируются в:
```
logs/nicepay_callback.log
```

Для просмотра логов:
```bash
tail -f logs/nicepay_callback.log
```

## 7. Структура файлов NicePay

```
/workspace/
├── includes/nicepay.class.php    # Класс для работы с API NicePay
├── nicepay_callback.php          # Обработчик callback от NicePay
├── payment_return.php            # Страница возврата после оплаты
├── checkout.php                  # Оформление заказа с интеграцией NicePay
└── .env.example                  # Пример конфигурации
```

## 8. Безопасность

1. **Никогда не коммитьте** файл `.env` с реальными ключами в Git
2. Используйте HTTPS для всех страниц с платежами
3. Проверяйте подпись (signature) во всех callback запросах
4. Регулярно меняйте секретный ключ

## 9. Поддержка

При возникновении проблем:
1. Проверьте логи в `logs/nicepay_callback.log`
2. Убедитесь, что Merchant ID и Secret Key указаны верно
3. Проверьте, что callback URLs доступны из интернета
4. Обратитесь в поддержку NicePay

