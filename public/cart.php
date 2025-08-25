<?php
session_start();
include '../config.php'; 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION ['quantities'])) {
    $_SESSION['quantities'] = [];
}
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id; 
        $_SESSION['quantities'][$product_id] = 1; 
    }
    header("Location: cart.php");
    exit ();
}
if (isset($_POST['update_cart'])) {

    $quantities = $_POST['quantities'] ?? [];
    foreach ($quantities as $product_id => $quantity) {
        if ($quantity>0) {
           $_SESSION['quantities'][$product_id] = (int)$quantity; 
        }
    }
    }
if (isset($_POST['remove'])) {
    $product_id = (int)$_POST['remove'];
    if (($key = array_search($product_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        unset($_SESSION['quantities'][$product_id]); 
        $_SESSION['cart']=array_values($_SESSION['cart']);
    }
}
$products = [];
$total=0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', $_SESSION['cart']);
    $product_sql = "SELECT * FROM products WHERE id IN ($ids)";
    $product_result = mysqli_query($conn, $product_sql);

    while ($product = mysqli_fetch_assoc($product_result)) {
        $quantity=$_SESSION['quantities'][$product["id"]] ?? 1;
        $product['quantity'] = $quantity;
        $product['total'] = $product['price']*$quantity;
        $total += $product ['total'];
        $products[] = $product;
    }
}
$is_public=true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div style="display: flex;"> 
        <?php include '../sidebar.php'; ?> 
        <main style="flex-grow: 1; padding: 15px;"> 
            <h1>Корзина товаров</h1>
            <?php if (!empty($products)): ?>
                <form method="post" action="cart.php">
                    <div class="container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Количество</th>
                                    <th>Цена</th>
                                    <th>Итого</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td>
                                            <input type="number" name="quantities[<?php echo $product['id']; ?>]" 
                                                   value="<?php echo $product['quantity']; ?>" min="1" class="quantity-input">
                                        </td>
                                        <td><?php echo number_format($product['price'], 2); ?> руб.</td>
                                        <td><?php echo number_format($product['total'], 2); ?> руб.</td>
                                        <td>
                                            <button type="submit" name="remove" value="<?php echo $product['id']; ?>">Удалить</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                                </table>
                        <div class="cart-summary">
                            <p>Общая сумма: <?php echo number_format($total, 2); ?> руб.</p>
                            <button type="submit" name="update_cart">Обновить корзину</button>
                        </div>
                    </div>
                </form>
                <div class="checkout">
                    <a href="checkout.php" class="button">Оформить заказ</a>
                </div>
                    <style>
        table {
            width: 1170px; 
            table-layout: fixed; 
            border-collapse: collapse; 
        }
        th, td {
            border: 1px solid #000; 
            padding: 10px;
            text-align: center; 
        }
        .quantity-input {
            width: 50px; 
        }
        .cart-summary button, .checkout .button {
            width: 200px;
            padding: 10px 20px;
            background-color: #808080;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            font-size: 16px;
            text-decoration: none;
        }
         .cart-summary button:hover, .checkout .button:hover {
            transform: scale(1.1); 
            background-color: white;
            color: #000000ff; 
            border: 2px solid #808080; 
            margin-left: 20px;
        }
        footer {
            margin-top: 20px;
            text-align: center;
            padding: 10px 0; 
        }
    button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #808080; 
    color: white; 
    text-align: center; 
    margin: 5px; 
    transition: background-color 0.3s; 
}
button:hover {
    background-color: #666; 
}
.quantity-input {
    width: 50px; 
    text-align: center; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
    padding: 5px; 
    outline: none;
    transition: border-color 0.3s;
}
.quantity-input:focus {
    border-color: #808080; 
}
    </style>
            <?php else: ?>
                 <div style="text-align: center;">
                <p>Ваша корзина пуста.</p>
                 </div>
            <?php endif; ?>
        </main>
    </div>
    <footer>
        <p>&copy; Электронные товары</p>
    </footer>
</body>
</html>
