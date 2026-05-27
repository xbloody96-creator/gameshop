<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Обработка действий с пользователями
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'edit_user':
            $id = intval($_POST['id']);
            $login = trim($_POST['login']);
            $email = trim($_POST['email']);
            $fullname = trim($_POST['fullname']);
            $nickname = trim($_POST['nickname']);
            $gender = $_POST['gender'];
            $role = $_POST['role'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Проверка email на уникальность (кроме текущего пользователя)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $error = 'Email уже используется другим пользователем';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET login=?, email=?, fullname=?, nickname=?, gender=?, role=?, is_active=? WHERE id=?");
                $stmt->execute([$login, $email, $fullname, $nickname, $gender, $role, $is_active, $id]);
                $success = 'Пользователь обновлен';
            }
            break;
            
        case 'delete_user':
            $id = intval($_POST['id']);
            // Нельзя удалить самого себя
            if ($id == $_SESSION['user_id']) {
                $error = 'Нельзя удалить самого себя';
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                $stmt->execute([$id]);
                $success = 'Пользователь удален';
            }
            break;
            
        case 'reset_password':
            $id = intval($_POST['id']);
            $new_password = $_POST['new_password'];
            
            if (strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([$hashed, $id]);
                $success = 'Пароль сброшен';
            } else {
                $error = 'Пароль должен быть не менее 6 символов';
            }
            break;
    }
}

// Получение списка пользователей
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$sql = "SELECT * FROM users WHERE 1";
$params = [];

if ($search) {
    $sql .= " AND (login LIKE ? OR email LIKE ? OR fullname LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
if (!is_array($users)) $users = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1><svg class="icon-svg icon-sm" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Управление пользователями</h1>
                <?php include 'includes/theme-toggle.php'; ?>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Фильтры -->
            <div class="admin-form">
                <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                        <input type="text" name="search" placeholder="Поиск по логину, email, ФИО" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <select name="role">
                            <option value="">Все роли</option>
                            <option value="user" <?= $role_filter == 'user' ? 'selected' : '' ?>>Пользователь</option>
                            <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Администратор</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Фильтр</button>
                    <a href="users.php" class="btn btn-secondary">Сбросить</a>
                </form>
            </div>

            <!-- Список пользователей -->
            <div class="admin-form">
                <h2><svg class="icon-svg icon-sm" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> Пользователи (<?= count($users) ?>)</h2>
                <table class="admin-table admin-table-compact">
                    <thead>
                        <tr>
                            <th>Аватар</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="" class="user-avatar-small">
                                <?php else: ?>
                                    <div class="user-avatar-small" style="background:var(--bg-surface-2);display:flex;align-items:center;justify-content:center;border-radius:50%;width:35px;height:35px;font-size:0.8rem;">?</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['login'] ?? $user['nickname'] ?? $user['email'] ?? 'Без имени') ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                            <td>
                                <span class="status-badge status-badge-<?= $user['role'] == 'admin' ? 'active' : 'inactive' ?>">
                                    <?= $user['role'] == 'admin' ? 'Админ' : 'Польз.' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-badge-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $user['is_active'] ? '✓' : '✗' ?>
                                </span>
                            </td>
                            <td class="actions actions-compact">
                                <a href="user-edit.php?id=<?= $user['id'] ?>" class="btn-icon btn-edit" title="Редактировать">
                                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-edit"></use></svg>
                                </a>
                                <button class="btn-icon btn-info" style="color:var(--info);background:var(--info-bg);" onclick="viewUser(<?= $user['id'] ?>)" title="Просмотр">
                                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-eye"></use></svg>
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить пользователя?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Удалить">
                                            <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-cross"></use></svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal для просмотра деталей -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Информация о пользователе</h3>
                <button class="modal-close" onclick="document.getElementById('viewModal').classList.remove('active')">&times;</button>
            </div>
            <div id="viewContent"></div>
        </div>
    </div>

    <script>
        function viewUser(userId) {
            fetch('ajax/user_details.php?id=' + userId)
                .then(r => r.json())
                .then(data => {
                    let html = `
                        <p><strong>ID:</strong> ${data.id}</p>
                        <p><strong>Логин:</strong> ${data.login}</p>
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>ФИО:</strong> ${data.fullname || '-'}</p>
                        <p><strong>Никнейм:</strong> ${data.nickname || '-'}</p>
                        <p><strong>Пол:</strong> ${data.gender || '-'}</p>
                        <p><strong>Роль:</strong> ${data.role}</p>
                        <p><strong>Дата регистрации:</strong> ${data.created_at}</p>
                        <p><strong>Последний вход:</strong> ${data.last_login || 'Никогда'}</p>
                        <p><strong>Статус:</strong> ${data.is_active ? 'Активен' : 'Неактивен'}</p>
                    `;
                    document.getElementById('viewContent').innerHTML = html;
                    document.getElementById('viewModal').classList.add('active');
                });
        }
    </script>
</body>
</html>
