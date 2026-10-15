<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Lấy id sản phẩm từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm từ CSDL
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý khi nhấn nút Thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }

    header("Location: cart.php");
    exit;
}

// Xử lý khi nhấn nút Thêm vào yêu thích
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }

    if (!in_array($id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $id;
    }

    header("Location: wishlist.php");
    exit;
}
?>

<style>
.product-detail-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: Arial, sans-serif;
}

.product-detail-card {
    display: flex;
    gap: 40px;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    align-items: center;
}

.product-detail-img-wrapper {
    width: 450px;
    height: 380px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-detail-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-detail-info {
    flex: 1;
}

.brand-tag {
    color: #2e7d32;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
}

.product-detail-title {
    font-size: 28px;
    font-weight: bold;
    color: #111;
    margin: 0 0 15px 0;
}

.product-detail-price {
    font-size: 26px;
    color: #2e7d32;
    font-weight: bold;
    margin-bottom: 15px;
}

.product-detail-desc {
    color: #555;
    line-height: 1.6;
    margin-bottom: 20px;
}

.stock-status {
    color: #2e7d32;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 20px;
}

.purchase-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.qty-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.qty-input {
    width: 70px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    text-align: center;
}

.btn-group {
    display: flex;
    gap: 12px;
    margin-top: 10px;
}

.btn-add-cart {
    flex: 2;
    background-color: #2e7d32;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-add-cart:hover {
    background-color: #1b5e20;
}

.btn-wishlist {
    flex: 1;
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ccc;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-wishlist:hover {
    background-color: #e9ecef;
}

.btn-back {
    display: inline-block;
    margin-top: 20px;
    color: #555;
    text-decoration: none;
    font-size: 14px;
}
</style>

<div class="product-detail-container">
    <?php if ($product): ?>
        <?php 
            // Tự động quét tìm cột tên file ảnh trong CSDL
            $img_name = $product['image'] ?? $product['image_url'] ?? $product['thumbnail'] ?? $product['img'] ?? '';
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
                    // Nếu không tìm thấy file vật lý, dùng ảnh nguyên mẫu
                    $img_src = 'assets/images/' . $img_name;
                }
            }
        ?>
        <div class="product-detail-card">
            <div class="product-detail-img-wrapper">
                <?php if (!empty($img_src)): ?>
                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-detail-img">
                <?php else: ?>
                    <div style="color:#6c757d; font-size: 14px;">Chưa có hình ảnh</div>
                <?php endif; ?>
            </div>

            <div class="product-detail-info">
                <span class="brand-tag">ECORECYCLE - SẢN PHẨM XANH</span>
                <h1 class="product-detail-title"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-detail-price"><?= number_format($product['price'], 0, ',', '.') ?> đ</div>
                <div class="product-detail-desc"><?= nl2br(htmlspecialchars($product['description'] ?? 'Sản phẩm thân thiện với môi trường.')) ?></div>
                
                <div class="stock-status">✓ Còn sản phẩm trong kho</div>

                <form method="POST" class="purchase-form">
                    <div class="qty-group">
                        <label for="quantity">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" class="qty-input">
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="add_to_cart" class="btn-add-cart">🛒 Thêm vào giỏ hàng</button>
                        <button type="submit" name="add_to_wishlist" class="btn-wishlist">♡ Yêu thích</button>
                    </div>
                </form>

                <a href="products.php" class="btn-back">← Tiếp tục xem sản phẩm</a>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px 0;">
            <h2>Sản phẩm không tồn tại!</h2>
            <a href="products.php" class="btn-add-cart" style="display:inline-block; text-decoration:none; margin-top:15px;">Quay lại danh sách sản phẩm</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
