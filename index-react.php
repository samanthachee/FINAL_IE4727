<?php 
require __DIR__ . '/db.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login-react.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - React Version</title>
  <link rel="stylesheet" href="style.css">
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <div id="react-app"></div>

  <script type="text/babel">
    const { useState, useEffect } = React;

    function Navigation() {
      return (
        <nav>
          <a href="index-react.php" style={{color:'#9966cc', fontSize:'1.3em', fontWeight:'600', margin:'0 20px', textDecoration:'none'}}>Home</a> |
          <div className="dropdown">
            <a href="catalogue-react.php" className="dropbtn" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Catalogue</a>
            <div className="dropdown-content">
              <a href="catalogue-react.php#tops">Tops</a>
              <a href="catalogue-react.php#bottoms">Bottoms</a>
            </div>
          </div> |
          <a href="cart-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Cart</a> |
          <a href="logout.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Logout</a>
        </nav>
      );
    }
    //first image on web, can click and redirect to catalogue
    function ProductCard({ product, label }) {
      const [isHovered, setIsHovered] = useState(false);

      return (
        <div 
          className="product-card"
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
          style={{
            transform: isHovered ? 'translateY(-5px) scale(1.02)' : 'translateY(0) scale(1)',
            transition: 'all 0.3s ease',
            cursor: 'pointer'
          }}
          onClick={() => window.location.href = 'catalogue-react.php'}
        >
          <div className="product-image">
            <img src={product.image} alt={product.name} />
          </div>
          <p style={{ fontSize: '1.2em', fontWeight: isHovered ? 'bold' : 'normal' }}>
            {label}
          </p>
        </div>
      );
    }
    //promotional image
    function PromoSection() {
      const [currentPromo, setCurrentPromo] = useState(0);
      const promos = [
        "• New Arrivals - Fresh styles just in! ✨",
        "• Flash Sale - 20% off selected items! 🔥", 
        "• Free Shipping - On orders over $50! 🚚"
      ];

      useEffect(() => {
        const interval = setInterval(() => {
          setCurrentPromo(prev => (prev + 1) % promos.length);
        }, 3000);
        return () => clearInterval(interval);
      }, []);

      return (
        <div className="promo-section" style={{
          background: 'linear-gradient(135deg, #f5d5ff 0%, #e5c5ff 100%)',
          transition: 'all 0.5s ease'
        }}>
          <h2 style={{
            opacity: 1,
            transform: 'translateY(0)',
            transition: 'all 0.5s ease'
          }}>
            {promos[currentPromo]}
          </h2>
        </div>
      );
    }

    function HomePage() {
      const products = [
        { name: 'Elegant Blouse', image: 'elegant_blouse.png' },
        { name: 'Summer Shorts', image: 'summer_shorts.png' },
        { name: 'Designer Jeans', image: 'designer_jeans.png' }
      ];

      const labels = ['New Collection Item', 'Summer Sale', 'Featured Product'];

      return (
        <div>
          <header>
            <h1>Hey Gorgeous, Welcome to LaureTha 💋</h1>
            <h2>Discover the Magic Below! ✨</h2>
            <div className="logo">
              <img src="LaureTha.png" alt="LaureTha Logo" />
            </div>
          </header>
          
          <Navigation />
          
          <div className="container">
            <PromoSection />
            
            <div className="product-grid">
              {products.map((product, index) => (
                <ProductCard 
                  key={index}
                  product={product} 
                  label={labels[index]}
                />
              ))}
            </div>
          </div>

          <footer>
            <i>💕 Pretty. Playful. Perfectly you</i><br/>
            Let's Chat 💌<br/>
            Email: <a href="mailto:hello@lauretha.com">hello@lauretha.com</a><br/>
            <small>© 2025 LaureTha. All rights reserved</small>
          </footer>
        </div>
      );
    }

    ReactDOM.render(<HomePage />, document.getElementById('react-app'));
  </script>
</body>
</html>