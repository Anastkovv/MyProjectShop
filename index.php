<?php
session_start();
include 'config.php'; 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_GET['add_to_cart'])&&isset($_GET['id'])) {
    $product_id=(int)$_GET['id'];
    if (!in_array($product_id,$_SESSION['cart'])) {
        $_SESSION['cart'][]=$product_id;
    }
    header('Location:index.php'.($_GET['search']?'?search='.urlencode($_GET['search']):''));
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = $search ? "WHERE name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'" : '';

$total_query = "SELECT COUNT(*) FROM products $where";
$total_result = mysqli_query($conn, $total_query);
if (!$total_result) {
    die("Ошибка выполнения запроса: " . mysqli_error($conn));
}
$total_count = mysqli_fetch_row($total_result)[0];
$total_pages = ceil($total_count / $limit);

$sql = "SELECT * FROM products $where LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  
    <div style="display: flex;">
        
        <?php include 'sidebar.php'; ?> 
        <main style="flex-grow: 1; padding: 15px;">
            <h1>Электронные товары<h1>
            <h2>Каталог товаров</h2>
            <form method="get" action="index.php">
                <input type="text" name="search" placeholder="Поиск товара" value="<?php echo htmlspecialchars($search); ?>">
                <input type="submit" value="Поиск">
            </form>
            
            <div class="product-list">
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            $in_cart = in_array($product["id"], $_SESSION['cart']);
            echo "<div class='product-item'>
                    <a href='public/product.php?id=" . $product['id'] . "'>
                        <img src='" . (isset($product['image_url']) ? htmlspecialchars($product['image_url']) : 'path/to/default/image.jpg') . "' alt='" . htmlspecialchars($product['name']) . "' class='product-image'>
                        <h3 class='product-name'>" . htmlspecialchars($product['name']) . "</h3>
                    </a>
                    <p class='product-price'>Цена: " . number_format($product['price'], 2) . " руб.</p>";
                    if ($in_cart) {
                        echo "<p class='in-cart'>Товар в корзине</p>";
                    } else {
                        echo "<a class='add-to-cart' href='index.php?add_to_cart=1&id=" . $product['id'] . ($search ? "&search=" . urlencode($search) : "") . "'>Добавить в корзину</a>";
                    }
                    echo "</div>";
        }
    } else {
        echo "<p>Товары не найдены.</p>";
    }
    ?>
</div>

            <div class="pagination">
                <?php
                for ($i = 1; $i <= $total_pages; $i++) {
                    $page_param="?page=$i";
                    if($search){
                        $page_param.="&search=". urlencode($search);
                    }
                    echo "<a href='$page_param'>$i</a> ";
                }
                ?>
            </div>

        </main>
    </div>

</body>
</html>

<?php
$conn->close();
?>
