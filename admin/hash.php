<?php
session_start();
$hash = '';
$password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_DEFAULT);
}
$is_admin = true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">

<!-- Хеширование паролей -->
</head>
<body>
<div style="display: flex;">
<?php include '../sidebar.php'; ?>
<main style="flex-grow: 1; padding: 15px;">

<!-- Генератор хешей паролей -->
<form method="post">
<div>

<!-- Введите пароль: -->
<input type="text" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>" required>
</div>

<!-- Сгенерировать хеш -->
</form>
<?php if ($hash): ?>
<div style="margin-top: 20px;">

<!-- Хеш пароля: -->
<textarea readonly style="width: 100%; height: 60px; font-family: monospace;"><?php echo $hash; ?></textarea>

<!-- SQL запрос для обновления: -->
<textarea readonly style="width: 100%; height: 40px; font-family: monospace;">UPDATE users SET password = '<?php echo $hash; ?>' WHERE username = 'ИМЯ_ПОЛЬЗОВАТЕЛЯ';</textarea>
</div>
<?php endif; ?>
</main>
</div>
</body>
</html>