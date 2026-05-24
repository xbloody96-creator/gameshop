// ===========================
// GamesKey Store - Main JavaScript
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
        const response = await fetch('ajax/reviews.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                rating: rating.value,
                comment: comment.value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Отзыв отправлен на модерацию', 'success');
            comment.value = '';
            rating.checked = false;
        } else {
            showNotification(data.message || 'Ошибка отправки отзыва', 'error');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка отправки отзыва', 'error');
    }
}

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
// Уведомления
// ===========================
function showNotification(message, type = 'info') {
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
}

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

// Экспорт функций для глобального использования
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.toggleFavorite = toggleFavorite;
window.submitReview = submitReview;
window.rateNews = rateNews;
window.applyFilters = applyFilters;
window.sortProducts = sortProducts;
window.validateForm = validateForm;
window.showNotification = showNotification;
