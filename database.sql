-- База данных GamesKey Store
CREATE DATABASE IF NOT EXISTS gameskey_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gameskey_store;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    login VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    nickname VARCHAR(100) UNIQUE NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица сессий
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица категорий игр
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица платформ
CREATE TABLE platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    icon VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица игр/товаров
-- Таблица услуг
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration INT DEFAULT 60,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица продуктов (обновленная структура для админки)
CREATE TABLE IF NOT EXISTS products_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    platform VARCHAR(100),
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Старая таблица products (для совместимости)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    category_id INT,
    platform_id INT,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) NULL,
    discount_percent INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    gallery JSON,
    stock INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    is_popular BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    release_date DATE,
    developer VARCHAR(255),
    publisher VARCHAR(255),
    system_requirements JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE SET NULL,
    INDEX idx_popular (is_popular),
    INDEX idx_featured (is_featured),
    INDEX idx_price (price),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Исправленная таблица products для админки
ALTER TABLE products ADD COLUMN IF NOT EXISTS name VARCHAR(255) AFTER title;
ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT AFTER name;
ALTER TABLE products ADD COLUMN IF NOT EXISTS platform VARCHAR(100) AFTER category_id;
ALTER TABLE products ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0 AFTER platform;
ALTER TABLE products ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) AFTER stock;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER image_url;

UPDATE products SET name = title WHERE name IS NULL;
UPDATE products SET image_url = image WHERE image_url IS NULL;

-- Акции (обновленная структура)
CREATE TABLE IF NOT EXISTS promotions_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_percent INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляем недостающие поля в promotions
ALTER TABLE promotions ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) AFTER banner_image;

