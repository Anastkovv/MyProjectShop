<nav>
    <ul>
        <li><a href="<?php echo isset($is_admin) ? '../index.php' : (isset($is_public) ? '../index.php' : 'index.php'); ?>">Главная</a></li>
        <li><a href="<?php echo isset($is_admin) ? '../public/cart.php' : (isset($is_public) ? 'cart.php' : 'public/cart.php'); ?>">Корзина</a></li>
        <li><a href="<?php echo isset($is_admin) ? '../public/checkout.php' : (isset($is_public) ? 'checkout.php' : 'public/checkout.php'); ?>">Оформление заказа</a></li>
        <li><a href="<?php echo isset($is_admin) ? 'login.php' : (isset($is_public) ? '../admin/login.php' : 'admin/login.php'); ?>">Войти как администратор</a></li>
    </ul>
</nav>