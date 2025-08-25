<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config.php';
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (!password_verify($current_password, $user['password'])) {
        $error_message = "Неверный текущий пароль!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Новые пароли не совпадают!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Новый пароль должен содержать минимум 6 символов!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Пароль успешно изменен!";
        } else {
            $error_message = "Ошибка при изменении пароля: " . $conn->error;
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_email') {
    $new_email = $_POST['new_email'];
    $password = $_POST['password'];
    if (!password_verify($password, $user['password'])) {
        $error_message = "Неверный пароль!";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_email, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error_message = "Пользователь с таким email уже существует!";
        } else {
            $update_sql = "UPDATE users SET email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_email, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Email успешно изменен!";
                $user['email'] = $new_email; 
            } else {
                $error_message = "Ошибка при изменении email: " . $conn->error;
            }
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_username') {
    $new_username = $_POST['new_username'];
    $password = $_POST['password'];
    if (!password_verify($password, $user['password'])) {
        $error_message = "Неверный пароль!";
    } else {
        $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_username, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error_message = "Пользователь с таким именем уже существует!";
        } else {
            $update_sql = "UPDATE users SET username = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_username, $user_id);
            if ($update_stmt->execute()) {
                $success_message = "Имя пользователя успешно изменено!";
                $user['username'] = $new_username; 
                $_SESSION['username'] = $new_username;
            } else {
                $error_message = "Ошибка при изменении имени пользователя: " . $conn->error;
            }
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_first_name') {
    $new_first_name = $_POST['new_first_name'];
    $password = $_POST['password'];
    if (!password_verify($password, $user['password'])) {
     $error_message = "Неверный пароль!";
    } else {
        $update_sql = "UPDATE users SET first_name = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_first_name, $user_id);
        if ($update_stmt->execute()) {
            $success_message = "Имя успешно изменено!";
            $user['first_name'] = $new_first_name; 
        } else {
            $error_message = "Ошибка при изменении имени: " . $conn->error;
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_last_name') {
    $new_last_name = $_POST['new_last_name'];
    $password = $_POST['password'];
    if (!password_verify($password, $user['password'])) {
     $error_message = "Неверный пароль!";
    } else {
        $update_sql = "UPDATE users SET last_name = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_last_name, $user_id);
        if ($update_stmt->execute()) {
            $success_message = "Имя успешно изменено!";
            $user['last_name'] = $new_last_name; 
        } else {
            $error_message = "Ошибка при изменении имени: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройки профиля</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { display: flex; }
        .sidebar { width: 250px; background: #f8f9fa; padding: 20px; }
        .main { flex-grow: 1; padding: 20px; }
        .profile-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .profile-header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        .btn-primary { background: #808080; color: white; }
        .btn-success { background: #808080; color: white; }
        .btn-warning { background: #808080; color: white; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-box { background: #cfcfcfff; border: 1px solid #b1b1b1ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .role-badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; background: #808080; color: white; }
    </style>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar_admin.php'; ?>
        <div class="main">
            <h1>Настройки профиля</h1>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="profile-header">
                <h2>Информация о профиле</h2>
                <div class="info-box">
                    <p><strong>ID пользователя:</strong> <?php echo $user['id']; ?></p>
                    <p><strong>Имя пользователя:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                    <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Роль:</strong> 
                        <span class="role-badge">
                            <?php 
                            switch($user['role']) {
                                case 'owner': echo 'Владелец'; break;
                                case 'warehouse_manager': echo 'Менеджер склада'; break;
                                case 'order_manager': echo 'Менеджер заказов'; break;
                                case 'client': echo 'Клиент'; break;
                                default: echo $user['role'];
                            }
                            ?>
                        </span>
                    </p>
                    <p><strong>Дата регистрации:</strong> <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
<div class="profile-section">
    <h3>Изменить пароль</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <input type="password" name="current_password" required placeholder="Текущий пароль">
        </div>
        <div class="form-group">
            <input type="password" name="new_password" required minlength="6" placeholder="Новый пароль">
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" required minlength="6" placeholder="Подтвердите новый пароль">
        </div>
        <button type="submit" class="btn btn-primary">Изменить пароль</button>
    </form>
</div>
<div class="profile-section">
    <h3>Изменить email</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_email">
        <div class="form-group">
            <input type="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Новый email">
        </div>
        <div class="form-group">
            <input type="password" name="password" required placeholder="Пароль для подтверждения">
        </div>
        <button type="submit" class="btn btn-success">Изменить email</button>
    </form>
</div>
<div class="profile-section">
    <h3>Изменить имя</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_first_name">
        <div class="form-group">
            <input type="text" name="new_first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required placeholder="Новое имя">
        </div>
        <div class="form-group">
            <input type="password" name="password" required placeholder="Пароль для подтверждения">
        </div>
        <button type="submit" class="btn btn-success">Изменить имя</button>
    </form>
</div>
<div class="profile-section">
    <h3>Изменить фамилию</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_last_name">
        <div class="form-group">
            <input type="text" name="new_last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required placeholder="Новая фамилия">
        </div>
        <div class="form-group">
            <input type="password" name="password" required placeholder="Пароль для подтверждения">
        </div>
        <button type="submit" class="btn btn-success">Изменить фамилию</button>
    </form>
</div>
<div class="profile-section">
    <h3>Изменить имя пользователя</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_username">
        <div class="form-group">
            <input type="text" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Новое имя пользователя">
        </div>
        <div class="form-group">
            <input type="password" name="password" required placeholder="Пароль для подтверждения">
        </div>
        <button type="submit" class="btn btn-success">Изменить имя пользователя</button>
    </form>
</div>
<style>
         .profile-section {
            max-width: 100%; 
            margin: 20px auto; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 585px;
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box; 
            font-size: 11px; 
            text-align: left
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none; 
        }
        .btn {
            padding: 10px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 11px;
            text-align: center;
        }
        .btn-primary {
            color: white; 
        }
        .btn-success {
            color: white; 
        }
        .stats-grid div {
        padding-left: 20px; 
        }
        .profile-section form{
            display: flex;
            flex-direction: column; 
            gap: 10px; 
        }
        </style>
            <div class="message" style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
                <strong>Совет:</strong> Для безопасности используйте сложные пароли, содержащие буквы, цифры и специальные символы.
            </div>
        </div>
    </div>
</body>
</html>
