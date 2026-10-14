<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$stmt=$pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([$id?:0]); $product=$stmt->fetch();
if(!$product){http_response_code(404);exit('Không tìm thấy sản phẩm.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??''); $description=trim($_POST['description']??''); $price=$_POST['price']??''; $stock=$_POST['stock']??''; $image_url=trim($_POST['image_url']??''); $status=$_POST['status']??'active';
    if($name===''||$description===''||!is_numeric($price)||(float)$price<0||filter_var($stock,FILTER_VALIDATE_INT)===false||(int)$stock<0) $error='Vui lòng nhập tên, mô tả, giá và tồn kho hợp lệ.';
    elseif(!in_array($status,['active','hidden'],true)) $error='Trạng thái không hợp lệ.';
    else{$update=$pdo->prepare('UPDATE products SET name=?,description=?,price=?,stock=?,image_url=?,status=? WHERE id=?');$update->execute([$name,$description,(float)$price,(int)$stock,$image_url,$status,$id]);header('Location: index.php?updated=1');exit;}
    $product=array_merge($product,['name'=>$name,'description'=>$description,'price'=>$price,'stock'=>$stock,'image_url'=>$image_url,'status'=>$status]);
}
$pageTitle='Sửa sản phẩm';require __DIR__.'/../includes/header.php';
?>
<div class="form-page"><a class="back-link" href="index.php">← Quay lại quản trị</a><div class="form-card"><span class="eyebrow">PRODUCT MANAGEMENT</span><h1>Sửa sản phẩm #<?= (int)$id ?></h1><?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><form method="post" class="admin-form"><label>Tên sản phẩm<input name="name" required maxlength="180" value="<?= e($product['name']) ?>"></label><label>Mô tả<textarea name="description" rows="5" required><?= e($product['description']) ?></textarea></label><div class="form-row"><label>Giá bán (VNĐ)<input type="number" name="price" min="0" step="1000" required value="<?= e((string)$product['price']) ?>"></label><label>Số lượng tồn kho<input type="number" name="stock" min="0" required value="<?= e((string)$product['stock']) ?>"></label></div><label>URL hình ảnh<input type="url" name="image_url" value="<?= e($product['image_url']) ?>"></label><label>Trạng thái<select name="status"><option value="active" <?= $product['status']==='active'?'selected':'' ?>>Đang bán</option><option value="hidden" <?= $product['status']==='hidden'?'selected':'' ?>>Ẩn</option></select></label><button class="button button-primary" type="submit">Cập nhật sản phẩm</button></form></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
