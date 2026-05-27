<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';
$user = null;
$is_edit = false;

// Получение ID пользователя
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php?error=not_found');
        exit;
    }
    $is_edit = true;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $login = trim($_POST['login']);
    $email = trim($_POST['email']);
    $fullname = trim($_POST['fullname']);
    $nickname = trim($_POST['nickname']);
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Проверка email на уникальность
    if ($is_edit) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $error = 'Email уже используется другим пользователем';
        }
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email уже зарегистрирован';
        }
    }
    
    if (empty($error)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE users SET login=?, email=?, fullname=?, nickname=?, gender=?, role=?, is_active=? WHERE id=?");
            $stmt->execute([$login, $email, $fullname, $nickname, $gender, $role, $is_active, $id]);
            $success = 'Пользователь обновлен';
            header('Location: user-edit.php?id=' . $id . '&success=updated');
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (login, email, fullname, nickname, gender, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$login, $email, $fullname, $nickname, $gender, $role]);
            header('Location: users.php?success=added');
            exit;
        }
    }
}

// Проверка параметров URL
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') $success = 'Пользователь успешно обновлен';
    if ($_GET['success'] === 'added') $success = 'Пользователь добавлен';
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Редактировать пользователя' : 'Добавить пользователя' ?> - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <header class="admin-header">
        <h1>
            <svg class="svg-icon svg-md"><use href="../assets/icons.svg#icon-user"></use></svg>
            <?= $is_edit ? 'Редактирование пользователя' : 'Добавление нового пользователя' ?>
        </h1>
        <?php include 'includes/theme-toggle.php'; ?>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-form" style="max-width: 900px;">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $is_edit ? 'edit_user' : 'add_user' ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>
            
            <div class="form-section">
                <h3>
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-info"></use></svg>
                    Основная информация
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Логин *</label>
                        <input type="text" name="login" value="<?= htmlspecialchars($user['login'] ?? '') ?>" required placeholder="username">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required placeholder="example@mail.ru">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" placeholder="Иванов Иван Иванович">
                    </div>
                    <div class="form-group">
                        <label>Никнейм</label>
                        <input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname'] ?? '') ?>" placeholder="NickName">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Пол</label>
                        <select name="gender">
                            <option value="male" <?= (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : '' ?>>Мужской</option>
                            <option value="female" <?= (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : '' ?>>Женский</option>
                            <option value="other" <?= (isset($user['gender']) && $user['gender'] === 'other') ? 'selected' : '' ?>>Другой</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Роль *</label>
                        <select name="role">
                            <option value="user" <?= (isset($user['role']) && $user['role'] === 'user') ? 'selected' : '' ?>>Пользователь</option>
                            <option value="admin" <?= (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : '' ?>>Администратор</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <?php if ($is_edit): ?>
            <div class="form-section">
                <h3>
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-shield"></use></svg>
                    Статус и безопасность
                </h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" <?= (isset($user['is_active']) && $user['is_active'] == 1) ? 'checked' : '' ?>>
                        <span>Аккаунт активен</span>
                    </label>
                </div>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                
                <h4 style="margin-bottom: 15px; color: var(--text-primary);">Сброс пароля</h4>
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" minlength="6" placeholder="Оставьте пустым, чтобы не менять">
                    <small style="color: var(--text-muted); font-size: 0.85rem;">Минимум 6 символов</small>
                </div>
                <button type="button" class="btn btn-warning" onclick="resetPassword()">🔑 Сбросить пароль</button>
            </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <a href="users.php" class="btn btn-secondary">
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-arrow-left"></use></svg>
                    Назад к списку
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-check"></use></svg>
                    <?= $is_edit ? 'Сохранить изменения' : 'Добавить пользователя' ?>
                </button>
            </div>
        </form>
    </div>
</main>

<form id="resetPasswordForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="new_password" id="new_password_field">
</form>

<style>
.form-section {
    background: var(--bg-surface);
    padding: 25px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.form-section h3 {
    margin: 0 0 20px;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-light);
}

.form-section h4 {
    margin: 0 0 15px;
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 600;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-size: 1rem;
    color: var(--text-primary);
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.svg-icon {
    width: 1em;
    height: 1em;
    fill: currentColor;
}

.svg-md {
    font-size: 1.5rem;
}

.svg-sm {
    font-size: 1rem;
}
</style>

<script>
function resetPassword() {
    const newPassword = prompt('Введите новый пароль (минимум 6 символов):');
    if (newPassword && newPassword.length >= 6) {
        document.getElementById('new_password_field').value = newPassword;
        document.getElementById('resetPasswordForm').submit();
    } else if (newPassword) {
        alert('Пароль должен содержать минимум 6 символов');
    }
}
</script>
</body>
</html>
