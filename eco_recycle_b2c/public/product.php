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

$msg_cart_success = '';
if (isset($_GET['added'])) {$msg_cart_success = "Đã thêm sản phẩm vào giỏ hàng thành công!";}

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
            header("Location: product.php?id=" . $id . "&added=1");
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
                
                <!-- Hiển thị thông báo lỗi giỏ hàng (nếu có) -->
                <?php if (!empty($msg_error)): ?>
                     <div style="background:#ffebee; color:#c62828; padding:10px 14px; border-radius:6px; margin-bottom:15px; font-size:14px;">
                      ⚠️ <?= htmlspecialchars($msg_error) ?>
                  </div>
              <?php endif; ?>

              <?php if ((int)$product['stock'] > 0): ?>
                  <div class="stock-status" style="color: #2e7d32; font-weight: bold; margin-bottom: 15px;">
                      ✓ Còn <?= (int)$product['stock'] ?> sản phẩm trong kho
                  </div>

                  <form method="POST" class="purchase-form">
                      <div class="qty-group">
                          <label for="quantity">Số lượng:</label>
                          <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="qty-input">
                      </div>

                      <div class="btn-group">
                          <button type="submit" name="add_to_cart" class="btn-add-cart">🛒 Thêm vào giỏ hàng</button>
                          <button type="submit" name="add_to_wishlist" class="btn-wishlist">♡ Yêu thích</button>
                      </div>
                  </form>
              <?php else: ?>
                  <div class="stock-status" style="color: #d32f2f; font-weight: bold; margin-bottom: 15px;">
                      ✕ Tạm hết hàng
                  </div>
                  <div class="btn-group">
                      <button type="button" disabled class="btn-add-cart" style="background:#ccc; cursor:not-allowed;">Hết hàng</button>
                      <form method="POST">
                          <button type="submit" name="add_to_wishlist" class="btn-wishlist">♡ Yêu thích</button>
                      </form>
                  </div>
              <?php endif; ?>

                <a href="products.php" class="btn-back">← Tiếp tục xem sản phẩm</a>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px 0;">
            <h2>Sản phẩm không tồn tại!</h2>
            <a href="products.php" class="btn-add-cart" style="display:inline-block; text-decoration:none; margin-top:15px;">Quay lại danh sách sản phẩm</a>
        </div>
    <?php endif; ?>
  <!-- ================= KHU VỰC ĐÁNH GIÁ SẢN PHẨM ================= -->
    <div class="reviews-section" style="margin-top: 50px; background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <h2 style="font-size: 22px; color: #111; margin-bottom: 25px; border-bottom: 2px solid #e8f5e9; padding-bottom: 12px;">
            Đánh giá & Nhận xét sản phẩm
        </h2>

        <?php if (!empty($msg_success)): ?>
            <div style="background:#e8f5e9; color:#2e7d32; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
                ✓ <?= htmlspecialchars($msg_success) ?>
            </div>
        <?php endif; ?>

        <!-- 1. TỔNG QUAN ĐIỂM SAO -->
        <div style="display: flex; gap: 30px; align-items: center; background: #fdfdfd; border: 1px solid #f0f0f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
            <div style="text-align: center; min-width: 130px; border-right: 1px solid #eee; padding-right: 20px;">
                <div style="font-size: 42px; font-weight: 800; color: #2e7d32; line-height: 1;">
                    <?= $reviewStats['avg_rating'] ?>
                </div>
                <div style="color: #ffb300; font-size: 18px; margin: 6px 0;">
                    <?php 
                    $stars = round((float)$reviewStats['avg_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars ? '★' : '☆';
                    }
                    ?>
                </div>
                <div style="font-size: 13px; color: #777;">
                    <?= (int)$reviewStats['total_reviews'] ?> lượt đánh giá
                </div>
            </div>
            <div style="flex: 1; font-size: 14px; color: #555;">
                <p style="margin: 0 0 5px 0;"><strong>Chính sách đánh giá tại EcoRecycle:</strong></p>
                <p style="margin: 0;">Chỉ những khách hàng đã mua sản phẩm và đơn hàng hoàn thành mới có thể gửi đánh giá.</p>
            </div>
        </div>

        <!-- 2. PHÂN QUYỀN ĐÁNH GIÁ THEO NGHIỆP VỤ -->
        <?php if ($canReview): ?>
            <!-- ĐÃ MUA HÀNG THÀNH CÔNG -> HIỂN THỊ FORM GỬI ĐÁNH GIÁ -->
            <div style="background: #fbfbfb; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 35px;">
                <h3 style="font-size: 17px; margin: 0 0 15px 0; color: #2e7d32;">
                    <?= $userReview ? '✎ Chỉnh sửa đánh giá của bạn' : '✍ Viết đánh giá của bạn' ?>
                </h3>
                <form method="POST">
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px;">Chọn mức độ hài lòng:</label>
                        <select name="rating" style="padding: 8px 14px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; outline: none;">
                            <option value="5" <?= ($userReview['rating'] ?? 5) == 5 ? 'selected' : '' ?>>★★★★★ (5 sao - Rất tốt)</option>
                            <option value="4" <?= ($userReview['rating'] ?? 5) == 4 ? 'selected' : '' ?>>★★★★☆ (4 sao - Tốt)</option>
                            <option value="3" <?= ($userReview['rating'] ?? 5) == 3 ? 'selected' : '' ?>>★★★☆☆ (3 sao - Bình thường)</option>
                            <option value="2" <?= ($userReview['rating'] ?? 5) == 2 ? 'selected' : '' ?>>★★☆☆☆ (2 sao - Tệ)</option>
                            <option value="1" <?= ($userReview['rating'] ?? 5) == 1 ? 'selected' : '' ?>>★☆☆☆☆ (1 sao - Rất tệ)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 8px;">Nhận xét của bạn:</label>
                        <textarea name="comment" rows="4" placeholder="Chia sẻ cảm nhận thực tế về chất lượng sản phẩm..." required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; outline: none;"><?= htmlspecialchars($userReview['comment'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" name="submit_review" class="btn-add-cart" style="padding: 10px 24px; font-size: 14px;">
                        <?= $userReview ? 'Lưu cập nhật đánh giá' : 'Gửi đánh giá' ?>
                    </button>
                </form>
            </div>

        <?php elseif ($reviewNotice === 'not_logged_in'): ?>
            <!-- CHƯA ĐĂNG NHẬP -> CHỈ HIỆN ĐÁNH GIÁ, KHÔNG CHO ĐÁNH GIÁ -->
            <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 10px; padding: 18px 22px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between;">
                <div style="color: #2e7d32; font-size: 14.5px;">
                    <i class="fa-solid fa-lock" style="margin-right: 8px;"></i>
                    Bạn muốn đánh giá sản phẩm này? Vui lòng đăng nhập bằng tài khoản đã mua hàng.
                </div>
                <a href="login.php" style="background: #2e7d32; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; white-space: nowrap;">
                    Đăng nhập
                </a>
            </div>

        <?php elseif ($reviewNotice === 'not_purchased'): ?>
            <!-- ĐÃ ĐĂNG NHẬP NHƯNG CHƯA MUA SẢN PHẨM NÀY -->
            <div style="background: #fff8e1; border: 1px solid #ffe082; border-radius: 10px; padding: 15px 20px; margin-bottom: 30px; color: #8d6e63; font-size: 14px;">
                <i class="fa-solid fa-circle-info" style="color: #ff8f00; margin-right: 8px;"></i>
                Chỉ những khách hàng đã mua và nhận hàng thành công mới có thể gửi đánh giá cho sản phẩm này.
            </div>
        <?php endif; ?>

        <!-- 3. DANH SÁCH BÌNH LUẬN ĐÃ CÓ (HIỂN THỊ CÔNG KHAI CHO TẤT CẢ MỌI NGƯỜI) -->
        <div class="reviews-list">
            <?php if (!empty($reviewsList)): ?>
                <?php foreach ($reviewsList as $rev): ?>
                    <div style="border-bottom: 1px solid #f0f0f0; padding: 18px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                                    <?= strtoupper(substr($rev['user_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong style="font-size: 15px; color: #333;"><?= htmlspecialchars($rev['user_name'] ?? 'Khách hàng') ?></strong>
                                    <span style="display: inline-block; background: #e8f5e9; color: #2e7d32; font-size: 11px; padding: 2px 7px; border-radius: 12px; margin-left: 6px; font-weight: 600;">
                                        ✓ Đã mua hàng
                                    </span>
                                </div>
                            </div>
                            <span style="font-size: 12.5px; color: #888;">
                                <?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?>
                            </span>
                        </div>
                        <div style="color: #ffb300; font-size: 15px; margin-bottom: 6px;">
                            <?php 
                            for ($s = 1; $s <= 5; $s++) {
                                echo $s <= (int)$rev['rating'] ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <div style="color: #444; font-size: 14.5px; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 30px 0; color: #888; font-size: 14.5px;">
                    Chưa có đánh giá nào cho sản phẩm này.
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
