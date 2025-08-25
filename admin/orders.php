<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['owner', 'order_manager'])) {
    header("Location: login.php");
    exit();
}
include '../config.php';
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];   
    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);
    if ($stmt->execute()) {
        $success_message = "Статус заказа успешно обновлен!";
    } else {
        $error_message = "Ошибка при обновлении статуса: " . $conn->error;
    }
}
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
    <style>   
    .container {
    display: flex;
}
.sidebar {
    width: 250px;
    background: #f8f9fa;
    padding: 20px;
}
.main {
    flex-grow: 1;
    padding: 20px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #000000ff;
}

.stat-label {
    color: #666;
    margin-top: 5px;
}

.orders-table {
    width: 1170px; /* Установите фиксированную ширину таблицы */
    border-collapse: collapse;
    margin: 50px; /* Добавляем отступ в 50 пикселей */
}

.orders-table th,
.orders-table td {
    border: 1px solid black; /* Установите цвет границы в черный */
    padding: 12px;
    text-align: left; /* Выравнивание текста по умолчанию влево */
    width: 200px; /* Задайте фиксированную ширину для ячеек */
}

.orders-table th {
    background: transparent; /* Убираем заливку у заголовков */
    text-align: center; /* Выравнивание текста заголовков по центру */
}

.orders-table td {
    text-align: center; /* Выравнивание текста в ячейках по центру */
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 5px;
}

.btn-primary {
    background: #515151ff;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-warning {
    background: #dcdcdcff;
    color: black;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-pending {
    background: #dcdcdcff;
    color: #515151ff; /* исправлено: убран лишний символ */
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.message {
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.order-details {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}


    </style>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar_admin.php'; ?>
        <div class="main">
            <h1>Управление заказами</h1>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Всего заказов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                    <div class="stat-label">Ожидают обработки</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['processing_orders']; ?></div>
                    <div class="stat-label">В обработке</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['shipped_orders']; ?></div>
                    <div class="stat-label">Отправлены</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['delivered_orders']; ?></div>
                    <div class="stat-label">Доставлены</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['cancelled_orders']; ?></div>
                    <div class="stat-label">Отменены</div>
                </div>
            </div>
            <h3>Список заказов:</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID заказа</th>
                        <th>Клиент</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td>
                                    <?php if ($row['user_id']): ?>
                                        <strong>Пользователь ID: <?php echo $row['user_id']; ?></strong><br>
                                        <small>Зарегистрированный пользователь</small>
                                    <?php else: ?>
                                        <strong><?php echo htmlspecialchars($row['costumer_name'] ?: 'Гость'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['costumer_phone'] ?: 'Телефон не указан'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($row['total'], 2); ?> ₽</td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php 
                                        switch($row['status']) {
                                            case 'pending': echo 'Ожидает'; break;
                                            case 'processing': echo 'Обрабатывается'; break;
                                            case 'shipped': echo 'Отправлен'; break;
                                            case 'delivered': echo 'Доставлен'; break;
                                            case 'cancelled': echo 'Отменен'; break;
                                            default: echo $row['status'];
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button onclick="viewOrderDetails(<?php echo $row['order_id']; ?>)" class="btn btn-primary">Детали</button>
                                    <button onclick="changeStatus(<?php echo $row['order_id']; ?>, '<?php echo $row['status']; ?>')" class="btn btn-warning">Изменить статус</button>
                                </td>
                            </tr>
                            <tr id="order-details-<?php echo $row['order_id']; ?>" style="display: none;">
                                <td colspan="6">
                                    <div class="order-details">
                                        <h4>Детали заказа #<?php echo $row['order_id']; ?></h4>
                                        <?php if ($row['user_id']): ?>
                                            <p><strong>ID пользователя:</strong> <?php echo $row['user_id']; ?></p>
                                            <p><strong>Тип:</strong> Зарегистрированный пользователь</p>
                                        <?php else: ?>
                                            <p><strong>Имя клиента:</strong> <?php echo htmlspecialchars($row['costumer_name'] ?: 'Не указано'); ?></p>
                                            <p><strong>Телефон клиента:</strong> <?php echo htmlspecialchars($row['costumer_phone'] ?: 'Не указан'); ?></p>
                                            <p><strong>Адрес доставки:</strong> <?php echo htmlspecialchars($row['costumer_address'] ?: 'Не указан'); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Сумма заказа:</strong> <?php echo number_format($row['total'], 2); ?> ₽</p>
                                        <p><strong>Дата создания:</strong> <?php echo date('d.m.Y H:i:s', strtotime($row['created_at'])); ?></p>
                                        <p><strong>Последнее обновление:</strong> <?php echo date('d.m.Y H:i:s', strtotime($row['updated_at'])); ?></p>
                                      <h5>Товары в заказе:</h5>
                                        <?php
                                        $order_items_sql = "SELECT oi.*, p.name, p.description, p.image_url 
                                                           FROM order_items oi 
                                                           LEFT JOIN products p ON oi.product_id = p.id 
                                                           WHERE oi.order_id = ?";
                                        $order_items_stmt = $conn->prepare($order_items_sql);
                                        $order_items_stmt->bind_param("i", $row['order_id']);
                                        $order_items_stmt->execute();
                                        $order_items_result = $order_items_stmt->get_result();
                                        if ($order_items_result->num_rows > 0): ?>
                                            <table class="order-items-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                                <thead>
                                                    <tr style="background-color: #f5f5f5;">
                                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Товар</th>
                                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Количество</th>
                                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Цена за шт.</th>
                                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Итого</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($item = $order_items_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td style="border: 1px solid #ddd; padding: 8px;">
                                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                                <?php if ($item['description']): ?>
                                                                    <br><small style="color: #666;"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?><?php echo strlen($item['description']) > 100 ? '...' : ''; ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo $item['quantity']; ?></td>
                                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo number_format($item['price'], 2); ?> ₽</td>
                                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ₽</strong></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p style="color: #999; font-style: italic;">Товары в заказе не найдены</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Заказы не найдены</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function viewOrderDetails(orderId) {
            const detailsRow = document.getElementById('order-details-' + orderId);
            if (detailsRow.style.display === 'none') {
                detailsRow.style.display = 'table-row';
            } else {
                detailsRow.style.display = 'none';
            }
        }
        function changeStatus(orderId, currentStatus) {
            const newStatus = prompt('Введите новый статус заказа (pending, processing, shipped, delivered, cancelled):', currentStatus);
            if (newStatus && newStatus !== currentStatus) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="${orderId}">
                    <input type="hidden" name="new_status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>