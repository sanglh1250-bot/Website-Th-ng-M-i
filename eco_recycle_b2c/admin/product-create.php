<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$error = '';
$name = '';
$description = '';
$price = '';
$stock = '';
$image_url = '';
$status = 'active';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $image_url = trim($_POST['image_url'] ?? '');
    $status = $_POST['status'] ?? 'active';
    if ($name === '' || $description === '' || !is_numeric($price) || (float)$price < 0 || filter_var($stock, FILTER_VALIDATE_INT) === false || (int)$stock < 0) $error = 'Vui lòng nhập tên, mô tả, giá và tồn kho hợp lệ.';
    elseif (!in_array($status, ['active', 'hidden'], true)) $error = 'Trạng thái không hợp lệ.';
    else {
        $stmt = $pdo->prepare('INSERT INTO products (name,description,price,stock,image_url,status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$name, $description, (float)$price, (int)$stock, $image_url, $status]);
        header('Location: index.php?created=1');
        exit;
    }
}
$pageTitle = 'Thêm sản phẩm';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-page"><a class="back-link" href="index.php">← Quay lại quản trị</a>
    <div class="form-card"><span class="eyebrow">PRODUCT MANAGEMENT</span>
        <h1>Thêm sản phẩm</h1><?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="admin-form"><label>Tên sản phẩm<input name="name" required maxlength="180" value="<?= e($name) ?>"></label><label>Mô tả<textarea name="description" rows="5" required><?= e($description) ?></textarea></label>
            <div class="form-row"><label>Giá bán (VNĐ)<input type="number" name="price" min="0" step="1000" required value="<?= e((string)$price) ?>"></label><label>Số lượng tồn kho<input type="number" name="stock" min="0" step="1" required value="<?= e((string)$stock) ?>"></label></div><label>URL hình ảnh<input type="url" name="image_url" value="<?= e($image_url) ?>" placeholder="https://..."></label><label>Trạng thái<select name="status">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang bán</option>
                    <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Ẩn</option>
                </select></label><button class="button button-primary" type="submit">Lưu sản phẩm</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
