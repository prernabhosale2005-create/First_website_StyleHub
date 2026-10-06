<?php
require_once 'config.php'; require_login();
$userId=(int)$_SESSION['user_id'];
$stmt=$conn->prepare("SELECT ci.quantity,p.id product_id,p.name,p.price,p.stock,p.image FROM cart_items ci JOIN products p ON p.id=ci.product_id WHERE ci.user_id=? AND p.is_active=1");$stmt->bind_param('i',$userId);$stmt->execute();$res=$stmt->get_result();$items=[];$total=0;while($r=$res->fetch_assoc()){ $r['line_total']=$r['price']*$r['quantity'];$total+=$r['line_total'];$items[]=$r;}$stmt->close();
if(!$items){header('Location: cart.php');exit;}
$address='';$phone='';$stmt=$conn->prepare("SELECT phone,address FROM users WHERE id=?");$stmt->bind_param('i',$userId);$stmt->execute();$u=$stmt->get_result()->fetch_assoc();$stmt->close();$phone=$u['phone']??'';$address=$u['address']??'';
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $address=trim($_POST['address']??'');$phone=trim($_POST['phone']??'');$method=$_POST['payment_method']??'';$valid=['cod','upi','card','netbanking','wallet'];
    if($address===''||!preg_match('/^[0-9]{10}$/',$phone)||!in_array($method,$valid,true)){$message='Please complete delivery details and select a payment method.';}
    else {
        $conn->begin_transaction();
        try{
            $stmt=$conn->prepare("UPDATE users SET phone=?, address=? WHERE id=?");$stmt->bind_param('ssi',$phone,$address,$userId);$stmt->execute();$stmt->close();
            $orderNo='SH'.date('YmdHis').strtoupper(bin2hex(random_bytes(2)));
            $status='Confirmed';
            $stmt=$conn->prepare("INSERT INTO orders (user_id,order_number,total_amount,payment_method,payment_status,order_status,shipping_address,phone) VALUES (?,?,?,?,?,?,?,?)");$paidStatus=$method==='cod'?'Pending':'Pending';$stmt->bind_param('isdsssss',$userId,$orderNo,$total,$method,$paidStatus,$status,$address,$phone);$stmt->execute();$orderId=$stmt->insert_id;$stmt->close();
            foreach($items as $item){ if($item['quantity']>$item['stock']) throw new Exception('A product is out of stock.'); $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)");$stmt->bind_param('iiid',$orderId,$item['product_id'],$item['quantity'],$item['price']);$stmt->execute();$stmt->close(); $stmt=$conn->prepare("UPDATE products SET stock=stock-? WHERE id=?");$stmt->bind_param('ii',$item['quantity'],$item['product_id']);$stmt->execute();$stmt->close(); }
            $stmt=$conn->prepare("INSERT INTO payments (order_id,method,status,amount,transaction_reference,paid_at) VALUES (?,?,?,?,?,?)");$ref='DEMO-'.strtoupper(bin2hex(random_bytes(4)));$pstatus=$method==='cod'?'Pending':'Demo Successful';$paidAt=$method==='cod'?null:date('Y-m-d H:i:s');$stmt->bind_param('issdss',$orderId,$method,$pstatus,$total,$ref,$paidAt);$stmt->execute();$stmt->close();
            $estimated=date('Y-m-d',strtotime('+3 days'));$stmt=$conn->prepare("INSERT INTO deliveries (order_id,delivery_address,delivery_status,estimated_date) VALUES (?,?,?,?)");$deliveryStatus='Preparing';$stmt->bind_param('isss',$orderId,$address,$deliveryStatus,$estimated);$stmt->execute();$stmt->close();
            $stmt=$conn->prepare("DELETE FROM cart_items WHERE user_id=?");$stmt->bind_param('i',$userId);$stmt->execute();$stmt->close();
            $conn->commit();header('Location: order_success.php?order='.urlencode($orderNo));exit;
        }catch(Throwable $e){$conn->rollback();$message='Unable to place order: '.$e->getMessage();}
    }
}
$methodLabels=['cod'=>'Cash on Delivery','upi'=>'UPI','card'=>'Credit / Debit Card','netbanking'=>'Net Banking','wallet'=>'Digital Wallet'];
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checkout - Style Hub</title><link rel="stylesheet" href="style.css"></head><body>
<header><a class="logo" href="home.php">Style Hub</a><nav><a href="home.php">Home</a><a href="cart.php">Cart</a></nav><div class="header-right"><span class="welcome">Hi, <?=htmlspecialchars($_SESSION['user_name'])?></span><a class="auth-btn" href="logout.php">Logout</a></div></header>
<main class="section"><h2>Secure Checkout</h2><?php if($message):?><div class="admin-message error"><?=htmlspecialchars($message)?></div><?php endif;?><div class="checkout-layout"><form method="post" class="checkout-card"><h3>Delivery Details</h3><input type="tel" name="phone" value="<?=htmlspecialchars($phone)?>" placeholder="10-digit phone number" pattern="[0-9]{10}" required><textarea name="address" placeholder="Full delivery address" required><?=htmlspecialchars($address)?></textarea><h3>Choose Payment Method</h3><div class="payment-grid"><?php foreach($methodLabels as $key=>$label):?><label class="payment-option"><input type="radio" name="payment_method" value="<?=$key?>" required><span><b><?=$label?></b><small><?=$key==='cod'?'Pay when your order arrives.':'Demo payment — no real money is charged.'?></small></span></label><?php endforeach;?></div><button class="primary-btn full" type="submit">Place Order & Pay</button></form><aside class="summary-card"><h3>Order Summary</h3><?php foreach($items as $i):?><div class="summary-row"><span><?=htmlspecialchars($i['name'])?> × <?=$i['quantity']?></span><b>₹<?=number_format($i['line_total'],2)?></b></div><?php endforeach;?><hr><div class="summary-row total"><span>Total</span><b>₹<?=number_format($total,2)?></b></div><p class="demo-note">Payment is simulated for this academic project. No real transaction is processed.</p></aside></div></main></body></html>
