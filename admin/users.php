<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}
include '../config.php';
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $first_name=$_POST['first_name'];
    $last_name=$_POST['last_name'];
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();   
    if ($check_stmt->get_result()->num_rows > 0) {
        $error_message = "Пользователь с таким именем или email уже существует!";
    } else {
        $sql = "INSERT INTO users (username, email, password, role, first_name, last_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $email, $password, $role, $first_name, $last_name);
        
        if ($stmt->execute()) {
            $success_message = "Пользователь успешно создан!";
        } else {
            $error_message = "Ошибка при создании пользователя: " . $conn->error;
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $user_id = $_POST['user_id'];
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "Нельзя удалить свой собственный аккаунт!";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Пользователь успешно удален!";
        } else {
            $error_message = "Ошибка при удалении пользователя: " . $conn->error;
        }
    }
}
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'change_role') {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "Нельзя изменить роль своего собственного аккаунта!";
    } else {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Роль пользователя успешно изменена!";
        } else {
            $error_message = "Ошибка при изменении роли: " . $conn->error;
        }
    }
}
$sql = "SELECT id, username, email, role, first_name, last_name, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
$stats_sql = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'owner' THEN 1 ELSE 0 END) as owners,
    SUM(CASE WHEN role = 'warehouse_manager' THEN 1 ELSE 0 END) as warehouse_managers,
    SUM(CASE WHEN role = 'order_manager' THEN 1 ELSE 0 END) as order_managers,
    SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as clients
    FROM users";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление пользователями</title>
    <style>
body { 
    font-family: Arial, sans-serif; 
    margin: 0; 
    padding: 0; 
}
.container { 
    display: flex; 
}
.sidebar { 
    width: 250px; 
    background: #f8f9fa; 
    padding: 20px; 
}
.main { 
    flex-grow: 1; 
    padding: 20px; 
}
.stats-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
    gap: 20px; 
    margin-bottom: 30px; 
}
.stat-card { 
    background: white; 
    padding: 20px; 
    border-radius: 8px; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
    text-align: center; 
}
.stat-number { 
    font-size: 2em; 
    font-weight: bold; 
    color: #000; 
}
.stat-label { 
    color: #666; 
    margin-top: 5px; 
}
.user-form { 
    background: #f8f9fa; 
    padding: 20px; 
    border-radius: 8px; 
    margin-bottom: 20px; 
}
.form-group { 
    margin-bottom: 15px; 
}
.form-group label { 
    display: block; 
    margin-bottom: 5px; 
    font-weight: bold; 
}
.form-group input, .form-group select { 
    width: 100%; 
    padding: 8px; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
}
.btn { 
    padding: 10px 20px; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    width: max-content;
    background: #808080; 
    color: white; 
    text-align: center; 
}
.btn-left {
    margin: 0; 
}
.users-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 20px; 
    font-size: 0.8em; 
}
.users-table th, 
.users-table td { 
    border: 1px solid black; 
    padding: 12px; 
    text-align: center; 
    background: transparent; 
}
.users-table th { 
    background: transparent; 
}
.role-badge { 
    padding: 4px 8px; 
    border-radius: 12px; 
    font-size: 0.8em; 
    font-weight: bold; 
    width: max-content; 
}
.role-owner { 
    background: #dc3545; 
    color: white; 
}
.role-warehouse_manager { 
    background: #007bff; 
    color: white; 
}
.role-order_manager { 
    background: #28a745; 
    color: white; 
}
.role-client { 
    background: #6c757d; 
    color: white; 
}
.message { 
    padding: 10px; 
    border-radius: 4px; 
    margin-bottom: 20px; 
}
.success { 
    background: #d4edda; 
    color: #155724; 
    border: 1px solid #c3e6cb; 
}
.error { 
    background: #f8d7da; 
    color: #721c24; 
    border: 1px solid #f5c6cb; 
}
.warning { 
    background: #fff3cd; 
    color: #856404; 
    border: 1px solid #ffeaa7; 
}
    </style>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar_admin.php'; ?>       
        <div class="main">
            <h1>Управление пользователями</h1>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Всего пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['owners']; ?></div>
                    <div class="stat-label">Владельцы</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['warehouse_managers']; ?></div>
                    <div class="stat-label">Менеджеры склада</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['order_managers']; ?></div>
                    <div class="stat-label">Менеджеры заказов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['clients']; ?></div>
                    <div class="stat-label">Клиенты</div>
                </div>
            </div>
            <div class="user-form">
                <h3>Добавить нового пользователя</h3>
                <form method="POST" class="comments">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Имя пользователя:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Имя:</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Фамилия:</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Роль:</label>
                        <select name="role" required>
                            <option value="client">Клиент</option>
                            <option value="warehouse_manager">Менеджер склада</option>
                            <option value="order_manager">Менеджер заказов</option>
                            <option value="owner">Владелец</option>
                        </select>
                    </div>
                    <button class="btn btn-left">Создать пользователя</button>
                </form>
            </div>
            <h3>Список пользователей</h3>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя пользователя</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Дата регистрации</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $row['role']; ?>">
                                        <?php 
                                        switch($row['role']) {
                                            case 'owner': echo 'Владелец'; break;
                                            case 'warehouse_manager': echo 'Менеджер склада'; break;
                                            case 'order_manager': echo 'Менеджер заказов'; break;
                                            case 'client': echo 'Клиент'; break;
                                            default: echo $row['role'];
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="changeUserRole(<?php echo $row['id']; ?>, '<?php echo $row['role']; ?>')" class="btn btn-warning">Изменить роль</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Удалить</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">Текущий пользователь</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Пользователи не найдены</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="message warning" style="margin-top: 20px;">
                <strong>Внимание!</strong> Удаление пользователей необратимо. Все связанные данные (заказы, корзины) также будут удалены.
            </div>
        </div>
    </div>
    <script>
        function changeUserRole(userId, currentRole) {
            const newRole = prompt('Введите новую роль пользователя (client, warehouse_manager, order_manager, owner):', currentRole);
            if (newRole && newRole !== currentRole) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="new_role" value="${newRole}">
                     `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>