<?php
require 'functions.php';need_login();
$q=$pdo->prepare("SELECT o.*,
 (SELECT GROUP_CONCAT(DISTINCT CASE WHEN u.role IN ('super_admin','admin') THEN 'KaryaKita Official' ELSE COALESCE(NULLIF(u.store_name,''),u.name) END SEPARATOR ', ')
  FROM order_items oi LEFT JOIN users u ON u.id=oi.seller_id WHERE oi.order_id=o.id) seller_label,
 (SELECT COUNT(DISTINCT oi.seller_id) FROM order_items oi WHERE oi.order_id=o.id) seller_count
 FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC");
$q->execute([user()['id']]);$orders=$q->fetchAll();
$pageTitle='Pesanan Saya';require 'header.php';
?>
<section class="section">
 <h2>Pesanan Saya</h2>
 <p>Setiap toko memiliki pesanan terpisah. Pembayaran produk toko mitra diverifikasi oleh penjual terkait, sedangkan produk KaryaKita Official diverifikasi oleh tim KaryaKita.</p>
 <?php foreach($orders as $o):?>
 <div class="order-card">
  <div><b>Pesanan #<?=e($o['id'])?></b><small><?=e($o['created_at'])?></small><small style="display:block">Toko: <?=e($o['seller_label']?:'Toko tidak diketahui')?></small></div>
  <div><?=rupiah($o['total'])?></div>
  <div><span class="status"><?=e(str_replace('_',' ',$o['payment_status']))?></span><small style="display:block"><?=e(str_replace('_',' ',$o['order_status']))?></small></div>
  <div>
   <?php if(in_array($o['payment_status'],['menunggu_pembayaran','ditolak'],true)):?><a class="btn small" href="<?=url('payment.php?id='.$o['id'])?>">Bayar / Upload Bukti</a><?php endif;?>
   <?php if($o['payment_proof']):?><a href="<?=url('uploads/payments/'.rawurlencode($o['payment_proof']))?>" target="_blank" rel="noopener">Lihat bukti pembayaran</a><a class="btn small" href="<?=url('receipt.php?id='.$o['id'])?>" target="_blank" rel="noopener">Cetak / Download Struk</a><?php endif;?>
   <?php if($o['payment_status']==='menunggu_verifikasi'):?><small style="display:block;margin-top:8px;color:#8a5a16">Menunggu verifikasi <?=stripos($o['seller_label']??'','KaryaKita Official')!==false?'KaryaKita Official':'penjual toko'?>.</small><?php endif;?>
  </div>
 </div>
 <?php endforeach;?>
 <?php if(!$orders):?><p>Belum ada pesanan.</p><?php endif;?>
</section>
<?php require 'footer.php';?>
