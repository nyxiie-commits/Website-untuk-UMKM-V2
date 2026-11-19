<?php
require 'functions.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare('SELECT p.*,c.name category_name,u.id store_id,u.name seller_name,u.role seller_role,u.store_name,u.store_logo,u.seller_status FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN users u ON u.id=p.seller_id WHERE p.id=? LIMIT 1');
$s->execute([$id]); $p=$s->fetch();
if(!$p){http_response_code(404);exit('Produk tidak ditemukan');}
$related=recommend_products($pdo,$id,4); $pageTitle=$p['name'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $qty=max(1,(int)($_POST['qty']??1));
 if($qty>(int)$p['stock']){flash('Jumlah melebihi stok.');go('product.php?id='.$id);}
 $_SESSION['cart'][$id]=min((int)$p['stock'],($_SESSION['cart'][$id]??0)+$qty);
 flash('Produk ditambahkan ke keranjang.');go('cart.php');
}
$isOfficial=in_array(($p['seller_role']??''),['super_admin','admin'],true);
$isSeller=($p['seller_role']??'')==='seller' && ($p['seller_status']??'')==='approved' && !empty($p['store_id']);
$logo=!empty($p['store_logo'])?url('uploads/store/'.rawurlencode(basename($p['store_logo']))):'';
require 'header.php';
?>
<style>
.product-seller-card{display:flex;align-items:center;gap:13px;padding:14px 16px;margin:18px 0;border:1px solid #eadfc9;border-radius:14px;background:#fff}
.product-seller-avatar{width:54px;height:54px;flex:none;border-radius:13px;object-fit:cover;background:#fff1c7;border:1px solid #eadfc9}
.product-seller-fallback{display:grid;place-items:center;color:#80582f;font-size:23px}
.product-seller-meta{min-width:0;flex:1}.product-seller-meta strong{display:block;color:#432e20}.product-seller-meta small{color:#806b55}.product-seller-byline{display:block;color:#876d53;font-size:12px;margin:4px 0 8px}
.product-seller-link{display:inline-flex;align-items:center;gap:6px;padding:9px 13px;border:1px solid #e7c995;border-radius:999px;color:#70491f;text-decoration:none;font-weight:600;white-space:nowrap}
.product-seller-link:hover{background:#fff3d9;color:#70491f}
.official-mark{background:#fff0cf;color:#a66a12}.official-verified{display:inline-block;margin-left:5px;padding:3px 7px;border-radius:99px;background:#fff0cf;color:#946200;font-size:10px}.official-link{background:#fff7e7;border-color:#efd18c;color:#946200}.product-official-card{border-color:#efd18c;background:#fffdf7}\n@media(max-width:520px){.product-seller-card{align-items:flex-start;flex-wrap:wrap}.product-seller-link{margin-left:67px}}
</style>
<section class="detail">
<img src="<?=e(image_url($p['image']))?>" alt="<?=e($p['name'])?>">
<div>
<small class="eyebrow"><?=e($p['category_name']??'Handmade')?></small>
<h1><?=e($p['name'])?></h1><h2 class="price"><?=rupiah($p['price'])?></h2>
<p><?=nl2br(e($p['description']))?></p><p class="stock">Stok tersedia: <?=$p['stock']?></p>
<?php if($isOfficial):?>
<div class="product-seller-card product-official-card">
 <div class="product-seller-avatar product-seller-fallback official-mark"><i class="bi bi-patch-check-fill"></i></div>
 <div class="product-seller-meta"><strong>KaryaKita Official <span class="official-verified">✓ Resmi</span></strong><small>Dijual langsung oleh KaryaKita</small></div>
 <span class="product-seller-link official-link"><i class="bi bi-shield-check"></i> Official Store</span>
</div>
<?php elseif($isSeller):?>
<div class="product-seller-card">
<?php if($logo):?><img class="product-seller-avatar" src="<?=e($logo)?>" alt="Logo toko"><?php else:?><div class="product-seller-avatar product-seller-fallback"><i class="bi bi-shop"></i></div><?php endif;?>
<div class="product-seller-meta"><strong><?=e($p['store_name']?:$p['seller_name'])?></strong><small>Penjual KaryaKita</small></div>
<a class="product-seller-link" href="<?=url('store.php?id='.(int)$p['store_id'])?>"><i class="bi bi-shop"></i> Lihat Toko</a>
</div>
<?php endif;?>
<form method="post" class="buy-form"><?=csrf_field()?><input type="number" name="qty" value="1" min="1" max="<?=$p['stock']?>"><button class="btn" <?=$p['stock']<1?'disabled':''?>>Tambah ke Keranjang</button></form>
</div></section>
<section class="section"><h2>Produk yang mungkin kamu suka</h2><div class="products"><?php foreach($related as $r):?><article class="product"><a href="<?=url('product.php?id='.$r['id'])?>"><img src="<?=e(image_url($r['image']))?>"></a><div class="product-info"><a class="product-name" href="<?=url('product.php?id='.$r['id'])?>"><?=e($r['name'])?></a><b><?=rupiah($r['price'])?></b></div></article><?php endforeach;?></div></section>
<?php require 'footer.php'; ?>
