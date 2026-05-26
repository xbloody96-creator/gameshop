<footer id="footer" class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- О компании -->
            <div class="footer-section">
                <h4><?= SITE_NAME ?></h4>
                <p class="footer-description">Лучший магазин цифровых ключей для игр. Быстрая доставка, гарантия качества, поддержка 24/7.</p>
                <div class="social-links">
                    <a href="https://t.me/justkey_support" class="social-link" title="Telegram">✈️</a>
                    <a href="https://discord.gg/justkey" class="social-link" title="Discord">💬</a>
                    <a href="https://vk.com/justkey" class="social-link" title="VK">📱</a>
                    <a href="https://youtube.com/@justkey" class="social-link" title="YouTube">▶️</a>
                </div>
            </div>
            
            <!-- Контакты -->
            <div class="footer-section">
                <h4>📞 Контакты</h4>
                <ul class="footer-links">
                    <li><a href="tel:<?= str_replace([' ', '(', ')', '-', '+'], '', SUPPORT_PHONE) ?>" class="footer-link">📞 <?= SUPPORT_PHONE ?></a></li>
                    <li><a href="mailto:<?= SUPPORT_EMAIL ?>" class="footer-link">✉️ <?= SUPPORT_EMAIL ?></a></li>
                    <li><span class="footer-link">📍 Россия, Стерлитамак</span></li>
                </ul>
            </div>
            
            <!-- Информация -->
            <div class="footer-section">
                <h4>ℹ️ Информация</h4>
                <ul class="footer-links">
                    <li><a href="about.php" class="footer-link">О нас</a></li>
                    <li><a href="delivery.php" class="footer-link">Доставка и оплата</a></li>
                    <li><a href="guarantees.php" class="footer-link">Гарантии</a></li>
                    <li><a href="contacts.php" class="footer-link">Контакты</a></li>
                </ul>
            </div>
            
            <!-- Покупателям -->
            <div class="footer-section">
                <h4>🛒 Покупателям</h4>
                <ul class="footer-links">
                    <li><a href="cart.php" class="footer-link">Корзина</a></li>
                    <li><a href="profile.php" class="footer-link">Личный кабинет</a></li>
                    <li><a href="products.php" class="footer-link">Все товары</a></li>
                    <li><a href="reviews.php" class="footer-link">Отзывы</a></li>
                </ul>
            </div>
            
            <!-- Мы на карте -->
            <div class="footer-section">
                <h4>🗺️ Мы на карте</h4>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d91630.5716366728!2d55.9719!3d53.4261!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x43d2b9c0f3e37f6f:0x6b4b0a0e3d1f1e1!2sSterlitamak!5e0!3m2!1sen!2sru!4v1600000000000" 
                            width="100%" height="200" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Все права защищены.</p>
        </div>
    </div>
</footer>
