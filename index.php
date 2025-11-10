<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login-react.php');
  exit;
}
// Redirect to React version for better UX
header('Location: index-react.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div id="home" class="page active">
    <header>
          <h1>Hey Gorgeous, Welcome to LaureTha 💋</h1>
          <h2>Discover the Magic Below! ✨</h2>
          <div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div>
    </header>
    <nav>
      <a href="index.php">Home</a> |
      <div class="dropdown">
        <a href="catalogue.php" class="dropbtn">Catalogue</a>
        <div class="dropdown-content">
          <a href="catalogue.php#tops">Tops</a>
          <a href="catalogue.php#bottoms">Bottoms</a>
        </div>
      </div> |
      <a href="cart.php">Shopping Cart
        <span id="cart-count" class="cart-badge" style="display:none;">0</span>
      </a> |
      <?php if (empty($_SESSION['user_id'])): ?>
        <a href="login.php">Login</a> | <a href="register.php">Register</a>
      <?php else: ?>
        <a href="logout.php">Logout</a>
      <?php endif; ?>
    </nav>
    <div class="container">
      <div class="promo-section">
        <h2>• New Arrivals</h2>
        <h2>• Promotions</h2>
      </div>
      <div class="product-grid">
        <div class="product-card">
          <div class="product-image"><img src="elegant_blouse.png" alt="Elegant Blouse"></div>
          <p style="font-size: 1.2em;">New Collection Item</p>
        </div>
        <div class="product-card">
          <div class="product-image"><img src="summer_shorts.png" alt="Summer Shorts"></div>
          <p style="font-size: 1.2em;">Summer Sale</p>
        </div>
        <div class="product-card">
          <div class="product-image"><img src="designer_jeans.png" alt="Designer Jeans"></div>
          <p style="font-size: 1.2em;">Featured Product</p>
        </div>
      </div>
    </div>
    <footer><i>💕 Pretty. Playful. Perfectly you</i><br>Let's Chat 💌<br>Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br><small>© 2025 LaureTha. All rights reserved</small></footer>
  </div>

  <script>
    // simple in-memory badge (optional)
    let cart = [];
    function updateCartCount() {
      const badge = document.getElementById('cart-count');
      if (badge) {
        if (cart.length > 0) { badge.textContent = cart.length; badge.style.display = 'inline'; }
        else { badge.style.display = 'none'; }
      }
    }
  </script>
</body>
</html>