-- Связь акций с товарами
CREATE TABLE promotion_products (
    promotion_id INT,
    product_id INT,
    PRIMARY KEY (promotion_id, product_id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица новостей (обновленная структура для админки)
CREATE TABLE IF NOT EXISTS news_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 5.00,
    is_active BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Основная таблица новостей (пересоздаем если существует для обновления структуры)
DROP TABLE IF EXISTS news;
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    short_content VARCHAR(500),
    image VARCHAR(255),
    author_id INT,
    rating DECIMAL(3,2) DEFAULT 5.00,
    views INT DEFAULT 0,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Отзывы с модерацией (исправленная структура)
CREATE TABLE IF NOT EXISTS reviews_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляем поля для отзывов в существующую таблицу news
ALTER TABLE news ADD COLUMN IF NOT EXISTS is_approved BOOLEAN DEFAULT TRUE AFTER is_active;

-- Таблица отзывов о товарах
-- Таблица отзывов о товарах (дубликат для совместимости)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица корзины
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица избранного
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT,
    news_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица заказов
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    delivery_method VARCHAR(50),
    delivery_address TEXT,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица товаров в заказе
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 1,
    game_key VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица истории просмотров
CREATE TABLE view_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица рейтингов новостей
CREATE TABLE news_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_news_rating (news_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка тестовых данных

-- Админ пользователь (пароль: admin123)
INSERT INTO users (email, login, password, full_name, nickname, birth_date, gender, role) 
VALUES ('admin@gameskey.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор Системы', 'AdminMaster', '1990-01-01', 'male', 'admin');

-- Категории
INSERT INTO categories (name, slug, description) VALUES
('Экшен', 'action', 'Динамичные игры с активным геймплеем'),
('RPG', 'rpg', 'Ролевые игры с глубоким сюжетом'),
('Стратегии', 'strategy', 'Игры требующие тактического мышления'),
('Спорт', 'sport', 'Спортивные симуляторы'),
('Приключения', 'adventure', 'Приключенческие игры'),
('Хоррор', 'horror', 'Игры ужасов');

-- Платформы
INSERT INTO platforms (name, slug) VALUES
('Steam', 'steam'),
('Epic Games', 'epic'),
('Origin', 'origin'),
('Ubisoft Connect', 'ubisoft'),
('Battle.net', 'battlenet'),
('GOG', 'gog');

-- Примеры игр
INSERT INTO products (title, slug, description, short_description, category_id, platform_id, price, old_price, discount_percent, image, stock, is_popular, is_featured, rating, release_date, developer, publisher) VALUES
('Cyberpunk 2077', 'cyberpunk-2077', 'Откройте для себя мир будущего в захватывающей RPG от создателей Ведьмака 3. Киберпанк 2077 - это приключение в открытом мире, действие которого происходит в Найт-Сити, мегаполисе будущего, одержимом властью, гламуром и модификациями тела.', 'Приключенческая RPG в мире будущего', 2, 1, 1499.00, 2999.00, 50, 'cyberpunk2077.jpg', 150, TRUE, TRUE, 4.50, '2020-12-10', 'CD Projekt Red', 'CD Projekt'),
('The Witcher 3: Wild Hunt', 'witcher-3', 'Станьте профессиональным охотником на чудовищ и отправьтесь в эпическое приключение. В открытом мире, полном торговых городов, пиратских островов, опасных горных перевалов и забытых пещер для исследования.', 'Эпическая RPG о ведьмаке Геральте', 2, 1, 899.00, 1499.00, 40, 'witcher3.jpg', 200, TRUE, TRUE, 4.90, '2015-05-19', 'CD Projekt Red', 'CD Projekt'),
('Red Dead Redemption 2', 'rdr2', 'Америка, 1899 год. Эпоха дикого запада подходит к концу. Приключенческий экшен в огромном открытом мире от Rockstar Games.', 'Вестерн от создателей GTA', 1, 1, 2499.00, NULL, 0, 'rdr2.jpg', 100, TRUE, FALSE, 4.80, '2019-11-05', 'Rockstar Games', 'Rockstar Games'),
('Counter-Strike 2', 'cs2', 'Легендарный командный шутер от первого лица в новом воплощении. Соревновательная игра на новом движке Source 2.', 'Легендарный тактический шутер', 1, 1, 0.00, NULL, 0, 'cs2.jpg', 999, TRUE, TRUE, 4.70, '2023-09-27', 'Valve', 'Valve'),
('Elden Ring', 'elden-ring', 'Новая фэнтезийная RPG от FromSoftware. Путешествуйте по обширному миру, полному опасностей, тайн и красоты.', 'Фэнтезийная RPG от создателей Dark Souls', 2, 1, 2199.00, 2999.00, 27, 'eldenring.jpg', 80, TRUE, TRUE, 4.85, '2022-02-25', 'FromSoftware', 'Bandai Namco'),
('FIFA 24', 'fifa-24', 'Новейшая версия легендарного футбольного симулятора. Улучшенная графика, новые режимы игры и реалистичная физика мяча.', 'Футбольный симулятор 2024 года', 4, 2, 3499.00, NULL, 0, 'fifa24.jpg', 120, FALSE, FALSE, 4.20, '2023-09-29', 'EA Sports', 'Electronic Arts');

-- Новости
INSERT INTO news (title, slug, content, short_content, image, author_id, rating) VALUES
('Анонсирована новая часть GTA', 'gta-6-announced', 'Rockstar Games официально анонсировала GTA 6. Игра выйдет в 2025 году и перенесет игроков в вице-сити нового поколения...', 'Rockstar Games показала первый трейлер GTA 6', 'gta6news.jpg', 1, 4.95),
('Скидки до 90% в летней распродаже', 'summer-sale-2026', 'Началась грандиозная летняя распродажа! Тысячи игр со скидками до 90%. Успейте купить игры мечты по лучшей цене!', 'Летняя распродажа стартовала', 'summersale.jpg', 1, 4.50);

-- Акции
INSERT INTO promotions (title, description, discount_percent, start_date, end_date, is_active, image_url) VALUES
('Летняя распродажа 2026', 'Огромные скидки на лучшие игры года!', 50, '2026-06-01 00:00:00', '2026-06-30 23:59:59', TRUE, 'summer-sale.jpg'),
('Черная пятница', 'Не пропустите лучшие предложения года', 70, '2026-11-25 00:00:00', '2026-11-30 23:59:59', FALSE, 'black-friday.jpg');

-- Услуги
INSERT INTO services (name, description, price, duration, image_url, is_active) VALUES
('Настройка игрового ПК', 'Профессиональная настройка операционной системы, драйверов и оптимизация для игр', 1500.00, 120, 'pc-setup.jpg', TRUE),
('Установка игр', 'Установка и настройка любых игр из ваших библиотек Steam, Epic Games и других платформ', 500.00, 60, 'game-install.jpg', TRUE),
('Консультация геймера', 'Индивидуальная консультация по выбору игр, прохождению сложных моментов', 800.00, 45, 'consultation.jpg', TRUE),
('Восстановление аккаунта', 'Помощь в восстановлении доступа к игровым аккаунтам', 1000.00, 90, 'account-recovery.jpg', TRUE);

-- Тестовые данные для админки (products_new)
INSERT INTO products_new (name, description, price, category_id, platform, stock, image_url, is_active) VALUES
('Cyberpunk 2077', 'RPG в мире будущего', 1499.00, 2, 'Steam', 150, 'cyberpunk2077.jpg', TRUE),
('The Witcher 3', 'Эпическая RPG о ведьмаке', 899.00, 2, 'Steam', 200, 'witcher3.jpg', TRUE),
('Elden Ring', 'Фэнтезийная RPG от FromSoftware', 2199.00, 2, 'Steam', 80, 'eldenring.jpg', TRUE);

-- Тестовые данные для news_new
INSERT INTO news_new (title, content, image_url, rating, is_active) VALUES
('Анонсирована новая часть GTA', 'Rockstar Games официально анонсировала GTA 6...', 'gta6news.jpg', 4.95, TRUE),
('Скидки до 90% в летней распродаже', 'Началась грандиозная летняя распродажа!', 'summersale.jpg', 4.50, TRUE);

-- Тестовые отзывы
INSERT INTO reviews (product_id, user_id, rating, comment, is_approved) VALUES
(1, 1, 5, 'Отличная игра! Рекомендую всем.', TRUE),
(2, 1, 4, 'Хорошая графика и сюжет.', FALSE);
