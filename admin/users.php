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
                                <button class="btn-icon btn-edit" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)"><svg class="icon-svg icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                <button class="btn-icon btn-info" style="color:var(--info);background:var(--info-bg);" onclick="viewUser(<?= $user['id'] ?>)"><svg class="icon-svg icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить пользователя?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn-icon btn-delete"><svg class="icon-svg icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg></button>
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

    <!-- Modal для редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Редактировать пользователя</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Логин *</label>
                        <input type="text" name="login" id="edit_login" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" name="fullname" id="edit_fullname">
                    </div>
                    <div class="form-group">
                        <label>Никнейм</label>
                        <input type="text" name="nickname" id="edit_nickname">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Пол</label>
                        <select name="gender" id="edit_gender">
                            <option value="male">Мужской</option>
                            <option value="female">Женский</option>
                            <option value="other">Другой</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" id="edit_role">
                            <option value="user">Пользователь</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" id="edit_is_active"> Активен
                    </label>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Отмена</button>
                </div>
            </form>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">
            
            <h4>Сброс пароля</h4>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" id="reset_id">
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" minlength="6" required>
                </div>
                <button type="submit" class="btn btn-warning">Сбросить пароль</button>
            </form>
        </div>
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
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('reset_id').value = user.id;
            document.getElementById('edit_login').value = user.login;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_fullname').value = user.fullname || '';
            document.getElementById('edit_nickname').value = user.nickname || '';
            document.getElementById('edit_gender').value = user.gender || 'male';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_is_active').checked = user.is_active == 1;
            document.getElementById('editModal').classList.add('active');
        }
        
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
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
