<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->execute([$id ?: 0]); $product = $stmt->fetch();
if (!$product) { http_response_code(404); $pageTitle='Không tìm thấy'; require __DIR__.'/../includes/header.php'; echo '<div class="empty-state"><h1>Không tìm thấy sản phẩm</h1><a href="products.php">Quay lại danh sách</a></div>'; require __DIR__.'/../includes/footer.php'; exit; }
$pageTitle = $product['name'];
require __DIR__ . '/../includes/header.php';
?>
<div class="detail-layout">
  <div class="detail-image"><img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" onerror="this.src='https://placehold.co/800x700/e8f2e8/245a3b?text=EcoRecycle'"></div>
  <div class="detail-copy"><span class="eyebrow">ECORECYCLE · SẢN PHẨM XANH</span><h1><?= e($product['name']) ?></h1><div class="detail-price"><?= money($product['price']) ?></div><p><?= nl2br(e($product['description'])) ?></p><p class="stock-note"><?= (int)$product['stock'] > 0 ? '✓ Còn ' . (int)$product['stock'] . ' sản phẩm trong kho' : 'Tạm hết hàng' ?></p><div class="notice">🌱 Cảm ơn bạn đã cân nhắc lựa chọn sản phẩm thân thiện với môi trường.</div><a class="button button-primary" href="products.php">← Tiếp tục xem sản phẩm</a></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
