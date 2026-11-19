<?php
require 'functions.php';
need_login();
$cart=$_SESSION['cart']??[];
if(!$cart)go('cart.php');
$officialId=official_seller_id($pdo);
if($officialId<=0){http_response_code(500);exit('Akun KaryaKita Official belum disiapkan.');}
$items=[];$groups=[];$subtotal=0;
foreach($cart as $id=>$qty){
 $q=$pdo->prepare('SELECT * FROM products WHERE id=?');$q->execute([(int)$id]);$p=$q->fetch();
 if(!$p||$qty>$p['stock']){flash('Stok salah satu produk berubah. Periksa keranjang.');go('cart.php');}
 $owner=(int)($p['seller_id']??0);if($owner<=0)$owner=$officialId;
 $p['owner_id']=$owner;$p['qty']=(int)$qty;$p['line_total']=(float)$p['price']*$p['qty'];
 $items[]=$p;$groups[$owner][]=$p;$subtotal+=$p['line_total'];
}
$couriers=['JNE REG'=>12000,'J&T EZ'=>12000,'SiCepat REG'=>11000,'AnterAja Reguler'=>10000];
$selectedCourier=$_POST['courier']??'JNE REG';if(!isset($couriers[$selectedCourier]))$selectedCourier='JNE REG';
$shippingCost=$couriers[$selectedCourier];
$groupCount=count($groups);
$grandTotal=$subtotal+($shippingCost*$groupCount);
$address=trim($_POST['address']??'');$phone=trim($_POST['phone']??'');$method=$_POST['method']??'Transfer Bank';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['create_order'])){
 if(strlen($address)<8||$phone==='')$error='Alamat lengkap dan nomor telepon wajib diisi.';
 elseif(!isset($couriers[$_POST['courier']??'']))$error='Silakan pilih ekspedisi yang tersedia.';
 elseif(!in_array($method,['Transfer Bank','E-Wallet'],true))$error='Metode pembayaran tidak valid.';
 else{
  $selectedCourier=$_POST['courier'];$shippingCost=$couriers[$selectedCourier];
  $pdo->beginTransaction();
  try{
   $created=[];
   foreach($groups as $ownerId=>$groupItems){
    $groupSubtotal=array_sum(array_map(fn($p)=>$p['line_total'],$groupItems));
    $groupTotal=$groupSubtotal+$shippingCost;
    $q=$pdo->prepare("INSERT INTO orders(user_id,total,subtotal,shipping_courier,shipping_cost,address,phone,payment_method,payment_status,order_status)
      VALUES(?,?,?,?,?,?,?,?,'menunggu_pembayaran','menunggu_pembayaran')");
    $q->execute([user()['id'],$groupTotal,$groupSubtotal,$selectedCourier,$shippingCost,$address,$phone,$method]);
    $orderId=(int)$pdo->lastInsertId();$created[]=$orderId;
    $ins=$pdo->prepare('INSERT INTO order_items(order_id,product_id,seller_id,product_name,price,quantity) VALUES(?,?,?,?,?,?)');
    $up=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
    foreach($groupItems as $p){
      $ins->execute([$orderId,$p['id'],$ownerId,$p['name'],$p['price'],$p['qty']]);
      $up->execute([$p['qty'],$p['id'],$p['qty']]);
      if(!$up->rowCount())throw new RuntimeException('Stok produk tidak cukup.');
    }
   }
   $pdo->commit();unset($_SESSION['cart']);
   $_SESSION['checkout_created_orders']=$created;
   flash(count($created)>1?'Pesanan berhasil dipisahkan berdasarkan toko. Lakukan pembayaran untuk setiap pesanan melalui halaman Pesanan Saya.':'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');
   go('orders.php');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('KaryaKita split checkout: '.$e->getMessage());$error='Checkout gagal diproses. Periksa kembali stok dan database.';}
 }
}
$pageTitle='Checkout';require 'header.php';
?>
<section class="section narrow">
 <h2>Checkout</h2>
 <?php if($error):?><div class="notice error"><?=e($error)?></div><?php endif;?>
 <div class="panel">
  <h3>Ringkasan per Toko</h3>
  <?php foreach($groups as $ownerId=>$groupItems):
   $ownerId=(int)$ownerId;$official=$ownerId===$officialId;
   $sellerName='KaryaKita Official';
   if(!$official){$sq=$pdo->prepare('SELECT COALESCE(NULLIF(store_name,""),name) FROM users WHERE id=? AND role="seller"');$sq->execute([$ownerId]);$sellerName=$sq->fetchColumn()?:'Toko Penjual';}
   $gsub=array_sum(array_map(fn($p)=>$p['line_total'],$groupItems));?>
   <div style="border:1px solid #eadfc9;border-radius:12px;padding:12px;margin:12px 0">
    <strong><?=e($sellerName)?></strong><?php if($official):?><small style="display:block;color:#946200">Produk resmi KaryaKita Official</small><?php else:?><small style="display:block;color:#2866AD">Pesanan diproses oleh penjual toko ini</small><?php endif;?>
    <?php foreach($groupItems as $p):?><p style="margin:8px 0"><?=e($p['name'])?> × <?=e($p['qty'])?><span style="float:right"><?=rupiah($p['line_total'])?></span></p><?php endforeach;?>
    <hr><p>Subtotal toko <b style="float:right"><?=rupiah($gsub)?></b></p>
    <p>Estimasi ongkir per toko <b style="float:right"><?=rupiah($shippingCost)?></b></p>
    <p><strong>Total pesanan toko</strong><strong style="float:right"><?=rupiah($gsub+$shippingCost)?></strong></p>
   </div>
  <?php endforeach;?>
  <p><b>Total seluruh pesanan</b><b style="float:right"><?=rupiah($grandTotal)?></b></p>
  <small>Keranjang yang berisi produk dari beberapa toko akan dibuat menjadi pesanan terpisah. Dengan begitu, setiap penjual hanya memproses transaksi tokonya sendiri.</small>
 </div>
 <form method="post" class="form-card"><?=csrf_field()?>
  <label>Alamat lengkap (wilayah Jabodetabek)<textarea name="address" required><?=e($address)?></textarea></label>
  <label>Nomor telepon<input name="phone" value="<?=e($phone)?>" required></label>
  <label>Pilih ekspedisi<select name="courier" required><?php foreach($couriers as $name=>$fee):?><option value="<?=e($name)?>" <?=$selectedCourier===$name?'selected':''?>><?=e($name)?> — <?=rupiah($fee)?> per toko</option><?php endforeach;?></select></label>
  <small>Tarif merupakan simulasi. Ongkir diterapkan pada setiap pesanan toko karena pesanan dipisahkan berdasarkan penjual.</small>
  <label>Metode pembayaran<select name="method"><option value="Transfer Bank" <?=$method==='Transfer Bank'?'selected':''?>>Transfer Bank</option><option value="E-Wallet" <?=$method==='E-Wallet'?'selected':''?>>E-Wallet</option></select></label>
  <button class="btn" type="submit" name="create_order" value="1">Buat Pesanan</button>
 </form>
</section>
<?php require 'footer.php';?>
