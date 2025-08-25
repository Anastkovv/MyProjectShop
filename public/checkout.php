<?php
session_start();
include '../config.php';
    if (empty($_SESSION['cart'])) {
        header ('Location: cart.php');
        exit();
    }
$error_message='';
$success_message='';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
        $costumer_name = mysqli_real_escape_string ($conn, $_POST['costumer_name']);
        // $quantity = mysqli_real_escape_string ($conn,$_POST['quantity']);
        $costumer_phone = mysqli_real_escape_string ($conn,$_POST['costumer_phone']);
        $costumer_address = mysqli_real_escape_string ($conn,$_POST['costumer_address']);
        $cart_items=[];
        $total=0;
        if (!empty($_SESSION['cart'])){
        $ids=implode(",",$_SESSION['cart']);
        $product_sql="SELECT * FROM products WHERE id IN ($ids)";
        $product_result=mysqli_query($conn, $product_sql);
        while ($product=mysqli_fetch_assoc($product_result)) {
            $quantity=$_SESSION['quantities'][$product['id']]??1;
            $product['quantity']=$quantity;
            $product['total']=$product['price']*$quantity;
            $total+=$product['total'];
            $cart_items[]=$product;
        }
        }    
        $order_sql="INSERT INTO orders (user_id, costumer_name, costumer_phone, costumer_address, total, status) VALUES (NULL, ?, ?, ?, ?,'pending')";
        $stmt=$conn->prepare($order_sql);
        $stmt->bind_param("sssd",$costumer_name, $costumer_phone, $costumer_address, $total);
        if($stmt->execute()){
            $order_id=$conn->insert_id;
            foreach($cart_items as $item){
                $item_sql="INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (?,?,?,?)";
                $item_stmt=$conn->prepare($item_sql);
                $item_stmt->bind_param("iiid", $order_id, $item['id'],$item['quantity'],$item['price']);
                $item_stmt->execute();
            }
            $_SESSION['cart']=[];
            $_SESSION['quantities']=[];
            $success_message='Заказ оформлен';
        }
        else{
            $error_message='Ошибка:'.$stmt->error;
        }
        }
    $cart_items=[];
    $total=0;
    if(!empty($_SESSION['cart'])){
        $ids=implode(",",$_SESSION['cart']);
        $product_sql="SELECT * FROM products WHERE id IN ($ids)";
        $product_result=mysqli_query($conn,$product_sql);
        while($product=mysqli_fetch_assoc($product_result)){
            $quantity=$_SESSION['quantities'][$product['id']]?? 1;
            $product['quantity']=$quantity;
            $product['total']=$product['price']*$quantity;
            $total+=$product['total'];
            $cart_items[]=$product;
        }
    }
$is_public=true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление покупки</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div style="display: flex;"> 
        <?php include '../sidebar.php'; ?>
        <main style="flex-grow: 1; padding: 15px;"> 
            <?php if (empty($cart_items)): ?>
                <div style="text-align: center;">
                <p>Ваша корзина пуста.</p>
                <a href="../index.php" style="color: black;">Перейти к товарам.</a>
                </div>
            <?php else: ?>
       <style>
    table {
        width: 100%; 
        table-layout: fixed;
        border-collapse: collapse; 
        margin-top: 20px; 
    }
    th, td {
        border: 1px solid #000000ff;
        padding: 10px; 
        text-align: center; 
    }
    .error-message {
        color: red; 
    }
    .success-message {
        color: green; 
    }
</style>
<h1>Оформление покупки</h1>
<?php if ($error_message): ?>
    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<?php if ($success_message): ?>
    <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Товар</th> 
                <th>Цена</th>
                <th>Количество</th>
                <th>Итоговая стоимость</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cart_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?> руб.</td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['total'], 2); ?> руб.</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                <p>Общая стоимость: <?php echo number_format($total,2);?>руб</p>
                <style>
    body {
        font-family: Arial, sans-serif; 
        margin: 0;
        padding: 20px;
        background-color: #f9f9f9; 
    }
    form {
        display: flex; 
        flex-direction: column; 
        max-width: 400px; 
        margin: auto; 
        padding: 20px; 
        background-color: white; 
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    }

    div {
        margin-bottom: 15px; 
    }

    input[name="costumer_name"], input[name="costumer_phone"], input[name="costumer_address"]{
        width: 400px; 
        padding: 10px; 
        border: 1px solid #ccc; 
        border-radius: 4px; 
        box-sizing: border-box;
        text-align: center;
    }
    button {
        width: 400px;
        padding: 10px; 
        background-color: #808080; 
        color: white; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer;
        text-transform: uppercase; 
        font-weight: bold; 
    }
    button:hover {
        background-color: #606060; 
    }
</style>

<form method="POST">
    <div>
        <input type="text" name="costumer_name" id="costumer_name" required placeholder="Имя покупателя">
    </div>
    <div>
        <input type="tel" name="costumer_phone" id="costumer_phone" required placeholder="Номер телефона">
    </div>
    <div>
        <input type="text" name="costumer_address" id="costumer_address" required placeholder="Адрес">
    </div>
    <button type="submit" name="place_order">Оформить заказ</button>
</form>
            <?php endif; ?>
        <?php endif; ?>
        </main>
    </div>
</body>
</html>