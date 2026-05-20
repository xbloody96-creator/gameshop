<footer id="footer" class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h4><?= SITE_NAME ?></h4>
                <p>Лучший магазин цифровых ключей для игр. Быстрая доставка, гарантия качества, поддержка 24/7.</p>
                <div class="social-links">
                    <a href="https://t.me/justkey_support" class="social-link" title="Telegram">✈️</a>
                    <a href="https://discord.gg/justkey" class="social-link" title="Discord">💬</a>
                    <a href="https://vk.com/justkey" class="social-link" title="VK">📱</a>
                    <a href="https://youtube.com/@justkey" class="social-link" title="YouTube">▶️</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Контакты</h4>
                <ul class="footer-links">
                    <li><a href="tel:<?= str_replace([' ', '(', ')', '-', '+'], '', SUPPORT_PHONE) ?>" class="footer-link">📞 <?= SUPPORT_PHONE ?></a></li>
                    <li><a href="mailto:<?= SUPPORT_EMAIL ?>" class="footer-link">✉️ <?= SUPPORT_EMAIL ?></a></li>
                    <li><span class="footer-link">📍 Россия, Москва</span></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Информация</h4>
                <ul class="footer-links">
                    <li><a href="about.php" class="footer-link">О нас</a></li>
                    <li><a href="delivery.php" class="footer-link">Доставка и оплата</a></li>
                    <li><a href="guarantees.php" class="footer-link">Гарантии</a></li>
                    <li><a href="contacts.php" class="footer-link">Контакты</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Мы на карте</h4>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2244.5!2d37.6173!3d55.7558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54a50b315e573:0xa886bf5a3d9b2e68!2sRed+Square!5e0!3m2!1sen!2sru!4v1600000000000" 
                            width="100%" height="200" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Все права защищены.</p>
        </div>
    </div>
</footer>
