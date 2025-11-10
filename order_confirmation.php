<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';
$address = $_GET['address'] ?? '';
$price = $_GET['price'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Order Confirmed! 🎉</h1>
    <div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div>
  </header>
  <nav>
    <a href="index-react.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Home</a> |
    <a href="catalogue-react.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Catalogue</a> |
    <a href="cart-react.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Cart</a> |
    <a href="logout.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Logout</a>
  </nav>

  <div class="container">
    <div class="confirmation-box">
      <h2>Your Order Summary</h2>
      <div id="order-items" class="cart-box"></div>
      
      <div style="background:#f5d5ff;padding:30px;border-radius:15px;margin:30px 0;text-align:center;">
        <h3>Payment Successful! 💳</h3>
        <p style="font-size:1.2em;line-height:1.6;">
          Your payment of <strong><?= htmlspecialchars($price) ?></strong> is successful!<br>
          Thank you <strong><?= htmlspecialchars($name) ?></strong>! A copy will be emailed to <strong><?= htmlspecialchars($email) ?></strong>.<br>
          Items will arrive at <strong><?= htmlspecialchars($address) ?></strong> in ~5 days.
        </p>
        <button class="btn" onclick="window.location.href='catalogue-react.php'" style="margin-top:20px;padding:15px 30px;font-size:1.2em;">Continue Shopping</button>
      </div>
    </div>
  </div>

  <footer><i>💕 Pretty. Playful. Perfectly you</i><br>Let's Chat 💌<br>Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br><small>© 2025 LaureTha. All rights reserved</small></footer>

  <script>
    function loadOrderItems() {
      try {
        const orderData = sessionStorage.getItem('orderData');
        if (!orderData) return;
        
        const data = JSON.parse(orderData);
        if (!data.items || data.items.length === 0) return;
        
        const itemsHtml = data.items.map(i => `
          <div style="display:flex;align-items:center;gap:20px;padding:20px;background:#fff;border-radius:15px;margin:15px 0;box-shadow:0 2px 10px rgba(0,0,0,0.1);border:2px solid #e5c5ff;">
            <div style="flex-shrink:0;">
              <img src="${i.image || getProductImage(i.product_name)}" alt="${i.product_name}" style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:2px solid #f5d5ff;">
            </div>
            <div style="flex:1;">
              <h4 style="margin:0 0 10px 0;color:#9966cc;font-size:1.2em;">${i.product_name}</h4>
              <p style="margin:5px 0;color:#666;"><strong>Size:</strong> ${i.size}</p>
              <p style="margin:5px 0;color:#666;"><strong>Quantity:</strong> ${i.qty}</p>
              <p style="margin:5px 0;color:#666;"><strong>Unit Price:</strong> $${Number(i.unit_price).toFixed(2)}</p>
            </div>
            <div style="text-align:right;">
              <div style="font-weight:bold;font-size:1.3em;color:#9966cc;">$${Number(i.line_total).toFixed(2)}</div>
            </div>
          </div>
        `).join('');
        
        document.getElementById('order-items').innerHTML = `
          <h3>Items Ordered:</h3>
          ${itemsHtml}
          <div style="text-align:right;padding:15px;background:#f5d5ff;border-radius:10px;margin-top:15px;">
            <strong style="font-size:1.3em;">Total: $${Number(data.total).toFixed(2)}</strong>
          </div>
        `;
        
        // Clear the stored data after use
        sessionStorage.removeItem('orderData');
      } catch(e) {
        console.warn('Failed to load order items:', e);
      }
    }
    
    function getProductImage(productName) {
      const imageMap = {
        'Elegant Blouse': 'elegant_blouse.png',
        'Casual T-Shirt': 'casual_tshirt.png',
        'Designer Jeans': 'designer_jeans.png',
        'Summer Shorts': 'summer_shorts.png'
      };
      return imageMap[productName] || 'placeholder.png';
    }
    
    document.addEventListener('DOMContentLoaded', loadOrderItems);
  </script>
</body>
</html>