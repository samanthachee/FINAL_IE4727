<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login-react.php');
  exit;
}
// Redirect to React version for better UX
header('Location: catalogue-react.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Catalogue</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Your New Obsession Awaits 💅</h1>
    <div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div>

  </header>
  <nav>
    <a href="index.php">Home</a> |
    <div class="dropdown">
      <a href="catalogue.php" class="dropbtn">Catalogue</a>
      <div class="dropdown-content">
        <a href="#tops" onclick="showSection('tops')">Tops</a>
        <a href="#bottoms" onclick="showSection('bottoms')">Bottoms</a>
      </div>
    </div> |
    <a href="cart.php">Cart</a> |
    <?php if (empty($_SESSION['user_id'])): ?>
      <a href="login.php">Login</a> | <a href="register.php">Register</a>
    <?php else: ?>
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </nav>

  <div class="container">
    <div id="tops" class="category-section">
      <div class="catalog-item">
        <div class="item-info">Elegant Blouse</div>
        <div class="item-pic"><img src="elegant_blouse.png" alt="Elegant Blouse"></div>
        <div class="item-price-qty">
          <div class="price-box">$45</div>
          <div class="size-box">
            <select style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;" onchange="checkStock('Elegant Blouse', this.value, this)">
              <option value="XS">XS</option>
              <option value="S">S</option>
              <option value="M" selected>M</option>
              <option value="L">L</option>
              <option value="XL">XL</option>
            </select>
          </div>
          <div class="qty-box">
            <input type="number" value="1" min="1" style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;">
          </div>
          <button class="add-to-cart" onclick="addToCart('Elegant Blouse',45,event)">Add</button>
        </div>
      </div>
      <div class="catalog-item">
        <div class="item-info">Casual T-Shirt</div>
        <div class="item-pic"><img src="casual_tshirt.png" alt="Casual T-Shirt"></div>
        <div class="item-price-qty">
          <div class="price-box">$25</div>
          <div class="size-box">
            <select style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;" onchange="checkStock('Casual T-Shirt', this.value, this)">
              <option value="XS">XS</option>
              <option value="S">S</option>
              <option value="M" selected>M</option>
              <option value="L">L</option>
              <option value="XL">XL</option>
            </select>
          </div>
          <div class="qty-box">
            <input type="number" value="1" min="1" style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;">
          </div>
          <button class="add-to-cart" onclick="addToCart('Casual T-Shirt',25,event)">Add</button>
        </div>
      </div>
    </div>

    <div id="bottoms" class="category-section" style="margin-top:40px;">
      <div class="catalog-item">
        <div class="item-info">Designer Jeans</div>
        <div class="item-pic"><img src="designer_jeans.png" alt="Designer Jeans"></div>
        <div class="item-price-qty">
          <div class="price-box">$80</div>
          <div class="size-box">
            <select style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;" onchange="checkStock('Designer Jeans', this.value, this)">
              <option value="XS">XS</option>
              <option value="S">S</option>
              <option value="M" selected>M</option>
              <option value="L">L</option>
              <option value="XL">XL</option>
            </select>
          </div>
          <div class="qty-box">
            <input type="number" value="1" min="1" style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;">
          </div>
          <button class="add-to-cart" onclick="addToCart('Designer Jeans',80,event)">Add</button>
        </div>
      </div>
      <div class="catalog-item">
        <div class="item-info">Summer Shorts</div>
        <div class="item-pic"><img src="summer_shorts.png" alt="Summer Shorts"></div>
        <div class="item-price-qty">
          <div class="price-box">$35</div>
          <div class="size-box">
            <select style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;" onchange="checkStock('Summer Shorts', this.value, this)">
              <option value="XS">XS</option>
              <option value="S">S</option>
              <option value="M" selected>M</option>
              <option value="L">L</option>
              <option value="XL">XL</option>
            </select>
          </div>
          <div class="qty-box">
            <input type="number" value="1" min="1" style="width:60px;text-align:center;border:none;background:transparent;font-weight:bold;">
          </div>
          <button class="add-to-cart" onclick="addToCart('Summer Shorts',35,event)">Add</button>
        </div>
      </div>
    </div>
  </div>

  <footer><i>💕 Pretty. Playful. Perfectly you</i><br>Let's Chat 💌<br>Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br><small>© 2025 LaureTha. All rights reserved</small></footer>

  <script>
    let cart = []; let totalPrice = 0;

    function getSiblingQty(btnEl){
      const qtyBox = btnEl.closest('.item-price-qty')?.querySelector('input[type="number"]');
      const q = parseInt(qtyBox?.value || '1', 10);
      return isNaN(q) || q < 1 ? 1 : q;
    }

    function getSiblingSize(btnEl){
      const sizeBox = btnEl.closest('.item-price-qty')?.querySelector('select');
      return sizeBox?.value || 'M';
    }

    async function checkStock(product, size, selectEl) {
      try {
        const r = await fetch(`check_inventory.php?product=${encodeURIComponent(product)}&size=${size}`);
        const data = await r.json();
        const btn = selectEl.closest('.catalog-item').querySelector('.add-to-cart');
        if (!data.available) {
          selectEl.style.color = '#999';
          btn.disabled = true;
          btn.textContent = 'Out of Stock';
        } else {
          selectEl.style.color = '';
          btn.disabled = false;
          btn.textContent = 'Add';
        }
      } catch(e) { console.warn('Stock check failed:', e); }
    }

    async function addToCart(itemName, price, evt){
      const qty = evt ? getSiblingQty(evt.currentTarget) : 1;
      const size = evt ? getSiblingSize(evt.currentTarget) : 'M';
      
      try {
        const fd = new FormData();
        fd.append('product_name', itemName);
        fd.append('unit_price', price);
        fd.append('size', size);
        fd.append('qty', qty);
        const r = await fetch('save_item.php', { method:'POST', body: fd });
        const result = await r.text();
        
        if (r.ok && result === 'OK') {
          for (let i=0;i<qty;i++){ cart.push({name:itemName, price, size}); totalPrice += price; }
          alert(itemName + ' (Size: ' + size + ') added to cart!');
        } else {
          alert('Error: ' + result);
        }
        checkStock(itemName, size, evt.currentTarget.closest('.catalog-item').querySelector('select'));
      } catch(e) { 
        console.warn('Add to cart failed:', e);
        alert('Failed to add item to cart');
      }
    }
    
    function showSection(sectionId) {
      document.getElementById('tops').style.display = sectionId === 'tops' ? 'block' : 'none';
      document.getElementById('bottoms').style.display = sectionId === 'bottoms' ? 'block' : 'none';
    }
    
    // Refresh stock status for all items
    function refreshAllStock() {
      document.querySelectorAll('select').forEach(sel => {
        const item = sel.closest('.catalog-item').querySelector('.item-info').textContent;
        checkStock(item, sel.value, sel);
      });
    }
    
    // Show all sections by default and check stock
    document.addEventListener('DOMContentLoaded', function() {
      if (window.location.hash === '#tops') showSection('tops');
      else if (window.location.hash === '#bottoms') showSection('bottoms');
      else { showSection('tops'); showSection('bottoms'); }
      
      // Check initial stock for all items
      refreshAllStock();
      
      // Auto-refresh stock every 10 seconds
      setInterval(refreshAllStock, 10000);
    });
    
    // Refresh stock when page becomes visible again
    document.addEventListener('visibilitychange', function() {
      if (!document.hidden) {
        refreshAllStock();
      }
    });
  </script>
</body>
</html>
