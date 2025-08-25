<?php
session_start();
include "../config.php";
if(!isset($_SESSION['user_id'])||!in_array($_SESSION['user_role'],['owner','warehouse_manager'])){
    header('Location:login.php');
    exit();
}
if($_POST&&isset($_POST["action"])&&$_POST["action"]=="add"){
    $name=$_POST["name"];
    $description=$_POST["description"];
    $price=$_POST["price"];
    $stock=$_POST["stock"];
    $image_url=$_POST["image_url"];
    $sql="INSERT INTO products(name, description, price, stock, image_url) VALUES (?,?,?,?,?)";
    $stmt=$conn->prepare($sql);
    $stmt->bind_param("ssdss",$name,$description,$price,$stock,$image_url);
    if($stmt->execute()){
        $success_message="Товар добавлен";
    } else{
        $error_message="Ошибка при добавлении товара".$conn->error;
    }
}
if($_POST&&isset($_POST["action"])&&$_POST["action"]=="edit"){
    $id=$_POST["id"];
    $name=$_POST["name"];
    $description=$_POST["description"];
    $price=$_POST["price"];
    $stock=$_POST["stock"];
    $image_url=$_POST["image_url"];
    $sql="UPDATE products SET name=?, description=?, price=?, stock=?, image_url=? WHERE id=?";
    $stmt=$conn->prepare($sql);
    $stmt->bind_param("ssdssi",$name,$description,$price,$stock,$image_url,$id);
    if($stmt->execute()){
        $success_message="Товар обновлен";
    } else{
        $error_message="Ошибка при обновлении товара".$conn->error;
    }
} 
if($_POST&&isset($_POST["action"])&&$_POST["action"]=="delete"){
    $id=$_POST["id"];
    $sql="DELETE FROM products WHERE id=?";
    $stmt=$conn->prepare($sql);
    $stmt->bind_param("i",$id);
    if($stmt->execute()){
        $success_message="Товар удален";
    } else{
        $error_message="Ошибка при удалении товара".$conn->error;
    }
}
$sql="SELECT * FROM products ORDER BY id DESC";
$result=$conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="main" style="display: flex;">
    <?php include 'sidebar_admin.php'; ?> 
    <div class="admin_products">
        <h1>Управление товарами</h1>
        <?php if(isset($success_message)): ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if(isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div>
            <h3>Добавить новый товар</h3>
            <form method="POST" class="comments">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <input type="text" name="name" id="name" required placeholder="Название товара">
                </div>
                <div class="form-group">
                    <textarea name="description" id="description" required placeholder="Описание товара"></textarea>
                </div>
                <div class="form-group">
                    <input type="number" name="price" id="price" required placeholder="Цена" step="0.01">
                </div>
                <div class="form-group">
                    <input type="number" name="stock" id="stock" required placeholder="Наличие">
                </div>
                <div class="form-group">
                    <input type="text" name="image_url" id="image_url" required placeholder="URL изображения">
                </div>
                <button type="submit" name="add_product" class="btn btn-primary">Добавить</button>
            </form>
        </div>
        <h3>Список товаров</h3>
        <table class="admin_table products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Товар</th> 
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Действие</th>
                </tr>
            </thead>
<tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td><?php echo $row['stock']; ?></td>
                            <td>
                                <button class="btn btn-success" onclick="editProduct(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['description']); ?>', <?php echo $row['price']; ?>, <?php echo $row['stock']; ?>, '<?php echo addslashes($row['image_url']); ?>')">Редактировать</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Нет доступных товаров.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Редактировать товар</h3>
        <form method="POST" class="form_edit">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label for="name">Название товара:</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label for="description">Описание товара:</label>
                <textarea name="description" id="edit_description" required></textarea> 
            </div>
            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="number" name="price" id="edit_price" required step="0.01">
            </div>
            <div class="form-group">
                <label for="stock">Наличие товара:</label>
                <input type="number" name="stock" id="edit_stock" required>
            </div>
            <div class="form-group">
                <label for="image_url">URL изображения:</label>
                <input type="text" name="image_url" id="edit_image" required>
            </div>
            <button type="submit" name="edit_product" class="btn btn-primary">Сохранить изменения</button>
            <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Отмена</button>
        </form>
    </div>
</div>
<style>
.form_edit{
    flex-direction: column;
}
.admin_products {
    margin-left: 50px;
}
.admin_table {
    width: 1170px;
    table-layout: fixed;
    border-collapse: collapse; 
    text-align: center;
}
.admin_table th, .admin_table td {
    border: 1px solid #000000ff;
    padding: 10px;
}
.form-group {
    margin-bottom: 15px; 
}
input[type="text"],
input[type="number"],
textarea {
    text-align: left;
    width: 585px;
    padding: 10px; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    box-sizing: border-box; 
    font-size: 16px; 
}
textarea {
    height: 100px; 
    resize: none; 
}
input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus {
    outline: none; 
}
.btn {
    padding: 8px 16px; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    margin-right: 5px; 
    transition:  0.3s; 
}
.btn-primary { 
    background: #808080; 
    color: white; 
}
.btn-primary:hover {
    background: #696969; 
}
.btn-success { 
    background: #808080; 
    color: white; 
    margin-bottom: 10px;
}
.btn-success:hover {
    background: #696969; 
}
.btn-danger { 
    background: #808080; 
    color: white; 
}
.btn-danger:hover {
    background: #696969; 
}
.btn-secondary {
    background: #ccc;
    color: #333;
}
.btn-secondary:hover {
    background: #bbb;
}
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
    background-color: #fefefe;
    padding: 20px;
    border: 1px solid #888;
    width: 40%;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover {
    color: black;
}
</style>
<script>
function editProduct(id, name, description, price, stock, image) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock').value = stock;
    document.getElementById('edit_image').value = image;
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('editModal');
    window.onclick = function(event) {
        if (event.target == modal) {
            closeEditModal();
        }
    }
});
</script>
</body>
</html>