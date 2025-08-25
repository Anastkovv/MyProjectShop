<?php
if(!isset($_SESSION['user_id'])||!isset($_SESSION['user_role'])){
    return;
}
$user_role=$_SESSION['user_role'];
$username=$_SESSION['username'];
?>
<nav>
    <ul>
        <li><a href="../index.php">Назад на главную страницу</a></li>
        <li><a href="profile.php">Изменение профиля</a></li>
        <?php if($user_role=='owner'||$user_role=='warehouse_manager'):?>
        <li><a href="products.php">Управление товарами</a></li>
        <?php  endif;?>
        <?php if($user_role=='owner'||$user_role=='order_manager'):?>
        <li><a href="orders.php">Управление заказами</a></li>
        <?php  endif;?>
        <?php if($user_role=='owner'):?>
        <li><a href="users.php">Управление пользователями</a></li>
        <?php  endif;?>
        <li><a href="logout.php">Выйти</a></li>
    </ul>
</nav>