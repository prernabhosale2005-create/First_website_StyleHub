<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Please login to add items to your cart.']); exit; }
$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, min(20, (int)($_POST['quantity'] ?? 1)));
$stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param('i',$productId); $stmt->execute(); $product = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$product) { echo json_encode(['success'=>false,'message'=>'Product not found.']); exit; }
if ($qty > (int)$product['stock']) { echo json_encode(['success'=>false,'message'=>'Requested quantity is not available.']); exit; }
$stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->bind_param('ii',$_SESSION['user_id'],$productId); $stmt->execute(); $existing=$stmt->get_result()->fetch_assoc(); $stmt->close();
if ($existing) {
    $newQty = min((int)$product['stock'], (int)$existing['quantity'] + $qty);
    $stmt=$conn->prepare("UPDATE cart_items SET quantity=?, updated_at=NOW() WHERE id=?"); $stmt->bind_param('ii',$newQty,$existing['id']); $stmt->execute(); $stmt->close();
} else {
    $stmt=$conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?,?,?)"); $stmt->bind_param('iii',$_SESSION['user_id'],$productId,$qty); $stmt->execute(); $stmt->close();
}
$result=$conn->query("SELECT COALESCE(SUM(quantity),0) total FROM cart_items WHERE user_id=".(int)$_SESSION['user_id']);
$count=(int)$result->fetch_assoc()['total'];
echo json_encode(['success'=>true,'message'=>'Added to cart.','cart_count'=>$count]);
