<?php
require 'functions.php';
$storeId=(int)($_GET['id']??0);
$q=$pdo->prepare("SELECT id,name,store_name,store_description,store_address,store_phone,store_logo,store_banner FROM users WHERE id=? AND role='seller' AND seller_status='approved' LIMIT 1");
$q->execute([$storeId]); $store=$q->fetch();
if(!$store){http_response_code(404);$pageTitle='Toko tidak ditemukan';require 'header.php';?>
<main class="section"><h1>Toko tidak ditemukan</h1><p>Toko tidak tersedia atau belum disetujui untuk tampil di marketplace.</p><a class="btn" href="<?=url('index.php')?>">Kembali ke Beranda</a></main>
<?php require 'footer.php';exit;}
$ps=$pdo->prepare('SELECT p.*,c.name category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.seller_id=? ORDER BY p.created_at DESC');
$ps->execute([$storeId]);$products=$ps->fetchAll();
$pageTitle=$store['store_name']?:$store['name'];
$logo=!empty($store['store_logo'])?url('uploads/store/'.rawurlencode(basename($store['store_logo']))):'';
$banner=!empty($store['store_banner'])?url('uploads/store/'.rawurlencode(basename($store['store_banner']))):'';
require 'header.php';
?>
<style>
.public-store{max-width:1120px;margin:34px auto;padding:0 20px}
.public-store-panel{border:1px solid #eadfc9;border-radius:18px;background:#fff;margin-bottom:28px;overflow:hidden}
.public-store-banner{height:220px;background:linear-gradient(120deg,#fff0c7,#f4d99f)}
.public-store-banner img{width:100%;height:100%;object-fit:cover}
.public-store-identity{display:flex;align-items:flex-end;gap:17px;padding:0 25px 18px;margin-top:-42px;position:relative}
.public-store-logo{width:100px;height:100px;object-fit:cover;border-radius:19px;border:5px solid #fff;background:#fff1c7}
.public-store-logo-fallback{display:grid;place-items:center;color:#80582f;font-size:36px}
.public-store-name h1{font-family:Georgia,serif;margin:0 0 5px;color:#432e20}
.public-store-description{padding:0 25px 18px;color:#705b49;line-height:1.7}
.public-store-details{display:flex;gap:18px;flex-wrap:wrap;padding:0 25px 22px;color:#806b55;font-size:14px}
.public-store-products{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px}
@media(max-width:850px){.public-store-products{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:600px){.public-store-banner{height:155px}.public-store-identity{padding:0 15px 15px;gap:12px}.public-store-logo{width:78px;height:78px}.public-store-name h1{font-size:23px}.public-store-description,.public-store-details{padding-left:15px;padding-right:15px}.public-store-products{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}}
</style>
<main class="public-store">
<section class="public-store-panel">
<div class="public-store-banner"><?php if($banner):?><img src="<?=e($banner)?>" alt="Banner <?=e($pageTitle)?>"><?php endif;?></div>
<div class="public-store-identity">
<?php if($logo):?><img class="public-store-logo" src="<?=e($logo)?>" alt="Logo <?=e($pageTitle)?>"><?php else:?><div class="public-store-logo public-store-logo-fallback"><i class="bi bi-shop"></i></div><?php endif;?>
<div class="public-store-name"><h1><?=e($pageTitle)?></h1><small class="text-muted">Toko penjual KaryaKita</small></div>
</div>
<?php if($store['store_description']):?><div class="public-store-description"><?=nl2br(e($store['store_description']))?></div><?php endif;?>
<div class="public-store-details">
<?php if($store['store_address']):?><span><i class="bi bi-geo-alt"></i> <?=e($store['store_address'])?></span><?php endif;?>
<?php if($store['store_phone']):?><span><i class="bi bi-telephone"></i> <?=e($store['store_phone'])?></span><?php endif;?>
<span><i class="bi bi-box-seam"></i> <?=count($products)?> produk</span>
</div></section>
<section class="section"><div class="section-head"><div><span class="eyebrow">KATALOG TOKO</span><h2>Produk dari <?=e($pageTitle)?></h2></div></div>
<div class="public-store-products">
<?php foreach($products as $p):?><article class="product"><a href="<?=url('product.php?id='.(int)$p['id'])?>"><img src="<?=e(image_url($p['image']))?>" alt="<?=e($p['name'])?>"></a><div class="product-info"><small><?=e($p['category_name']??'Handmade')?></small><a class="product-name" href="<?=url('product.php?id='.(int)$p['id'])?>"><?=e($p['name'])?></a><b><?=rupiah($p['price'])?></b><span class="stock">Stok <?=$p['stock']?></span></div></article><?php endforeach;?>
<?php if(!$products):?><p>Toko ini belum memiliki produk.</p><?php endif;?>
</div></section></main>
<?php require 'footer.php'; ?>
