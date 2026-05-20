# JustKey.ru — Магазин цифровых ключей

Современный интернет-магазин цифровых ключей для игр с интеграцией платежной системы NicePay.

## 🚀 Особенности

- **Полноценная база данных** — все товары, пользователи, заказы хранятся в БД
- **Панель администратора** — управление товарами, заказами, пользователями, новостями
- **Личный кабинет** — история заказов, избранное, профиль пользователя
- **Корзина и оформление заказа** — удобный процесс покупки
- **Интеграция с NicePay** — безопасная оплата банковскими картами
- **Адаптивный дизайн** — работает на ПК и мобильных устройствах
- **Темная/светлая тема** — переключение между темами
- **Режим для слабовидящих** — увеличенный размер шрифтов

## 📁 Структура проекта

```
/workspace/
├── config.php                 # Конфигурация и подключение к БД
├── index.php                  # Главная страница
├── products.php               # Каталог товаров
├── product.php                # Страница товара
├── cart.php                   # Корзина
├── checkout.php               # Оформление заказа
├── login.php                  # Авторизация
├── register.php               # Регистрация
├── profile.php                # Личный кабинет
├── nicepay_callback.php       # Обработчик платежей NicePay
├── payment_return.php         # Страница результата оплаты
├── includes/
│   ├── header.php             # Шапка сайта
│   ├── footer.php             # Подвал сайта
│   └── nicepay.class.php      # Класс для работы с NicePay API
├── admin/                     # Панель администратора
├── ajax/                      # AJAX обработчики
├── static/                    # Статические файлы
├── templates/                 # Шаблоны
└── logs/                      # Логи
```

## 💳 Настройка NicePay

1. Зарегистрируйтесь в [NicePay](https://nicepay.ru) и получите:
   - Merchant ID
   - Secret Key

2. Создайте файл `.env` в корне проекта:
```bash
cp .env.example .env
```

3. Укажите ваши данные в `.env`:
```
NICEPAY_MERCHANT_ID=ваш_merchant_id
NICEPAY_SECRET_KEY=ваш_secret_key
```

4. Настройте callback URLs в личном кабинете NicePay:
   - Return URL: `https://justkey.ru/payment_return.php`
   - Callback URL: `https://justkey.ru/nicepay_callback.php`

📖 Подробная инструкция: [NICEPAY_SETUP.md](NICEPAY_SETUP.md)

## 🗄️ База данных

Импорт SQL дампа:
```bash
mysql -u root -p gameskey_store < database.sql
```

## 🔐 Безопасность

- Защита от XSS атак
- Prepared statements для защиты от SQL инъекций
- Проверка подписи платежей NicePay
- Валидация пользовательских данных
- HTTPS рекомендуется для продакшена

## 📋 Требования

- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx с mod_rewrite
- cURL для работы с API NicePay

## 🌐 Домен

Сайт настроен на домен **justkey.ru**

## 📞 Контакты

- Email: support@justkey.ru
- Телефон: +7 (999) 123-45-67

---

© 2024 JustKey.ru. Все права защищены.
