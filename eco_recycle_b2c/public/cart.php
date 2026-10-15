<?php
// Bắt đầu Session để lưu trữ thông tin giỏ hàng
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý các hành động: Cập nhật số lượng hoặc Xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action === 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($action === 'delete' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
    }

    // Load lại trang để cập nhật thông tin mới nhất
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Giỏ hàng của bạn';
require_once __DIR__ . '/../includes/header.php';

// Lấy danh sách sản phẩm trong giỏ hàng từ CSDL
$cart_products = [];
$total_money = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $fetched_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fetched_products as $prod) {
        $qty = $_SESSION['cart'][$prod['id']];
        $subtotal = $prod['price'] * $qty;
        $total_money += $subtotal;

        // Tìm đường dẫn ảnh chính xác
        $img_name = $prod['image'] ?? $prod['image_url'] ?? $prod['thumbnail'] ?? '';
        $img_src = '';

        if (!empty($img_name)) {
            if (file_exists(__DIR__ . '/assets/images/' . $img_name)) {
                $img_src = 'assets/images/' . $img_name;
            } elseif (file_exists(__DIR__ . '/uploads/' . $img_name)) {
                $img_src = 'uploads/' . $img_name;
            } elseif (file_exists(__DIR__ . '/../uploads/' . $img_name)) {
                $img_src = '../uploads/' . $img_name;
            } elseif (filter_var($img_name, FILTER_VALIDATE_URL)) {
                $img_src = $img_name;
            } else {
                $img_src = 'assets/images/' . $img_name;
            }
        }

        $cart_products[] = [
            'id' => $prod['id'],
            'name' => $prod['name'],
            'price' => $prod['price'],
            'image_src' => $img_src,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<style>
.cart-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 15px;
    font-family: Arial, sans-serif;
}

.cart-title {
    font-size: 24px;
    font-weight: bold;
    color: #2e7d32;
    margin-bottom: 20px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.cart-table th, .cart-table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.cart-table th {
    background-color: #f8f9fa;
    color: #333;
    font-weight: bold;
}

.cart-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.qty-input {
    width: 60px;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-align: center;
}

.btn-update {
    padding: 6px 12px;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.btn-delete {
    padding: 6px 12px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.cart-summary {
    margin-top: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.total-price {
    font-size: 20px;
    font-weight: bold;
    color: #d32f2f;
}

.btn-checkout {
    padding: 12px 25px;
    background-color: #2e7d32;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    font-size: 16px;
}

.btn-checkout:hover {
    background-color: #1b5e20;
}

.btn-continue {
    padding: 12px 20px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}

.empty-cart {
    text-align: center;
    padding: 50px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
</style>

<div class="cart-container">
    <h1 class="cart-title">Giỏ hàng của bạn</h1>

    <?php if (!empty($cart_products)): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tạm tính</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_products as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($item['image_src'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_src']) ?>" class="cart-img" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div class="cart-img" style="background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #888;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td>
                        <td>
                            <form action="cart.php" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                <button type="submit" class="btn-update">Cập nhật</button>
                            </form>
                        </td>
                        <td style="font-weight: bold; color: #d32f2f;">
                            <?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ
                        </td>
                        <td>
                            <form action="cart.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div>
                <a href="products.php" class="btn-continue">← Tiếp tục mua sắm</a>
            </div>
            <div style="text-align: right;">
                <div style="margin-bottom: 10px;">
                    Tổng tiền thanh toán: <span class="total-price"><?= number_format($total_money, 0, ',', '.') ?> VNĐ</span>
                </div>
                <a href="checkout.php" class="btn-checkout">Tiến hành thanh toán</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p style="font-size: 18px; color: #666; margin-bottom: 20px;">Giỏ hàng của bạn đang trống.</p>
            <a href="products.php" class="btn-checkout">Khám phá sản phẩm ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Nhúng Footer
require_once __DIR__ . '/../includes/footer.php';
?>
