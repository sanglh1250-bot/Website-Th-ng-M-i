<?php
// Bắt đầu Session để lưu trữ sản phẩm yêu thích
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nhúng file kết nối CSDL và Header
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Khởi tạo mảng wishlist nếu chưa có
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Xử lý Thêm / Xóa sản phẩm khỏi danh sách yêu thích
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) {
        if ($action === 'add') {
            if (!in_array($product_id, $_SESSION['wishlist'])) {
                $_SESSION['wishlist'][] = $product_id;
            }
        } elseif ($action === 'remove') {
            $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$product_id]);
        }
    }

    header('Location: wishlist.php');
    exit;
}

// Lấy danh sách sản phẩm yêu thích từ Database
$wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['wishlist']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_values($_SESSION['wishlist']));
    $wishlist_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
.wishlist-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
    font-family: Arial, sans-serif;
}

.wishlist-title {
    font-size: 24px;
    font-weight: bold;
    color: #2e7d32;
    margin-bottom: 20px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.product-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    position: relative;
}

.product-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 12px;
}

.product-name {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin: 0 0 10px 0;
}

.product-price {
    font-size: 18px;
    color: #d32f2f;
    font-weight: bold;
    margin-bottom: 15px;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.btn-detail {
    flex: 1;
    text-align: center;
    background-color: #2e7d32;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}

.btn-remove {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.empty-wishlist {
    text-align: center;
    padding: 50px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.btn-shop {
    display: inline-block;
    padding: 10px 20px;
    background-color: #2e7d32;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}
</style>

<div class="wishlist-container">
    <h1 class="wishlist-title">Danh sách yêu thích của bạn</h1>

    <?php if (!empty($wishlist_products)): ?>
        <div class="product-grid">
            <?php foreach ($wishlist_products as $product): ?>
                <?php 
                    $img_name = $product['image'] ?? $product['image_url'] ?? $product['thumbnail'] ?? '';
                ?>
                <div class="product-card">
                    <div>
                        <?php if (!empty($img_name)): ?>
                            <img src="assets/images/<?= htmlspecialchars($img_name) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                    </div>
                    <div>
                        <div class="product-price"><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</div>
                        <div class="card-actions">
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn-detail">Xem chi tiết</a>
                            <form action="wishlist.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn-remove" title="Xóa khỏi yêu thích">✕</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-wishlist">
            <p style="font-size: 18px; color: #666; margin-bottom: 20px;">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
            <a href="products.php" class="btn-shop">Khám phá sản phẩm ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Nhúng Footer
require_once __DIR__ . '/../includes/footer.php';
?>
