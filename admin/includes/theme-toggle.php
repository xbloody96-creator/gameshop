<?php
// Theme Toggle Component with JavaScript
?>

<div class="admin-header-actions">
    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle">
        <button type="button" data-theme="light" title="Светлая тема" onclick="setTheme('light')">
            ☀️
        </button>
        <button type="button" data-theme="dark" title="Темная тема" onclick="setTheme('dark')">
            🌙
        </button>
    </div>
    
    <!-- Notifications Bell -->
    <div class="notifications-panel">
        <div class="notification-bell" id="notificationBell" title="Уведомления" style="cursor: pointer; position: relative;">
            <span class="bell-icon">🔔</span>
            <?php
            // Подсчет уведомлений (заказы, отзывы и т.д.)
            try {
                $pending_reviews = $pdo->query("SELECT COUNT(*) as count FROM reviews WHERE is_approved = 0")->fetch()['count'];
                $pending_orders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'];
                $total_notifications = $pending_reviews + $pending_orders;
                
                if ($total_notifications > 0) {
                    echo '<span class="notification-badge">' . min($total_notifications, 99) . '</span>';
                }
            } catch(Exception $e) {}
            ?>
        </div>
        
        <!-- Dropdown Panel for Notifications -->
        <div class="notifications-dropdown" id="notificationsDropdown" style="display: none;">
            <div class="notifications-header">
                <h4>Уведомления</h4>
                <span class="close-notifications" onclick="toggleNotifications()">✕</span>
            </div>
            <div class="notifications-content">
                <?php
                try {
                    $has_notifications = false;
                    
                    // Pending Orders
                    $pending_orders_stmt = $pdo->query("SELECT o.id, o.total_amount, o.created_at, u.full_name as user_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.status = 'pending' 
                        ORDER BY o.created_at DESC 
                        LIMIT 5");
                    $pending_orders_list = $pending_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($pending_orders_list) > 0) {
                        $has_notifications = true;
                        echo '<div class="notification-section"><strong>📋 Новые заказы:</strong></div>';
                        foreach ($pending_orders_list as $order) {
                            $name = htmlspecialchars($order['user_name'] ?? 'Гость');
                            $amount = number_format($order['total_amount'], 0, '.', ' ');
                            $date = date('d.m.Y H:i', strtotime($order['created_at']));
                            echo "<a href='orders.php' class='notification-item'>
                                    <div class='notif-info'>
                                        <div><strong>Заказ #{$order['id']}</strong> от {$name}</div>
                                        <div class='notif-meta'>{$amount} ₽ • {$date}</div>
                                    </div>
                                  </a>";
                        }
                    }
                    
                    // Pending Reviews
                    $pending_reviews_stmt = $pdo->query("SELECT r.id, r.comment, r.created_at, u.full_name as user_name 
                        FROM reviews r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.is_approved = 0 
                        ORDER BY r.created_at DESC 
                        LIMIT 5");
                    $pending_reviews_list = $pending_reviews_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($pending_reviews_list) > 0) {
                        $has_notifications = true;
                        echo '<div class="notification-section" style="margin-top: 15px;"><strong>💬 Отзывы на модерации:</strong></div>';
                        foreach ($pending_reviews_list as $review) {
                            $name = htmlspecialchars($review['user_name'] ?? 'Аноним');
                            $date = date('d.m.Y H:i', strtotime($review['created_at']));
                            $comment = htmlspecialchars(mb_substr($review['comment'], 0, 50)) . '...';
                            echo "<a href='reviews.php' class='notification-item'>
                                    <div class='notif-info'>
                                        <div><strong>{$name}</strong>: {$comment}</div>
                                        <div class='notif-meta'>{$date}</div>
                                    </div>
                                  </a>";
                        }
                    }
                    
                    if (!$has_notifications) {
                        echo '<div class="no-notifications">Нет новых уведомлений</div>';
                    }
                } catch(Exception $e) {
                    echo '<div class="no-notifications">Ошибка загрузки уведомлений</div>';
                }
                ?>
            </div>
            <div class="notifications-footer">
                <a href="orders.php" class="view-all-link">Все заказы</a> | 
                <a href="reviews.php" class="view-all-link">Все отзывы</a>
            </div>
        </div>
    </div>
    
    <!-- User Info -->
    <div class="admin-user-info">
        <div class="user-avatar">
            <?= strtoupper(substr($_SESSION['login'] ?? 'A', 0, 1)) ?>
        </div>
        <span><?= htmlspecialchars($_SESSION['login'] ?? 'Admin') ?></span>
    </div>
</div>

<script>
// Theme Management
(function() {
    const THEME_KEY = 'admin_theme';
    const DARK_THEME = 'dark';
    const LIGHT_THEME = 'light';
    
    // Get saved theme or default to light
    function getSavedTheme() {
        return localStorage.getItem(THEME_KEY) || LIGHT_THEME;
    }
    
    // Save theme to localStorage
    function saveTheme(theme) {
        localStorage.setItem(THEME_KEY, theme);
    }
    
    // Apply theme to document
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update toggle buttons
        const buttons = document.querySelectorAll('.theme-toggle button');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.theme === theme) {
                btn.classList.add('active');
            }
        });
    }
    
    // Initialize theme on page load
    function initTheme() {
        const savedTheme = getSavedTheme();
        applyTheme(savedTheme);
    }
    
    // Set theme function (called from inline onclick)
    window.setTheme = function(theme) {
        applyTheme(theme);
        saveTheme(theme);
        
        // Add subtle animation
        document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    };
    
    // Run initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();

// Mobile Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('mobileMenuToggle');
    
    if (sidebar && toggle) {
        sidebar.classList.toggle('open');
        
        // Change hamburger icon
        const hamburger = toggle.querySelector('.hamburger');
        if (sidebar.classList.contains('open')) {
            hamburger.textContent = '✕';
        } else {
            hamburger.textContent = '☰';
        }
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('mobileMenuToggle');
    
    if (window.innerWidth <= 768 && sidebar && toggle) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
            const hamburger = toggle.querySelector('.hamburger');
            if (hamburger) {
                hamburger.textContent = '☰';
            }
        }
    }
});

// Toggle Notifications Dropdown
function toggleNotifications() {
    const dropdown = document.getElementById('notificationsDropdown');
    if (dropdown) {
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
}

// Close notifications when clicking outside
document.addEventListener('click', function(e) {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationsDropdown');
    
    if (bell && dropdown && !bell.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// Add click event to notification bell
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notificationBell');
    if (bell) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotifications();
        });
    }
});

// Add smooth scroll behavior
document.documentElement.style.scrollBehavior = 'smooth';

// Add fade-in animation to main content on load
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.admin-main');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
});

// Table row hover effects enhancement
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('.admin-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'background-color 0.15s ease';
        });
    });
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert, index) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000 + (index * 500));
    });
});
</script>
