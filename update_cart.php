<?php
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) { http_response_code(401); echo 'Not logged in'; exit; }

$data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
$action = $data['action'] ?? '';
$itemId = (int)($data['item_id'] ?? 0);
$qty = max(1, (int)($data['qty'] ?? 1));
$size = trim($data['size'] ?? 'M');

$pdo = db();

try {
  if ($action === 'update' && $itemId > 0) {
    //Get current cart item and check available stock
    $cart_item = $pdo->prepare('SELECT product_name, size, qty FROM cart_items WHERE id = ? AND user_id = ?');
    $cart_item->execute([$itemId, $_SESSION['user_id']]);
    $item = $cart_item->fetch();
    
    if ($item) {
      $stock_check = $pdo->prepare('SELECT stock_quantity FROM inventory WHERE product_name = ? AND size = ?');
      $stock_check->execute([$item['product_name'], $item['size']]);
      $stock = $stock_check->fetch();
      
      $available = ($stock['stock_quantity'] ?? 0) + $item['qty']; // current stock + what's already in cart
      
      if ($qty > $available) {
        http_response_code(400);
        echo "Only $available items available";
        exit;
      }
      
      //Update inventory based on quantity change
      $qty_diff = $qty - $item['qty'];
      $pdo->prepare('UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE product_name = ? AND size = ?')
          ->execute([$qty_diff, $item['product_name'], $item['size']]);
    }
    
    $stmt = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$qty, $itemId, $_SESSION['user_id']]);
    echo 'Updated';
  }
  elseif ($action === 'update_size' && $itemId > 0) {
    $stmt = $pdo->prepare('UPDATE cart_items SET size = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$size, $itemId, $_SESSION['user_id']]);
    echo 'Size updated';
  } 
  elseif ($action === 'delete' && $itemId > 0) {
    //Get item details before deleting to restore inventory
    $cart_item = $pdo->prepare('SELECT product_name, size, qty FROM cart_items WHERE id = ? AND user_id = ?');
    $cart_item->execute([$itemId, $_SESSION['user_id']]);
    $item = $cart_item->fetch();
    
    if ($item) {
      //Restore inventory
      $pdo->prepare('UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE product_name = ? AND size = ?')
          ->execute([$item['qty'], $item['product_name'], $item['size']]);
    }
    
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    echo 'Deleted';
  }
  elseif ($action === 'clear') {
    //Restore all inventory before clearing cart
    $items = $pdo->prepare('SELECT product_name, size, qty FROM cart_items WHERE user_id = ?');
    $items->execute([$_SESSION['user_id']]);
    
    while ($item = $items->fetch()) {
      $pdo->prepare('UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE product_name = ? AND size = ?')
          ->execute([$item['qty'], $item['product_name'], $item['size']]);
    }
    
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    echo 'Cleared';
  }
  elseif ($action === 'purchase') {
    //Complete purchase - save order and clear cart WITHOUT restoring inventory
    $name = $data['customer_name'] ?? '';
    $email = $data['customer_email'] ?? '';
    $address = $data['customer_address'] ?? '';
    $total = (float)($data['total_amount'] ?? 0);
    
    //Get cart items before clearing
    $cart_items = $pdo->prepare('SELECT product_name, unit_price, size, qty, (unit_price*qty) as line_total FROM cart_items WHERE user_id = ?');
    $cart_items->execute([$_SESSION['user_id']]);
    $items = $cart_items->fetchAll();
    
    if (!empty($items) && $name && $email && $address) {
      //Create order record
      $order_stmt = $pdo->prepare('INSERT INTO orders (user_id, customer_name, customer_email, customer_address, total_amount) VALUES (?, ?, ?, ?, ?)');
      $order_stmt->execute([$_SESSION['user_id'], $name, $email, $address, $total]);
      $order_id = $pdo->lastInsertId();
      
      //Save order items
      $item_stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_name, unit_price, size, qty, line_total) VALUES (?, ?, ?, ?, ?, ?)');
      foreach ($items as $item) {
        $item_stmt->execute([$order_id, $item['product_name'], $item['unit_price'], $item['size'], $item['qty'], $item['line_total']]);
      }
      
      //Order completed successfully
    }
    
    //Clear cart when order is submitted
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    echo 'Purchase completed';
  }
  else {
    http_response_code(400);
    echo 'Invalid action';
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Error';
}