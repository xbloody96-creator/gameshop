-- База данных JustKey Store
CREATE DATABASE IF NOT EXISTS justkey_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE justkey_store;

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

-- Исправленная таблица products для админки - добавляем поля напрямую в CREATE TABLE
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    category_id INT,
    platform_id INT,
    platform VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) NULL,
    discount_percent INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    gallery JSON,
    stock INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
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
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE,
    INDEX idx_popular (is_popular),
    INDEX idx_featured (is_featured),
    INDEX idx_price (price),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Акции (обновленная структура)
DROP TABLE IF EXISTS promotions;
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_percent INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    banner_image VARCHAR(255),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Связь акций с товарами
CREATE TABLE promotion_products (
    promotion_id INT,
    product_id INT,
    PRIMARY KEY (promotion_id, product_id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица новостей (обновленная структура для админки)
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
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Отзывы с модерацией
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Удаляем дублирующиеся таблицы и запросы
DROP TABLE IF EXISTS reviews_new;

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

-- Таблица товаров в заказе (перемещена после orders)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 1,
    game_key VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
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
INSERT IGNORE INTO users (email, login, password, full_name, nickname, birth_date, gender, role) 
VALUES ('admin@gameskey.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор Системы', 'AdminMaster', '1990-01-01', 'male', 'admin');

-- Категории
INSERT IGNORE INTO categories (name, slug, description) VALUES
('Экшен', 'action', 'Динамичные игры с активным геймплеем'),
('RPG', 'rpg', 'Ролевые игры с глубоким сюжетом'),
('Стратегии', 'strategy', 'Игры требующие тактического мышления'),
('Спорт', 'sport', 'Спортивные симуляторы'),
('Приключения', 'adventure', 'Приключенческие игры'),
('Хоррор', 'horror', 'Игры ужасов');

-- Платформы
INSERT IGNORE INTO platforms (name, slug) VALUES
('Steam', 'steam'),
('Epic Games', 'epic'),
('Origin', 'origin'),
('Ubisoft Connect', 'ubisoft'),
('Battle.net', 'battlenet'),
('GOG', 'gog');

-- Примеры игр
INSERT IGNORE INTO products (title, slug, description, short_description, category_id, platform_id, price, old_price, discount_percent, image, stock, is_popular, is_featured, rating, release_date, developer, publisher) VALUES
('Cyberpunk 2077', 'cyberpunk-2077', 'Откройте для себя мир будущего в захватывающей RPG от создателей Ведьмака 3. Киберпанк 2077 - это приключение в открытом мире, действие которого происходит в Найт-Сити, мегаполисе будущего, одержимом властью, гламуром и модификациями тела.', 'Приключенческая RPG в мире будущего', 2, 1, 1499.00, 2999.00, 50, 'cyberpunk2077.jpg', 150, TRUE, TRUE, 4.50, '2020-12-10', 'CD Projekt Red', 'CD Projekt'),
('The Witcher 3: Wild Hunt', 'witcher-3', 'Станьте профессиональным охотником на чудовищ и отправьтесь в эпическое приключение. В открытом мире, полном торговых городов, пиратских островов, опасных горных перевалов и забытых пещер для исследования.', 'Эпическая RPG о ведьмаке Геральте', 2, 1, 899.00, 1499.00, 40, 'witcher3.jpg', 200, TRUE, TRUE, 4.90, '2015-05-19', 'CD Projekt Red', 'CD Projekt'),
('Red Dead Redemption 2', 'rdr2', 'Америка, 1899 год. Эпоха дикого запада подходит к концу. Приключенческий экшен в огромном открытом мире от Rockstar Games.', 'Вестерн от создателей GTA', 1, 1, 2499.00, NULL, 0, 'rdr2.jpg', 100, TRUE, FALSE, 4.80, '2019-11-05', 'Rockstar Games', 'Rockstar Games'),
('Counter-Strike 2', 'cs2', 'Легендарный командный шутер от первого лица в новом воплощении. Соревновательная игра на новом движке Source 2.', 'Легендарный тактический шутер', 1, 1, 0.00, NULL, 0, 'cs2.jpg', 999, TRUE, TRUE, 4.70, '2023-09-27', 'Valve', 'Valve'),
('Elden Ring', 'elden-ring', 'Новая фэнтезийная RPG от FromSoftware. Путешествуйте по обширному миру, полному опасностей, тайн и красоты.', 'Фэнтезийная RPG от создателей Dark Souls', 2, 1, 2199.00, 2999.00, 27, 'eldenring.jpg', 80, TRUE, TRUE, 4.85, '2022-02-25', 'FromSoftware', 'Bandai Namco'),
('FIFA 24', 'fifa-24', 'Новейшая версия легендарного футбольного симулятора. Улучшенная графика, новые режимы игры и реалистичная физика мяча.', 'Футбольный симулятор 2024 года', 4, 2, 3499.00, NULL, 0, 'fifa24.jpg', 120, FALSE, FALSE, 4.20, '2023-09-29', 'EA Sports', 'Electronic Arts'),
('God of War', 'god-of-war', 'Кратос возвращается в эпическом приключении по скандинавским мифам. Сражайтесь с богами и монстрами вместе с сыном Атреем.', 'Эпическое приключение в мире скандинавских богов', 1, 1, 1999.00, 2499.00, 20, 'gow.jpg', 90, TRUE, TRUE, 4.90, '2018-04-20', 'Santa Monica Studio', 'Sony Interactive Entertainment'),
('Horizon Zero Dawn', 'horizon-zero-dawn', 'Погрузитесь в постапокалиптический мир, где правят механические существа. Охотьтесь на роботов-динозавров и раскройте тайну прошлого.', 'Приключение в мире машин', 1, 1, 1799.00, 2199.00, 18, 'horizon.jpg', 85, TRUE, FALSE, 4.75, '2017-02-28', 'Guerrilla Games', 'Sony Interactive Entertainment'),
('Ghost of Tsushima', 'ghost-of-tsushima', 'Станьте самураем и освободите остров Цусима от монгольского вторжения. Красивый открытый мир в феодальной Японии.', 'Самурайский экшен в открытой Японии', 1, 1, 2299.00, NULL, 0, 'ghost.jpg', 75, TRUE, TRUE, 4.80, '2020-07-17', 'Sucker Punch Productions', 'Sony Interactive Entertainment'),
('Spider-Man Remastered', 'spider-man-remastered', 'Присоединяйтесь к Человеку-пауку в его борьбе за спасение Нью-Йорка. Качайте паутину и сражайтесь с преступниками.', 'Паутина приключений в Нью-Йорке', 1, 1, 1899.00, 2299.00, 17, 'spiderman.jpg', 95, TRUE, FALSE, 4.70, '2018-09-07', 'Insomniac Games', 'Sony Interactive Entertainment'),
("Assassin's Creed Valhalla", 'assassins-creed-valhalla', 'Викинги возвращаются! Станьте Эйвором и возглавьте набег на берега Англии в эпоху викингов.', 'Эпическое приключение викингов', 1, 4, 2199.00, 2999.00, 27, 'acv.jpg', 100, TRUE, FALSE, 4.50, '2020-11-10', 'Ubisoft Montreal', 'Ubisoft'),
('Call of Duty: Modern Warfare II', 'call-of-duty-mw2', 'Лучший военный шутер возвращается. Участвуйте в глобальных операциях и почувствуйте интенсивность современного боя.', 'Военный шутер нового поколения', 1, 1, 3499.00, NULL, 0, 'cod.jpg', 110, TRUE, TRUE, 4.40, '2022-10-28', 'Infinity Ward', 'Activision'),
('Forza Horizon 5', 'forza-horizon-5', 'Гонки в открытом мире в живописной Мексике. Сотни автомобилей, динамические сезоны и полная свобода.', 'Гонки в открытой Мексике', 4, 1, 2499.00, 2999.00, 17, 'forza.jpg', 88, TRUE, FALSE, 4.80, '2021-11-09', 'Playground Games', 'Xbox Game Studios'),
('Halo Infinite', 'halo-infinite', 'Легендарный Мастер Чиф возвращается в новой главе саги Halo. Исследуйте Зетанское кольцо и спасите человечество.', 'Мастер Чиф в новой миссии', 1, 1, 1999.00, 2499.00, 20, 'halo.jpg', 70, FALSE, FALSE, 4.30, '2021-12-08', '343 Industries', 'Xbox Game Studios'),
('Gears 5', 'gears-5', 'Эпический шутер от третьего лица в мрачном мире Серы. Сражайтесь с роем и защищайте остатки человечества.', 'Брутальный экшен в мире Серы', 1, 1, 1499.00, 1999.00, 25, 'gears.jpg', 65, FALSE, FALSE, 4.40, '2019-09-10', 'The Coalition', 'Xbox Game Studios'),
('Sea of Thieves', 'sea-of-thieves', 'Пиратское приключение в открытом море. Поднимите паруса, найдите сокровища и станьте легендой семи морей.', 'Пиратское приключение', 5, 1, 1299.00, NULL, 0, 'sot.jpg', 80, TRUE, FALSE, 4.50, '2018-03-20', 'Rare', 'Xbox Game Studios'),
('Minecraft', 'minecraft', 'Стройте, исследуйте и выживайте в бесконечном процедурно генерируемом мире. Самая популярная песочница всех времен.', 'Бесконечное творчество и выживание', 5, 1, 999.00, NULL, 0, 'minecraft.jpg', 500, TRUE, TRUE, 4.95, '2011-11-18', 'Mojang Studios', 'Microsoft'),
('Terraria', 'terraria', '2D песочница с элементами выживания. Копайте, стройте, сражайтесь с боссами и исследуйте подземный мир.', '2D приключения и выживание', 5, 1, 399.00, NULL, 0, 'terraria.jpg', 200, TRUE, FALSE, 4.85, '2011-05-16', 'Re-Logic', 'Re-Logic'),
('Stardew Valley', 'stardew-valley', 'Унаследуйте ферму деда и превратите её в процветающее хозяйство. Выращивайте урожай, разводите животных и заводите друзей.', 'Уютный симулятор фермы', 5, 1, 449.00, NULL, 0, 'stardew.jpg', 180, TRUE, TRUE, 4.95, '2016-02-26', 'ConcernedApe', 'ConcernedApe'),
('Hollow Knight', 'hollow-knight', 'Мрачное метроидвания-приключение в заброшенном королевстве насекомых. Сложные бои и атмосферный мир.', 'Атмосферное метроидвания', 1, 1, 599.00, 799.00, 25, 'hollow.jpg', 120, TRUE, FALSE, 4.90, '2017-02-24', 'Team Cherry', 'Team Cherry'),
('Celeste', 'celeste', 'Хардкорный платформер о восхождении на гору Селеста. Точные прыжки и трогательная история о преодолении себя.', 'Сложный платформер с душой', 5, 1, 399.00, NULL, 0, 'celeste.jpg', 100, TRUE, FALSE, 4.85, '2018-01-25', 'Matt Makes Games', 'Matt Makes Games'),
('Hades', 'hades', 'Рогалик-экшен о побеге из царства мертвых. Сражайтесь с богами Олимпа и раскройте тайны семьи.', 'Божественный рогалик', 1, 1, 799.00, 999.00, 20, 'hades.jpg', 110, TRUE, TRUE, 4.95, '2020-09-17', 'Supergiant Games', 'Supergiant Games'),
('Dead Cells', 'dead-cells', 'Динамичный рогалик-метроидвания. Сражайтесь, умирайте, возрождайтесь и становитесь сильнее.', 'Быстрый и жестокий рогалик', 1, 1, 699.00, NULL, 0, 'deadcells.jpg', 95, TRUE, FALSE, 4.75, '2018-08-07', 'Motion Twin', 'Motion Twin'),
('Cuphead', 'cuphead', 'Хардкорный раннер с боссами в стиле мультфильмов 1930-х годов. Уникальная рисованная графика и сложные бои.', 'Мультяшный хардкор', 1, 1, 599.00, NULL, 0, 'cuphead.jpg', 85, TRUE, FALSE, 4.70, '2017-09-29', 'Studio MDHR', 'Studio MDHR'),
('Ori and the Blind Forest', 'ori-blind-forest', 'Волшебное метроидвания-приключение о духе леса Ори. Потрясающая визуальная составляющая и эмоциональная история.', 'Волшебное приключение духа леса', 5, 1, 599.00, 799.00, 25, 'ori.jpg', 90, TRUE, FALSE, 4.85, '2015-03-11', 'Moon Studios', 'Xbox Game Studios'),
('Undertale', 'undertale', 'Культовая RPG, где вам не нужно убивать никого. Дружелюбный монстр в подземном мире.', 'RPG где можно дружить с монстрами', 2, 1, 399.00, NULL, 0, 'undertale.jpg', 150, TRUE, FALSE, 4.90, '2015-09-15', 'Toby Fox', 'Toby Fox'),
('Among Us', 'among-us', 'Социальная дедукция в космосе. Найдите предателя среди экипажа или сами станьте им.', 'Космическая социальная дедукция', 5, 1, 199.00, NULL, 0, 'amongus.jpg', 300, TRUE, FALSE, 4.40, '2018-06-15', 'InnerSloth', 'InnerSloth'),
('Fall Guys', 'fall-guys', 'Весёлая многопользовательская битва с препятствиями. Проходите безумные уровни и станьте последним выжившим.', 'Безумные соревнования с друзьями', 4, 1, 0.00, NULL, 0, 'fallguys.jpg', 500, TRUE, FALSE, 4.20, '2020-08-04', 'Mediatonic', 'Epic Games'),
('Valheim', 'valheim', 'Выживание в мире скандинавской мифологии. Стройте поселения, плавайте на драккарах и побеждайте древних богов.', 'Викингское выживание', 5, 1, 599.00, NULL, 0, 'valheim.jpg', 140, TRUE, FALSE, 4.70, '2021-02-02', 'Iron Gate AB', 'Coffee Stain Publishing'),
('Subnautica', 'subnautica', 'Подводное выживание на инопланетном океаническом мире. Исследуйте глубины и раскройте тайны планеты 4546B.', 'Подводное приключение', 5, 1, 899.00, 1199.00, 25, 'subnautica.jpg', 100, TRUE, FALSE, 4.80, '2018-01-23', 'Unknown Worlds', 'Unknown Worlds'),
('The Forest', 'the-forest', 'Выживание на острове, населённом каннибалами. Стройте убежище, исследуйте пещеры и защищайтесь от туземцев.', 'Хоррор выживание на острове', 6, 1, 399.00, NULL, 0, 'forest.jpg', 110, TRUE, FALSE, 4.60, '2018-04-30', 'Endnight Games', 'Endnight Games'),
('Grand Theft Auto V', 'gta-v', 'Три уникальных героя в огромном открытом мире Лос-Сантоса. Ограбления, погони и полная свобода действий.', 'Легендарный открытый мир', 1, 1, 1499.00, 1999.00, 25, 'gtav.jpg', 200, TRUE, TRUE, 4.85, '2013-09-17', 'Rockstar North', 'Rockstar Games'),
('Dark Souls III', 'dark-souls-3', 'Мрачная фэнтезийная RPG от создателей серии Souls. Сложные бои, глубокий лор и незабываемая атмосфера.', 'Хардкорная фэнтези RPG', 2, 1, 1999.00, 2499.00, 20, 'ds3.jpg', 85, TRUE, FALSE, 4.80, '2016-04-12', 'FromSoftware', 'Bandai Namco'),
('Bloodborne', 'bloodborne', 'Готический хоррор-экшен в городе Ярнам. Охотьтесь на кошмарных существ и раскройте тайны Древних Богов.', 'Готический охотничий хоррор', 6, 1, 1799.00, NULL, 0, 'bloodborne.jpg', 70, TRUE, FALSE, 4.85, '2015-03-24', 'FromSoftware', 'Sony Interactive Entertainment'),
('Sekiro: Shadows Die Twice', 'sekiro-shadows-die-twice', 'Самурайский экшен от FromSoftware. Станьте шиноби и отомстите за своего господина в феодальной Японии.', 'Самурайский слэшер от мастера', 1, 1, 1999.00, 2499.00, 20, 'sekiro.jpg', 75, TRUE, FALSE, 4.75, '2019-03-22', 'FromSoftware', 'Activision'),
('Resident Evil 4', 'resident-evil-4', 'Ремейк классического хоррора. Леон Кеннеди отправляется в Испанию спасти дочь президента от культистов.', 'Легендарный хоррор в новом обличии', 6, 1, 2499.00, 2999.00, 17, 're4.jpg', 95, TRUE, TRUE, 4.90, '2023-03-24', 'Capcom', 'Capcom'),
('Devil May Cry 5', 'devil-may-cry-5', 'Стильный экшен о демоне Данте и его союзниках. Безумные комбо, зрелищные бои и рок-саундтрек.', 'Демонический стильный экшен', 1, 1, 1499.00, 1999.00, 25, 'dmc5.jpg', 80, TRUE, FALSE, 4.70, '2019-03-08', 'Capcom', 'Capcom'),
('Monster Hunter World', 'monster-hunter-world', 'Охотьтесь на гигантских монстров в живом экосистемном мире. Создавайте экипировку из трофеев и становитесь легендой.', 'Эпическая охота на монстров', 1, 1, 1799.00, 2299.00, 22, 'mhw.jpg', 100, TRUE, FALSE, 4.65, '2018-01-26', 'Capcom', 'Capcom'),
('Final Fantasy VII Remake Intergrade', 'final-fantasy-vii-remake', 'Переосмысление классической RPG. Присоединяйтесь к Клауду и АВАНГАРДУ в борьбе против корпорации Shinra.', 'Легендарная RPG в новом формате', 2, 1, 2999.00, 3499.00, 14, 'ff7.jpg', 70, TRUE, TRUE, 4.80, '2021-06-10', 'Square Enix', 'Square Enix'),
('Street Fighter 6', 'street-fighter-6', 'Новая глава легендарного файтинга. Улучшенная боевая система, режим World Tour и онлайн-сражения.', 'Король файтингов возвращается', 4, 1, 3499.00, NULL, 0, 'sf6.jpg', 85, TRUE, FALSE, 4.60, '2023-06-02', 'Capcom', 'Capcom'),
('Tekken 8', 'tekken-8', 'Продолжение кульного 3D-файтинга. Новый движок Unreal Engine 5, зрелищные бои и огромный roster бойцов.', 'Эпический 3D-файтинг', 4, 1, 3999.00, NULL, 0, 'tekken8.jpg', 75, TRUE, TRUE, 4.70, '2024-01-26', 'Bandai Namco', 'Bandai Namco');

-- Новости
INSERT IGNORE INTO news (title, slug, content, short_content, image, author_id, rating) VALUES
('Анонсирована новая часть GTA', 'gta-6-announced', 'Rockstar Games официально анонсировала GTA 6. Игра выйдет в 2025 году и перенесет игроков в вице-сити нового поколения...', 'Rockstar Games показала первый трейлер GTA 6', 'gta6news.jpg', 1, 4.95),
('Скидки до 90% в летней распродаже', 'summer-sale-2026', 'Началась грандиозная летняя распродажа! Тысячи игр со скидками до 90%. Успейте купить игры мечты по лучшей цене!', 'Летняя распродажа стартовала', 'summersale.jpg', 1, 4.50);

-- Акции
INSERT IGNORE INTO promotions (title, description, discount_percent, start_date, end_date, is_active, image_url) VALUES
('Летняя распродажа 2026', 'Огромные скидки на лучшие игры года!', 50, '2026-06-01 00:00:00', '2026-06-30 23:59:59', TRUE, 'summer-sale.jpg'),
('Черная пятница', 'Не пропустите лучшие предложения года', 70, '2026-11-25 00:00:00', '2026-11-30 23:59:59', FALSE, 'black-friday.jpg');

-- Услуги
INSERT IGNORE INTO services (name, description, price, duration, image_url, is_active) VALUES
('Настройка игрового ПК', 'Профессиональная настройка операционной системы, драйверов и оптимизация для игр', 1500.00, 120, 'pc-setup.jpg', TRUE),
('Установка игр', 'Установка и настройка любых игр из ваших библиотек Steam, Epic Games и других платформ', 500.00, 60, 'game-install.jpg', TRUE),
('Консультация геймера', 'Индивидуальная консультация по выбору игр, прохождению сложных моментов', 800.00, 45, 'consultation.jpg', TRUE),
('Восстановление аккаунта', 'Помощь в восстановлении доступа к игровым аккаунтам', 1000.00, 90, 'account-recovery.jpg', TRUE);

-- Тестовые данные для админки
INSERT IGNORE INTO products (title, name, slug, description, short_description, category_id, platform_id, platform, price, image, image_url, stock, is_active) VALUES
('Cyberpunk 2077', 'Cyberpunk 2077', 'cyberpunk-2077', 'RPG в мире будущего', 'Приключенческая RPG в мире будущего', 2, 1, 'Steam', 1499.00, 'cyberpunk2077.jpg', 'cyberpunk2077.jpg', 150, TRUE),
('The Witcher 3', 'The Witcher 3', 'witcher-3', 'Эпическая RPG о ведьмаке', 'Эпическая RPG о ведьмаке Геральте', 2, 1, 'Steam', 899.00, 'witcher3.jpg', 'witcher3.jpg', 200, TRUE),
('Elden Ring', 'Elden Ring', 'elden-ring', 'Фэнтезийная RPG от FromSoftware', 'Фэнтезийная RPG от создателей Dark Souls', 2, 1, 'Steam', 2199.00, 'eldenring.jpg', 'eldenring.jpg', 80, TRUE);

-- Тестовые отзывы
INSERT IGNORE INTO reviews (product_id, user_id, rating, comment, is_approved) VALUES
(1, 1, 5, 'Отличная игра! Рекомендую всем.', TRUE),
(2, 1, 4, 'Хорошая графика и сюжет.', FALSE);

-- Добавляем поля для сброса пароля если их нет
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL;
