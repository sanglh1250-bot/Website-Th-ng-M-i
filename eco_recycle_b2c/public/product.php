<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    require_once __DIR__ . '/../includes/header.php';
    echo '<div style="text-align:center; padding: 60px 20px;"><h2>Sản phẩm không tồn tại!</h2><a href="products.php" class="btn-add-cart" style="text-decoration:none; display:inline-block; margin-top:15px;">Quay lại danh sách sản phẩm</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$user = $_SESSION['user'] ?? null;
$msg_success = '';
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;

    $currentStock = (int)($product['stock'] ?? 0);

    if ($currentStock <= 0) {
        $msg_error = "Sản phẩm này hiện đã hết hàng!";
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $currentQtyInCart = $_SESSION['cart'][$id] ?? 0;
        // Kiểm tra dồn giỏ hàng không vượt quá tồn kho
        if (($currentQtyInCart + $qty) > $currentStock) {
            $msg_error = "Số lượng muốn mua (" . ($currentQtyInCart + $qty) . ") vượt quá số lượng tồn kho còn lại (" . $currentStock . ")!";
        } else {
            $_SESSION['cart'][$id] = $currentQtyInCart + $qty;
            header("Location: cart.php");
            exit;
        }
    }
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$user) {
        $msg_error = "Vui lòng đăng nhập để gửi đánh giá.";
    } else {
        // Kiểm tra xem tài khoản này đã từng mua sản phẩm này trong đơn hàng 'completed' chưa
        $checkOrder = $pdo->prepare("
            SELECT COUNT(*) 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
        ");
        $checkOrder->execute([$user['id'], $id]);
        $hasBought = (int)$checkOrder->fetchColumn();

        if ($hasBought === 0) {
            $msg_error = "Chỉ khách hàng đã mua và nhận hàng thành công mới được đánh giá sản phẩm này.";
        } else {
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
            $comment = trim($_POST['comment'] ?? '');

            if ($rating < 1 || $rating > 5) {
                $msg_error = "Vui lòng chọn số sao hợp lệ (từ 1 đến 5 sao).";
            } elseif (mb_strlen($comment) < 5) {
                $msg_error = "Nội dung nhận xét tối thiểu phải có 5 ký tự.";
            } else {
                // Thêm mới hoặc Cập nhật đánh giá nếu đã từng đánh giá (vì DB có Unique user_id + product_id)
                $revStmt = $pdo->prepare("
                    INSERT INTO reviews (product_id, user_id, rating, comment, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        rating = VALUES(rating),
                        comment = VALUES(comment),
                        updated_at = NOW()
                ");
                $revStmt->execute([$id, $user['id'], $rating, $comment]);
                $msg_success = "Đánh giá của bạn đã được ghi nhận thành công!";
            }
        }
    }
}

$statStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_reviews,
        COALESCE(ROUND(AVG(rating), 1), 0) AS avg_rating
    FROM reviews 
    WHERE product_id = ?
");
$statStmt->execute([$id]);
$reviewStats = $statStmt->fetch(PDO::FETCH_ASSOC);

$listStmt = $pdo->prepare("
    SELECT r.*, u.name AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$listStmt->execute([$id]);
$reviewsList = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$canReview = false;
$userReview = null;
$reviewNotice = '';

if (!$user) {
    $reviewNotice = 'not_logged_in'; // Khách vãng lai chưa đăng nhập
} else {
    // Kiểm tra đã mua hàng thành công chưa
    $checkBuy = $pdo->prepare("
        SELECT COUNT(*) 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
    ");
    $checkBuy->execute([$user['id'], $id]);
    $boughtCount = (int)$checkBuy->fetchColumn();

    if ($boughtCount > 0) {
        $canReview = true;
        // Kiểm tra xem đã từng đánh giá sản phẩm này chưa
        $myRev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
        $myRev->execute([$id, $user['id']]);
        $userReview = $myRev->fetch(PDO::FETCH_ASSOC);
    } else {
        $reviewNotice = 'not_purchased'; // Đã đăng nhập nhưng chưa mua
    }
}

$pageTitle = $product['name'];
require_once __DIR__ . '/../includes/header.php';
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
