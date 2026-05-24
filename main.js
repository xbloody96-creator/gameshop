// ===========================
// JustKey Store - Main JavaScript
// ===========================

// Глобальные переменные
let currentSlide = 0;
let slideInterval;
const slides = [];

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    initAccessibility();
    initSlider();
    initSearch();
    initScrollAnimations();
    initLazyLoading();
    initCart();
    initFavorites();
    initMobileMenu();
    initTooltips();
    initRoulette();
});

// ===========================
// Управление темой
// ===========================
function initTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        updateThemeIcon(currentTheme);
        
        themeToggle.addEventListener('click', function() {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Анимация переключения
            document.body.style.transition = 'background-color 0.3s ease';
        });
    }
}

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.innerHTML = theme === 'light' ? '🌙' : '☀️';
    }
}

// ===========================
// Режим для слабовидящих
// ===========================
function initAccessibility() {
    const accessibilityToggle = document.getElementById('accessibility-toggle');
    const currentMode = localStorage.getItem('accessibility') || 'normal';
    
    if (currentMode === 'high-contrast') {
        document.documentElement.setAttribute('data-accessibility', 'high-contrast');
    }
    
    if (accessibilityToggle) {
        accessibilityToggle.addEventListener('click', function() {
            const mode = document.documentElement.getAttribute('data-accessibility');
            const newMode = mode === 'high-contrast' ? 'normal' : 'high-contrast';
            
            if (newMode === 'high-contrast') {
                document.documentElement.setAttribute('data-accessibility', 'high-contrast');
            } else {
                document.documentElement.removeAttribute('data-accessibility');
            }
            
            localStorage.setItem('accessibility', newMode);
            showNotification(newMode === 'high-contrast' ? 'Режим для слабовидящих включен' : 'Обычный режим');
        });
    }
}

// ===========================
// Слайдер
// ===========================
function initSlider() {
    const sliderElement = document.querySelector('.slider');
    if (!sliderElement) return;
    
    const slideElements = document.querySelectorAll('.slide');
    const prevBtn = document.getElementById('slider-prev');
    const nextBtn = document.getElementById('slider-next');
    const indicators = document.querySelectorAll('.indicator');
    
    slideElements.forEach((slide, index) => {
        slides.push(slide);
        if (index === 0) slide.classList.add('active');
    });
    
    if (slides.length > 0) {
        startSlideShow();
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            changeSlide(-1);
            resetSlideShow();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            changeSlide(1);
            resetSlideShow();
        });
    }
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            goToSlide(index);
            resetSlideShow();
        });
    });
    
    // Пауза при наведении
    sliderElement.addEventListener('mouseenter', stopSlideShow);
    sliderElement.addEventListener('mouseleave', startSlideShow);
}

function changeSlide(direction) {
    slides[currentSlide].classList.remove('active');
    
    currentSlide += direction;
    
    if (currentSlide >= slides.length) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    }
    
    slides[currentSlide].classList.add('active');
    updateIndicators();
}

function goToSlide(index) {
    slides[currentSlide].classList.remove('active');
    currentSlide = index;
    slides[currentSlide].classList.add('active');
    updateIndicators();
}

function updateIndicators() {
    const indicators = document.querySelectorAll('.indicator');
    indicators.forEach((indicator, index) => {
        if (index === currentSlide) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

function startSlideShow() {
    stopSlideShow();
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

function stopSlideShow() {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
}

function resetSlideShow() {
    stopSlideShow();
    startSlideShow();
}

// ===========================
// Поиск
// ===========================
function initSearch() {
    const searchInput = document.getElementById('search-input');
    const searchSuggestions = document.getElementById('search-suggestions');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                hideSuggestions();
            }
        });
        
        // Закрытие при клике вне поля
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                hideSuggestions();
            }
        });
    }
}

