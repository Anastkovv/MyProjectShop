<?php
session_start();
include "../config.php";
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['owner', 'warehouse_manager', 'order_manager'])) {
    header('Location: login.php');
    exit();
}
$id_admin = true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .container {
            display: flex;
        }
        .main {
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
        }
        h1, .welcome-message {
            text-align: center; 
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar_admin.php'; ?> 
        <div class="main">
            <h1>Панель управления</h1>
            <div class="welcome-message">
                <span>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span> 
            </div>
        </div>
    </div>
</body>
</html>
