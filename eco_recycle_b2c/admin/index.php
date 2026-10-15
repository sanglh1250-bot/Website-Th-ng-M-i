<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Quản trị sản phẩm';
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="admin-heading">
    <div><span class="eyebrow">ADMIN CONTROL CENTER</span>
        <h1>Quản trị EcoRecycle</h1>
        <p>Quản lý danh mục sản phẩm của cửa hàng.</p>
    </div><a class="button button-primary" href="product-create.php">＋ Thêm sản phẩm</a>
</section>
<div class="admin-stats">
    <div class="admin-stat"><span>Tổng sản phẩm</span><strong><?= $totalProducts ?></strong></div>
    <div class="admin-stat"><span>Tài khoản khách hàng</span><strong><?= $totalUsers ?></strong></div>
    <div class="admin-stat"><span>Vai trò</span><strong>ADMIN</strong></div>
</div>
<div class="table-card">
    <div class="table-title">
        <h2>Danh sách sản phẩm</h2><span><?= count($products) ?> mục</span>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá bán</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?><tr>
                        <td>
                            <div class="table-product"><img src="<?= e($p['image_url']) ?>" alt="" onerror="this.src='https://placehold.co/80x80/e8f2e8/245a3b?text=Eco'">
                                <div><strong><?= e($p['name']) ?></strong><small>#<?= (int)$p['id'] ?></small></div>
                            </div>
                        </td>
                        <td><?= money($p['price']) ?></td>
                        <td><?= (int)$p['stock'] ?></td>
                        <td><span class="status <?= $p['status'] === 'active' ? 'status-active' : 'status-hidden' ?>"><?= $p['status'] === 'active' ? 'Đang bán' : 'Ẩn' ?></span></td>
                        <td>
                            <div class="actions"><a class="action-edit" href="product-edit.php?id=<?= (int)$p['id'] ?>">Sửa</a>
                                <form method="post" action="product-delete.php" onsubmit="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?')"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="action-delete" type="submit">Xóa</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>
                <?php if (!$products): ?><tr>
                        <td colspan="5" class="empty-cell">Chưa có sản phẩm nào.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
