<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';  
    $db = 'mybase_shop';   
    $user = 'root';  
    $pass = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Неверное имя пользователя или пароль.";
        }
    } catch (PDOException $e) {
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
    }
}
$is_admin=true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div style="display: flex;">
    <?php include '../sidebar.php'; ?> 
        <main style="flex-grow: 1; padding: 15px;">
            <h1>Авторизация администатора</h1>
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <style>
                input[name="username"], input[name="password"] {
                    width: 200px;; 
                    padding: 10px; 
                    border: 1px solid #ccc; 
                    border-radius: 4px;
                    font-size: 16px; 
                    box-sizing: border-box; 
                    margin-bottom: 10px; 
                }
                button[type="submit"] {
                    padding: 10px;
                    font-size: 16px;
                    background-color: #808080;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                </style>
            <form method="POST" action="login.php">
                <div>
                   <input type="text" name="username" id="username" required placeholder="Имя пользователя">
                </div>
                <div>
                    <input type="password" name="password" id="password" required placeholder="Пароль">
                </div>
                <button type="submit">Войти</button>
            </form>
        </main>
    </div>
</body>
</html>