<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Catalogue - React Version</title>
  <link rel="stylesheet" href="style.css">
  <!-- React CDN -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <header>
    <h1>Your New Obsession Awaits 💅</h1>
    <div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div>
  </header>
  <nav>
    <a href="index-react.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Home</a> |
    <a href="catalogue-react.php" style="color:#9966cc;font-size:1.3em;font-weight:600;margin:0 20px;text-decoration:none;">Catalogue</a> |
    <a href="cart-react.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Cart</a> |
    <a href="logout.php" style="font-size:1.3em;font-weight:600;margin:0 20px;color:#1f1f1f;text-decoration:none;">Logout</a>
  </nav>

  <div id="react-catalogue"></div>

  <footer><i>💕 Pretty. Playful. Perfectly you</i><br>Let's Chat 💌<br>Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br><small>© 2025 LaureTha. All rights reserved</small></footer>

  <script type="text/babel">
    const { useState, useEffect } = React;

    // Product data
    const products = [
      { id: 1, name: 'Elegant Blouse', price: 45, image: 'elegant_blouse.png', category: 'tops' },
      { id: 2, name: 'Casual T-Shirt', price: 25, image: 'casual_tshirt.png', category: 'tops' },
      { id: 3, name: 'Designer Jeans', price: 80, image: 'designer_jeans.png', category: 'bottoms' },
      { id: 4, name: 'Summer Shorts', price: 35, image: 'summer_shorts.png', category: 'bottoms' }
    ];

    function ProductCard({ product }) {
      const [selectedSize, setSelectedSize] = useState('M');
      const [quantity, setQuantity] = useState(1);
      const [inventory, setInventory] = useState({});
      const [loading, setLoading] = useState(true);

      useEffect(() => {
        checkStock();
        const interval = setInterval(checkStock, 10000);
        return () => clearInterval(interval);
      }, [selectedSize]);

      const checkStock = async () => {
        try {
          const response = await fetch(`check_inventory.php?product=${encodeURIComponent(product.name)}&size=${selectedSize}`);
          const data = await response.json();
          setInventory(prev => ({ ...prev, [selectedSize]: data.stock }));
          setLoading(false);
        } catch (error) {
          console.error('Stock check failed:', error);
          setLoading(false);
        }
      };

      const handleAddToCart = async () => {
        if (inventory[selectedSize] <= 0) return;

        try {
          const formData = new FormData();
          formData.append('product_name', product.name);
          formData.append('unit_price', product.price);
          formData.append('size', selectedSize);
          formData.append('qty', quantity);

          const response = await fetch('save_item.php', { method: 'POST', body: formData });
          const result = await response.text();

          if (response.ok && result.trim() === 'OK') {
            alert(`${product.name} (Size: ${selectedSize}) added to cart!`);
            checkStock();
          } else {
            alert('Error: ' + result);
          }
        } catch (error) {
          alert('Failed to add item to cart');
        }
      };

      const isInStock = inventory[selectedSize] > 0;

      return (
        <div className="catalog-item">
          <div className="item-info">{product.name}</div>
          <div className="item-pic">
            <img src={product.image} alt={product.name} />
          </div>
          <div className="item-price-qty">
            <div className="price-box">${product.price}</div>
            <div className="size-box">
              <select 
                value={selectedSize} 
                onChange={(e) => setSelectedSize(e.target.value)}
                style={{
                  width: '60px',
                  textAlign: 'center',
                  border: 'none',
                  background: 'transparent',
                  fontWeight: 'bold',
                  color: isInStock ? '' : '#999'
                }}
              >
                {['XS', 'S', 'M', 'L', 'XL'].map(size => (
                  <option key={size} value={size}>{size}</option>
                ))}
              </select>
            </div>
            <div className="qty-box">
              <input 
                type="number" 
                value={quantity} 
                onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                min="1" 
                max={inventory[selectedSize] || 0}
                style={{
                  width: '60px',
                  textAlign: 'center',
                  border: 'none',
                  background: 'transparent',
                  fontWeight: 'bold'
                }}
              />
            </div>
            <button 
              className="add-to-cart" 
              onClick={handleAddToCart}
              disabled={!isInStock || loading}
              style={{
                backgroundColor: !isInStock ? '#ccc' : '',
                cursor: !isInStock ? 'not-allowed' : 'pointer'
              }}
            >
              {loading ? 'Loading...' : (isInStock ? 'Add' : 'Out of Stock')}
            </button>
          </div>
        </div>
      );
    }

    function Catalogue() {
      const [activeSection, setActiveSection] = useState('all');

      const filteredProducts = products.filter(product => {
        if (activeSection === 'all') return true;
        return product.category === activeSection;
      });

      return (
        <div className="container">
          <div style={{ marginBottom: '20px', textAlign: 'center' }}>
            <button 
              onClick={() => setActiveSection('all')}
              style={{ 
                margin: '0 10px', 
                padding: '10px 20px',
                backgroundColor: activeSection === 'all' ? '#f5d5ff' : '#fff',
                border: '2px solid #e5c5ff',
                borderRadius: '5px',
                cursor: 'pointer'
              }}
            >
              All Items
            </button>
            <button 
              onClick={() => setActiveSection('tops')}
              style={{ 
                margin: '0 10px', 
                padding: '10px 20px',
                backgroundColor: activeSection === 'tops' ? '#f5d5ff' : '#fff',
                border: '2px solid #e5c5ff',
                borderRadius: '5px',
                cursor: 'pointer'
              }}
            >
              Tops
            </button>
            <button 
              onClick={() => setActiveSection('bottoms')}
              style={{ 
                margin: '0 10px', 
                padding: '10px 20px',
                backgroundColor: activeSection === 'bottoms' ? '#f5d5ff' : '#fff',
                border: '2px solid #e5c5ff',
                borderRadius: '5px',
                cursor: 'pointer'
              }}
            >
              Bottoms
            </button>
          </div>

          <div>
            {filteredProducts.map(product => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      );
    }

    // Render the React app
    ReactDOM.render(<Catalogue />, document.getElementById('react-catalogue'));
  </script>
</body>
</html>