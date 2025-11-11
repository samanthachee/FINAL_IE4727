<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login-react.php');
  exit;
}
// Redirect to React version for better UX
header('Location: cart-react.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Got Everything You Need?</h1>
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
    <a href="cart.php">Shopping Cart</a> |
    <?php if (empty($_SESSION['user_id'])): ?>
      <a href="login.php">Login</a> | <a href="register.php">Register</a>
    <?php else: ?>
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </nav>

  <div class="container">
    <!-- Saved (DB) items panel -->
    <div id="cart-items" class="cart-box" style="display:none;">
      <h3 style="margin-bottom:10px;">Your Saved Cart
        <span class="badge">from account</span>
      </h3>
      <div id="cart-table"></div>
    </div>

    <!-- Checkout form -->
    <div class="form-section" style="background:#fce5ff;padding:40px;border-radius:15px;max-width:600px;margin:0 auto;">
      <h2 style="text-align:center;">Payment and shipping</h2>
      <div class="form-group">
        <label>Name:</label>
        <input type="text" id="name" placeholder="Enter your name" oninput="validateNameField()">
        <span id="name-error" style="color: red; font-size: 0.9em;"></span>
      </div>
      <div class="form-group">
        <label>Email:</label>
        <input type="email" id="email" placeholder="Enter your email" oninput="validateEmailField()">
        <span id="email-error" style="color: red; font-size: 0.9em;"></span>
      </div>
      <div class="form-group">
        <label>Address:</label>
        <input type="text" id="address" placeholder="Enter your address" oninput="validateAddressField()">
        <span id="address-error" style="color: red; font-size: 0.9em;"></span>
      </div>
      <div class="form-group">
        <label>Price: (auto)</label>
        <input type="text" id="price" readonly value="$0" style="background:#f5d5ff;font-weight:bold;">
      </div>
      <div class="form-group">
        <label>Payment method:</label>
        <select id="payment">
          <option>Credit Card</option>
          <option>PayPal</option>
          <option>Bank Transfer</option>
        </select>
      </div>
      <button class="btn" onclick="submitOrder()">Submit</button>
    </div>
  </div>

  <!-- Debug info -->
  <?php if (isset($_GET['debug'])): ?>
    <div style="background:#f0f0f0;padding:20px;margin:20px;border:1px solid #ccc;">
      <h3>Debug Info</h3>
      <p><strong>User ID:</strong> <?= $_SESSION['user_id'] ?? 'Not logged in' ?></p>
      <?php
      try {
        $pdo = db();
        echo "<h4>Cart Items:</h4>";
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        $items = $stmt->fetchAll();
        if (empty($items)) {
          echo "No items in cart<br>";
        } else {
          foreach ($items as $item) {
            echo $item['product_name'] . " (" . $item['size'] . ") - Qty: " . $item['qty'] . "<br>";
          }
        }
        
        echo "<h4>Inventory:</h4>";
        $stmt = $pdo->query("SELECT * FROM inventory ORDER BY product_name, size");
        while ($row = $stmt->fetch()) {
          echo $row['product_name'] . " (" . $row['size'] . "): " . $row['stock_quantity'] . "<br>";
        }
      } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
      }
      ?>
    </div>
  <?php endif; ?>

  <footer><i>💕 Pretty. Playful. Perfectly you</i><br>Let's Chat 💌<br>Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br><small>© 2025 LaureTha. All rights reserved</small></footer>

  <script>
    // fallback in-memory cart (if not logged in)
    let cart = []; let totalPrice = 0;

    function updateCartPrice() {
      const priceEl = document.getElementById('price');
      if (priceEl) priceEl.value = '$' + totalPrice.toFixed(2);
    }

    async function loadSavedCart(){
      const box = document.getElementById('cart-items');
      const tableDiv = document.getElementById('cart-table');
      try {
        const r = await fetch('load_cart.php', { headers: {'Accept':'application/json'} });
        if (!r.ok) { box.style.display = 'none'; updateCartPrice(); return; } // not logged in
        const data = await r.json();
        box.style.display = 'block';
        if (!data.items || data.items.length === 0) {
          tableDiv.innerHTML = '<p>No saved items yet.</p>';
          document.getElementById('price').value = '$0.00';
          return;
        }
        const rows = data.items.map(i => `
          <tr>
            <td>${i.product_name}</td>
            <td>$${Number(i.unit_price).toFixed(2)}</td>
            <td>
              <select onchange="updateSize(${i.id}, this.value)" style="width:60px;text-align:center;">
                <option value="XS" ${i.size === 'XS' ? 'selected' : ''}>XS</option>
                <option value="S" ${i.size === 'S' ? 'selected' : ''}>S</option>
                <option value="M" ${i.size === 'M' ? 'selected' : ''}>M</option>
                <option value="L" ${i.size === 'L' ? 'selected' : ''}>L</option>
                <option value="XL" ${i.size === 'XL' ? 'selected' : ''}>XL</option>
              </select>
            </td>
            <td>
              <input type="number" value="${i.qty}" min="1" style="width:60px;text-align:center;" 
                     onchange="updateQuantity(${i.id}, this.value)">
            </td>
            <td>$${Number(i.line_total).toFixed(2)}</td>
            <td><button class="btn" style="padding:5px 10px;font-size:0.9em;" onclick="deleteItem(${i.id})">Delete</button></td>
          </tr>`).join('');
        tableDiv.innerHTML = `
          <div style="margin-bottom:15px;">
            <button class="btn" onclick="clearCart()" style="background:#dc2626;color:white;">Clear Cart</button>
          </div>
          <table class="table">
            <thead><tr><th>Item</th><th>Price</th><th>Size</th><th>Qty</th><th>Total</th><th>Action</th></tr></thead>
            <tbody>${rows}</tbody>
            <tfoot>
              <tr><td colspan="5" style="text-align:right"><strong>Grand Total</strong></td>
                  <td><strong>$${Number(data.total).toFixed(2)}</strong></td></tr>
            </tfoot>
          </table>`;
        document.getElementById('price').value = '$' + Number(data.total).toFixed(2);
      } catch(e) {
        console.warn('Load saved cart failed:', e);
        box.style.display = 'none';
        updateCartPrice();
      }
    }
    // validation checks for form fields
    function validateNameField() {
      const name = document.getElementById('name').value;
      const error = document.getElementById('name-error');
      if (name && !/^[a-zA-Z\s]+$/.test(name)) {
        error.textContent = 'Name can only contain letters and spaces';
      } else {
        error.textContent = '';
      }
    }

    function validateEmailField() {
      const email = document.getElementById('email').value;
      const error = document.getElementById('email-error');
      if (email && (!email.includes('@') || !email.includes('.'))) {
        error.textContent = 'Email must contain @ and . symbols';
      } else {
        error.textContent = '';
      }
    }

    function validateAddressField() {
      const address = document.getElementById('address').value;
      const error = document.getElementById('address-error');
      if (address && (!/[a-zA-Z]/.test(address) || !/[0-9]/.test(address))) {
        error.textContent = 'Address must contain both letters and numbers';
      } else {
        error.textContent = '';
      }
    }

    async function submitOrder(){
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const address = document.getElementById('address').value.trim();
      const price = document.getElementById('price').value;
      
      if (!name || !email || !address) {
        alert('Please fill in all fields!');
        return;
      }
      
      if (!/^[a-zA-Z\s]+$/.test(name) || !email.includes('@') || !email.includes('.') || !/[a-zA-Z]/.test(address) || !/[0-9]/.test(address)) {
        alert('Please fix the errors in the form!');
        return;
      }
      
      // Get cart data before clearing
      let cartData = null;
      try {
        const r = await fetch('load_cart.php', { headers: {'Accept':'application/json'} });
        if (r.ok) cartData = await r.json();
      } catch(e) {
        console.warn('Failed to load cart data:', e);
      }
      
      // Complete purchase and save order
      try {
        const fd = new FormData();
        fd.append('action', 'purchase');
        fd.append('customer_name', name);
        fd.append('customer_email', email);
        fd.append('customer_address', address);
        fd.append('total_amount', price.replace('$', ''));
        await fetch('update_cart.php', { method: 'POST', body: fd });
      } catch(e) {
        console.warn('Failed to complete purchase:', e);
      }
      
      // Store cart data in sessionStorage and redirect
      if (cartData) sessionStorage.setItem('orderData', JSON.stringify(cartData));
      const params = new URLSearchParams({
        name: name,
        email: email,
        address: address,
        price: price
      });
      window.location.href = `order_confirmation.php?${params.toString()}`;
    }

    async function updateQuantity(itemId, qty) {
      try {
        const fd = new FormData();
        fd.append('action', 'update');
        fd.append('item_id', itemId);
        fd.append('qty', qty);
        const r = await fetch('update_cart.php', { method: 'POST', body: fd });
        const result = await r.text();
        
        if (r.ok) {
          loadSavedCart();
        } else {
          alert(result);
          loadSavedCart(); // Reload to reset the input field
        }
      } catch(e) {
        console.error('Update failed:', e);
        alert('Failed to update quantity');
        loadSavedCart();
      }
    }

    async function updateSize(itemId, size) {
      try {
        const fd = new FormData();
        fd.append('action', 'update_size');
        fd.append('item_id', itemId);
        fd.append('size', size);
        await fetch('update_cart.php', { method: 'POST', body: fd });
        loadSavedCart();
      } catch(e) {
        console.error('Update failed:', e);
        alert('Failed to update size');
      }
    }

    async function deleteItem(itemId) {
      if (!confirm('Remove this item from cart?')) return;
      try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('item_id', itemId);
        await fetch('update_cart.php', { method: 'POST', body: fd });
        loadSavedCart();
      } catch(e) {
        console.error('Delete failed:', e);
        alert('Failed to delete item');
      }
    }

    async function clearCart() {
      if (!confirm('Clear entire cart?')) return;
      try {
        const fd = new FormData();
        fd.append('action', 'clear');
        await fetch('update_cart.php', { method: 'POST', body: fd });
        loadSavedCart();
      } catch(e) {
        console.error('Clear failed:', e);
        alert('Failed to clear cart');
      }
    }

    //On page load, try DB cart first; fallback is zero or your JS cart
    document.addEventListener('DOMContentLoaded', loadSavedCart);
  </script>
</body>
</html>
