<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$cart_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($cart_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_money = 0;
$checkout_items = [];
foreach ($products as $prod) {
    $qty = $_SESSION['cart'][$prod['id']];
    $subtotal = $prod['price'] * $qty;
    $total_money += $subtotal;
    $checkout_items[] = [
        'id'       => $prod['id'],
        'name'     => $prod['name'],
        'price'    => $prod['price'],
        'quantity' => $qty,
        'subtotal' => $subtotal
    ];
}

$error = '';
$order_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name    = trim($_POST['customer_name'] ?? '');
    $phone   = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $payment = $_POST['payment_method'] ?? 'COD';
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng!';
    } else {
        try {
            $pdo->beginTransaction();

            $sqlOrder = "INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_amount, payment_method, status) 
                         VALUES (:user_id, :name, :phone, :address, :total, :payment, 'pending')";
            $stmtOrder = $pdo->prepare($sqlOrder);
            $stmtOrder->execute([
                ':user_id' => $user_id,
                ':name'    => $name,
                ':phone'   => $phone,
                ':address' => $address,
                ':total'   => $total_money,
                ':payment' => $payment
            ]);

            $order_id = $pdo->lastInsertId();

            $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmtItem = $pdo->prepare($sqlItem);

            foreach ($checkout_items as $item) {
                $stmtItem->execute([
                    ':order_id'   => $order_id,
                    ':product_id' => $item['id'],
                    ':quantity'   => $item['quantity'],
                    ':price'      => $item['price']
                ]);
            }

            $pdo->commit();

            unset($_SESSION['cart']);
            $order_success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Lỗi hệ thống khi tạo đơn hàng: ' . $e->getMessage();
        }
    }
}

if (file_exists(__DIR__ . '/../includes/header.php')) {
    include_once __DIR__ . '/../includes/header.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - EcoRecycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', Arial, sans-serif; }
        .checkout-container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .checkout-title { font-size: 24px; font-weight: bold; color: #2e7d32; margin-bottom: 25px; }
        .checkout-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card h2 { font-size: 18px; margin-bottom: 20px; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: #2e7d32; }
        .order-summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .order-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #d32f2f; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
        .btn-order { width: 100%; padding: 14px; background: #2e7d32; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .btn-order:hover { background: #1b5e20; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .success-box { text-align: center; padding: 50px 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .btn-home { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2e7d32; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; }
        @media (max-width: 768px) { .checkout-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="checkout-container">
    <?php if ($order_success): ?>
        <div class="success-box">
            <h1 style="color: #2e7d32; margin-bottom: 15px;">🎉 Đặt hàng thành công!</h1>
            <p>Mã đơn hàng của bạn là: <strong>#<?= $order_id ?></strong></p>
            <p style="color: #64748b; margin-top: 8px;">Cảm ơn bạn đã chung tay bảo vệ môi trường cùng EcoRecycle.</p>
            <a href="index.php" class="btn-home">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <h1 class="checkout-title">Tiến hành thanh toán</h1>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="checkout-grid">
            <!-- Cột thông tin nhận hàng -->
            <div class="card">
                <h2>Thông tin nhận hàng</h2>
                <form action="checkout.php" method="POST">
                    <div class="form-group">
                        <label>Họ và tên người nhận *</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="0901234567" required>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ giao hàng chi tiết *</label>
                        <textarea name="customer_address" class="form-control" rows="3" placeholder="Số nhà, đường, phường/xã, quận/huyện..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Phương thức thanh toán</label>
                        <select name="payment_method" class="form-control">
                            <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                            <option value="Bank">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>
                    <button type="submit" name="place_order" class="btn-order">Xác nhận đặt hàng</button>
                </form>
            </div>

            <!-- Cột tóm tắt đơn hàng -->
            <div class="card">
                <h2>Đơn hàng của bạn</h2>
                <?php foreach ($checkout_items as $item): ?>
                    <div class="order-summary-item">
                        <span><?= htmlspecialchars($item['name']) ?> × <strong><?= $item['quantity'] ?></strong></span>
                        <span><?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ</span>
                    </div>
                <?php endforeach; ?>

                <div class="order-total">
                    <span>Tổng thanh toán:</span>
                    <span><?= number_format($total_money, 0, ',', '.') ?> VNĐ</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
if (file_exists(__DIR__ . '/../includes/footer.php')) {
    include_once __DIR__ . '/../includes/footer.php';
}
ob_end_flush();
?>
</body>
</html>