async function performSearch(query) {
    try {
        const response = await fetch(`ajax/search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        displaySuggestions(data.results);
    } catch (error) {
        console.error('Ошибка поиска:', error);
    }
}

function displaySuggestions(results) {
    const suggestionsContainer = document.getElementById('search-suggestions');
    
    if (!suggestionsContainer) return;
    
    if (results.length === 0) {
        hideSuggestions();
        return;
    }
    
    let html = '';
    results.forEach(item => {
        html += `
            <div class="suggestion-item" onclick="window.location.href='product.php?id=${item.id}'">
                <img src="${item.image}" alt="${item.title}" class="suggestion-image">
                <div class="suggestion-info">
                    <div class="suggestion-title">${item.title}</div>
                    <div class="suggestion-price">${item.price} ₴</div>
                </div>
            </div>
        `;
    });
    
    suggestionsContainer.innerHTML = html;
    suggestionsContainer.classList.add('active');
}

function hideSuggestions() {
    const suggestionsContainer = document.getElementById('search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.classList.remove('active');
    }
}

// ===========================
// Корзина
// ===========================
function initCart() {
    updateCartBadge();
}

async function addToCart(productId) {
    console.log('Adding to cart:', productId);
    
    try {
        const response = await fetch('ajax/cart.php?action=add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId
            })
        });
        
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Сервер вернул не JSON ответ');
        }
        
        console.log('Add to cart response:', data);
        
        if (data.success) {
            updateCartBadge();
            showNotification('Товар добавлен в корзину ✓', 'success');
        } else {
            console.error('Cart error:', data.message);
            showNotification(data.message || 'Ошибка добавления в корзину', 'error');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification('Ошибка соединения с сервером: ' + error.message, 'error');
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch('ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge();
            location.reload(); // Перезагрузка страницы корзины
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
}

async function updateCartBadge() {
    try {
        const response = await fetch('ajax/cart.php?action=count');
        const data = await response.json();
        
        const badge = document.querySelector('#cart-badge');
        if (badge && data.count > 0) {
            badge.textContent = data.count;
            badge.style.display = 'flex';
        } else if (badge) {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Ошибка обновления корзины:', error);
    }
}

// ===========================
// Избранное
// ===========================
function initFavorites() {
    // Инициализация избранного
}

async function toggleFavorite(productId, type = 'product') {
    try {
        const response = await fetch('ajax/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                type: type,
                id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Ищем кнопку по нескольким возможным селекторам
            const btn = document.querySelector(`[data-favorite="${productId}"]`) || 
                        document.querySelector(`button[onclick*="toggleFavorite(${productId})"]`) ||
                        event?.target?.closest('button');
            if (btn) {
                // Обновляем текст кнопки или иконку
                if (data.is_favorite) {
                    btn.innerHTML = '❤️ В избранном';
                    btn.classList.add('active');
                } else {
                    btn.innerHTML = '🤍 В избранное';
                    btn.classList.remove('active');
                }
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Ошибка', 'error');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка соединения с сервером', 'error');
    }
}

// ===========================
// Отзывы и рейтинги
// ===========================
async function submitReview(productId) {
    const rating = document.querySelector('input[name="rating"]:checked');
    const comment = document.getElementById('review-comment');
    
    if (!rating || !comment.value.trim()) {
        showNotification('Пожалуйста, поставьте оценку и напишите отзыв', 'warning');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('rating', rating.value);
        formData.append('comment', comment.value.trim());
        
        const response = await fetch('ajax/reviews.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Отзыв отправлен на модерацию', 'success');
            comment.value = '';
            // Снять выделение со звезд
            document.querySelectorAll('input[name="rating"]').forEach(input => input.checked = false);
            // Перезагрузить страницу через 1 секунду
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Ошибка отправки отзыва', 'error');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка отправки отзыва', 'error');
    }
}

window.updateReview = function(reviewId, productId) {
    console.log('updateReview вызвана:', { reviewId, productId });
    
    const rating = document.querySelector('input[name="edit-rating"]:checked');
    const comment = document.getElementById('edit-review-comment');
    
    if (!rating || !comment.value.trim()) {
        showNotification('Пожалуйста, поставьте оценку и напишите отзыв', 'warning');
        return;
    }
    
    console.log('Данные для отправки:', {
        review_id: reviewId,
        product_id: productId,
        rating: rating.value,
        comment: comment.value.trim()
    });
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('review_id', reviewId);
    formData.append('product_id', productId);
    formData.append('rating', rating.value);
    formData.append('comment', comment.value.trim());
    
    // Показываем индикатор загрузки
    const submitBtn = document.querySelector('#edit-form-container button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Сохранение...';
    }
    
    fetch('ajax/reviews.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка сети: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Ответ сервера:', data);
        
        // Возвращаем кнопку в исходное состояние
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
        
        if (data.success) {
            showNotification('Отзыв обновлен и отправлен на повторную модерацию', 'success');
            // Скрываем форму редактирования
            hideEditForm();
            // Перезагружаем страницу через 1.5 секунды
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Ошибка обновления отзыва', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        
        // Возвращаем кнопку в исходное состояние
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
        
        showNotification('Ошибка обновления отзыва: ' + error.message, 'error');
    });
};

// Функции для отображения/скрытия формы редактирования - делаем глобальными
window.showEditForm = function() {
    console.log('showEditForm вызвана');
    const editForm = document.getElementById('edit-form-container');
    const reviewDisplay = document.querySelector('.user-review-display');
    
    if (editForm && reviewDisplay) {
        reviewDisplay.style.display = 'none';
        editForm.style.display = 'block';
    } else {
        console.error('Элементы не найдены:', { editForm, reviewDisplay });
    }
};

window.hideEditForm = function() {
    console.log('hideEditForm вызвана');
    const editForm = document.getElementById('edit-form-container');
    const reviewDisplay = document.querySelector('.user-review-display');
    
    if (editForm && reviewDisplay) {
        editForm.style.display = 'none';
        reviewDisplay.style.display = 'block';
    } else {
        console.error('Элементы не найдены:', { editForm, reviewDisplay });
    }
};

async function rateNews(newsId, rating) {
    try {
        const response = await fetch('ajax/news.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'rate',
                news_id: newsId,
                rating: rating
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Спасибо за вашу оценку!', 'success');
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
}

// ===========================
// Анимации при прокрутке
// ===========================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.product-card, .feature-item, .section-header').forEach(el => {
        observer.observe(el);
    });
}

// ===========================
// Ленивая загрузка изображений
// ===========================
function initLazyLoading() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// ===========================
// Мобильное меню
// ===========================
function initMobileMenu() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    }
}

// ===========================
// Подсказки
// ===========================
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;
            tooltip.style.left = `${rect.left + (rect.width - tooltip.offsetWidth) / 2}px`;
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// ===========================
// Уведомления - делаем глобальной
// ===========================
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    }[type] || 'ℹ';
    
    notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-message">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Принудительно вызываем reflow для анимации
    void notification.offsetWidth;
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
};

// ===========================
// Фильтры и сортировка
// ===========================
function applyFilters() {
    const form = document.getElementById('filters-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = `products.php?${params.toString()}`;
}

function sortProducts(sortBy) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// ===========================
// Валидация форм
// ===========================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'Это поле обязательно для заполнения');
            isValid = false;
        } else {
            clearError(input);
        }
        
        // Email валидация
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                showError(input, 'Введите корректный email');
                isValid = false;
            }
        }
        
        // Валидация пароля
        if (input.type === 'password' && input.value.length < 6) {
            showError(input, 'Пароль должен содержать минимум 6 символов');
            isValid = false;
        }
    });
    
    // Проверка совпадения паролей
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Пароли не совпадают');
        isValid = false;
    }
    
    return isValid;
}

function showError(input, message) {
    clearError(input);
    input.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    input.parentNode.appendChild(errorDiv);
}

function clearError(input) {
    input.classList.remove('error');
    const errorMessage = input.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

// ===========================
// Утилиты
// ===========================
function formatPrice(price) {
    return new Intl.NumberFormat('uk-UA', {
        style: 'currency',
        currency: 'UAH',
        minimumFractionDigits: 0
    }).format(price);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===========================
// Рулетка игр
// ===========================
const rouletteGames = [
    { id: 1, title: "Cyberpunk 2077", genre: "AAA", image: "cyberpunk.jpg" },
    { id: 2, title: "The Witcher 3", genre: "AAA", image: "witcher3.jpg" },
    { id: 3, title: "Red Dead Redemption 2", genre: "AAA", image: "rdr2.jpg" },
    { id: 4, title: "Elden Ring", genre: "AAA", image: "eldenring.jpg" },
    { id: 5, title: "God of War", genre: "AAA", image: "gow.jpg" },
    { id: 6, title: "Horizon Zero Dawn", genre: "AAA", image: "horizon.jpg" },
    { id: 7, title: "Ghost of Tsushima", genre: "AAA", image: "ghost.jpg" },
    { id: 8, title: "Spider-Man Remastered", genre: "AAA", image: "spiderman.jpg" },
    { id: 9, title: "Assassin's Creed Valhalla", genre: "AAA", image: "acv.jpg" },
    { id: 10, title: "Call of Duty: MW2", genre: "AAA", image: "cod.jpg" },
    { id: 11, title: "FIFA 24", genre: "AAA", image: "fifa.jpg" },
    { id: 12, title: "Forza Horizon 5", genre: "AAA", image: "forza.jpg" },
    { id: 13, title: "Halo Infinite", genre: "AAA", image: "halo.jpg" },
    { id: 14, title: "Gears 5", genre: "AAA", image: "gears.jpg" },
    { id: 15, title: "Sea of Thieves", genre: "AAA", image: "sot.jpg" },
    { id: 16, title: "Minecraft", genre: "Indie", image: "minecraft.jpg" },
    { id: 17, title: "Terraria", genre: "Indie", image: "terraria.jpg" },
    { id: 18, title: "Stardew Valley", genre: "Indie", image: "stardew.jpg" },
    { id: 19, title: "Hollow Knight", genre: "Indie", image: "hollow.jpg" },
    { id: 20, title: "Celeste", genre: "Indie", image: "celeste.jpg" },
    { id: 21, title: "Hades", genre: "Indie", image: "hades.jpg" },
    { id: 22, title: "Dead Cells", genre: "Indie", image: "deadcells.jpg" },
    { id: 23, title: "Cuphead", genre: "Indie", image: "cuphead.jpg" },
    { id: 24, title: "Ori and the Blind Forest", genre: "Indie", image: "ori.jpg" },
    { id: 25, title: "Undertale", genre: "Indie", image: "undertale.jpg" },
    { id: 26, title: "Among Us", genre: "Indie", image: "amongus.jpg" },
    { id: 27, title: "Fall Guys", genre: "Indie", image: "fallguys.jpg" },
    { id: 28, title: "Valheim", genre: "Indie", image: "valheim.jpg" },
    { id: 29, title: "Subnautica", genre: "Indie", image: "subnautica.jpg" },
    { id: 30, title: "The Forest", genre: "Indie", image: "forest.jpg" },
    { id: 31, title: "Grand Theft Auto V", genre: "AAA", image: "gtav.jpg" },
    { id: 32, title: "Dark Souls III", genre: "AAA", image: "ds3.jpg" },
    { id: 33, title: "Bloodborne", genre: "AAA", image: "bloodborne.jpg" },
    { id: 34, title: "Sekiro", genre: "AAA", image: "sekiro.jpg" },
    { id: 35, title: "Resident Evil 4", genre: "AAA", image: "re4.jpg" },
    { id: 36, title: "Devil May Cry 5", genre: "AAA", image: "dmc5.jpg" },
    { id: 37, title: "Monster Hunter World", genre: "AAA", image: "mhw.jpg" },
    { id: 38, title: "Final Fantasy VII", genre: "AAA", image: "ff7.jpg" },
    { id: 39, title: "Street Fighter 6", genre: "AAA", image: "sf6.jpg" },
    { id: 40, title: "Tekken 8", genre: "AAA", image: "tekken8.jpg" }
];

let isSpinning = false;
let rouletteInitialized = false;
let currentRoulettePosition = 0;

function initRoulette() {
    const spinBtn = document.getElementById('spinRouletteBtn');

    if (!spinBtn) return;

    spinBtn.addEventListener('click', function() {
        if (isSpinning) return;
        
        // Показываем контейнер рулетки с плавной анимацией
        const container = document.getElementById('rouletteContainer');
        if (container) {
            container.style.display = 'block';
            
            // Небольшая задержка для применения display перед opacity
            setTimeout(() => {
                container.style.opacity = '1';
                
                // Инициализируем дорожку с карточками если еще не инициализирована
                if (!rouletteInitialized) {
                    setupRouletteTrack();
                    rouletteInitialized = true;
                }
                
                // Запускаем прокрутку после появления
                setTimeout(() => {
                    spinRoulette();
                }, 600);
            }, 50);
        }
    });
}

function setupRouletteTrack() {
    const track = document.getElementById('rouletteTrack');
    if (!track) return;

    // Заполняем рулетку играми (дублируем для бесконечного эффекта)
    let html = '';
    for (let i = 0; i < 5; i++) {
        rouletteGames.forEach(game => {
            html += `
                <div class="roulette-card" data-id="${game.id}">
                    <div class="roulette-card-image">🎮</div>
                    <div class="roulette-card-title">${game.title}</div>
                    <div class="roulette-card-genre">${game.genre}</div>
                </div>
            `;
        });
    }
    track.innerHTML = html;

    // Устанавливаем начальную позицию - первый набор игр
    currentRoulettePosition = 0;
    track.style.transition = 'none';
    track.style.transform = `translateX(0px)`;
}

function spinRoulette() {
    if (isSpinning) return;
    isSpinning = true;

    const track = document.getElementById('rouletteTrack');
    const resultDiv = document.getElementById('rouletteResult');
    const spinBtn = document.getElementById('spinRouletteBtn');

    if (!track || !resultDiv) return;

    resultDiv.innerHTML = '';
    spinBtn.disabled = true;
    spinBtn.textContent = '🎲 Прокрутка...';

    // Случайный выбор игры
    const winningIndex = Math.floor(Math.random() * rouletteGames.length);
    const winningGame = rouletteGames[winningIndex];

    // Параметры карточек
    const cardWidth = 195; // 180px + 15px gap
    const cardsPerSet = rouletteGames.length;
    
    // Выбираем набор для остановки (третий набор - индекс 2, чтобы было место для разгона)
    const targetSet = 2;
    
    // Вычисляем позицию так, чтобы выигрышная карточка оказалась ровно по центру контейнера
    // Индикатор находится по центру видимой области рулетки
    // Добавляем небольшое случайное смещение внутри карточки для реалистичности (-40 to 40px)
    const randomOffset = Math.floor(Math.random() * 80) - 40;
    
    // Позиция начала выигрышной карточки в треке
    const winningCardPosition = (targetSet * cardsPerSet + winningIndex) * cardWidth;
    
    // Получаем ширину видимой области контейнера
    const containerWidth = track.parentElement.offsetWidth;
    
    // Вычисляем позицию трека: смещаем так, чтобы центр карточки совпал с центром контейнера
    // translateX должен быть отрицательным, чтобы сдвинуть трек влево
    const targetPosition = -(winningCardPosition - (containerWidth / 2) + (cardWidth / 2) + randomOffset);

    // Сбрасываем transition и позицию перед анимацией (возвращаемся к началу для плавности)
    track.style.transition = 'none';
    track.style.transform = `translateX(0px)`;

    // Принудительно вызываем reflow для применения стилей
    void track.offsetWidth;

    // Небольшая задержка перед анимацией для плавности
    setTimeout(() => {
        // Анимация прокрутки
        track.style.transition = 'transform 4s cubic-bezier(0.15, 0, 0.2, 1)';
        track.style.transform = `translateX(${targetPosition}px)`;

        // Эффект "щёлканья" во время прокрутки
        let clickCount = 0;
        const maxClicks = 50;
        const clickInterval = setInterval(() => {
            clickCount++;
            if (clickCount % 5 === 0 && clickCount <= maxClicks) {
                track.style.filter = `brightness(${1 + Math.sin(clickCount * 0.5) * 0.1})`;
            }
            if (clickCount > maxClicks) {
                clearInterval(clickInterval);
            }
        }, 80);

        setTimeout(() => {
            clearInterval(clickInterval);
            track.style.filter = 'brightness(1)';

            // Сохраняем текущую позицию для следующего спина (сбрасываем на первый набор)
            // Вычисляем эквивалентную позицию в первом наборе
            const normalizedPosition = -(winningIndex * cardWidth) + (containerWidth / 2) - (cardWidth / 2) + randomOffset;
            currentRoulettePosition = normalizedPosition;
            
            // Сбрасываем позицию без анимации для следующего спина
            setTimeout(() => {
                track.style.transition = 'none';
                track.style.transform = `translateX(${normalizedPosition}px)`;
            }, 50);

            // Показываем результат с правильным названием игры
            const resultTitle = winningGame.title || "Неизвестная игра";
            const resultGenre = winningGame.genre || "Игра";

            resultDiv.innerHTML = `
                <div class="result-card">
                    <div class="result-emoji">🎉</div>
                    <h3 style="color: #2d3748; margin-bottom: 10px;">Вам выпала:</h3>
                    <div class="result-game" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.5rem; font-weight: bold;">${escapeHtml(resultTitle)}</div>
                    <div class="result-genre">${escapeHtml(resultGenre)} проект</div>
                    <a href="products.php?search=${encodeURIComponent(resultTitle)}" class="btn btn-primary" style="margin-top: 15px;">Купить сейчас</a>
                </div>
            `;

            spinBtn.disabled = false;
            spinBtn.textContent = '🎲 Испытать удачу ещё раз!';
            isSpinning = false;

        }, 4100);
    }, 50);
}

// Функция для экранирования HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Экспорт функций для глобального использования
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.toggleFavorite = toggleFavorite;
window.submitReview = submitReview;
window.updateReview = updateReview;
window.showEditForm = showEditForm;
window.hideEditForm = hideEditForm;
window.rateNews = rateNews;
window.applyFilters = applyFilters;
window.sortProducts = sortProducts;
window.validateForm = validateForm;
window.showNotification = showNotification;
window.spinRoulette = spinRoulette;
