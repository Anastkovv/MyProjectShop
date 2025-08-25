<?php
session_start();
include '../config.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_sql = "SELECT * FROM products WHERE id = $product_id";
$product_result = mysqli_query($conn, $product_sql);
$product = mysqli_fetch_assoc($product_result);
if (!$product) {
    die("Товар не найден.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $comment_sql = "INSERT INTO comments (product_id, name, comment) VALUES ($product_id, '$name', '$comment')";
    mysqli_query($conn, $comment_sql);
}
$comments_sql = "SELECT * FROM comments WHERE product_id = $product_id ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_sql);
$is_public=true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div style="display: flex;">
    <?php include '../sidebar.php'; ?> 
    <main style="flex-grow: 1; padding: 15px;">
        <h1>Электронные товары</h1>
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>
        <h3>Комментарии</h3>
        <form class="comments" method="post" action="product.php?id=<?php echo $product_id; ?>">
            <textarea name="comment" required> вот моя страница продукта</textarea> 
            <input type="text" name="name" placeholder="Ваше имя" required>
            <input type="submit" value="Отправить">
        </form>
    </main>
</div>
<style>
    .product-image {
        width: 200px;
        height: auto; 
        border: 2px solid #808080; 
        border-radius: 5px; 
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); 
    }
</style>
    <footer>
        <p>&copy; 2025 Интернет-магазин</p>
    </footer>
</body>
</html>
