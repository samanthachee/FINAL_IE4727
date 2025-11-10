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
  <title>Cart - React Version</title>
  <link rel="stylesheet" href="style.css">
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <div id="react-cart"></div>

  <script type="text/babel">
    const { useState, useEffect } = React;

    function CartItem({ item, onUpdate, onDelete }) {
      const [quantity, setQuantity] = useState(item.qty);
      const [size, setSize] = useState(item.size);

      const handleQuantityChange = async (newQty) => {
        if (newQty < 1) return;
        setQuantity(newQty);
        await onUpdate(item.id, newQty, size);
      };

      const handleSizeChange = async (newSize) => {
        setSize(newSize);
        await onUpdate(item.id, quantity, newSize);
      };

      return (
        <tr>
          <td>{item.product_name}</td>
          <td>${Number(item.unit_price).toFixed(2)}</td>
          <td>
            <select 
              value={size} 
              onChange={(e) => handleSizeChange(e.target.value)}
              style={{width:'60px', textAlign:'center'}}
            >
              {['XS', 'S', 'M', 'L', 'XL'].map(s => (
                <option key={s} value={s}>{s}</option>
              ))}
            </select>
          </td>
          <td>
            <input 
              type="number" 
              value={quantity} 
              onChange={(e) => handleQuantityChange(parseInt(e.target.value) || 1)}
              min="1" 
              style={{width:'60px', textAlign:'center'}}
            />
          </td>
          <td>${Number(item.line_total).toFixed(2)}</td>
          <td>
            <button 
              className="btn" 
              onClick={() => onDelete(item.id)}
              style={{padding:'5px 10px', fontSize:'0.9em', background:'#dc2626', color:'white'}}
            >
              Delete
            </button>
          </td>
        </tr>
      );
    }

    function CheckoutForm({ total, onSubmit }) {
      const [formData, setFormData] = useState({
        name: '',
        email: '',
        address: '',
        payment: 'Credit Card'
      });
      const [errors, setErrors] = useState({});
      const [isSubmitting, setIsSubmitting] = useState(false);

      const handleInputChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        
        // Real-time validation
        const newErrors = { ...errors };
        
        if (field === 'name') {
          if (!value.trim()) {
            newErrors.name = 'Name is required';
          } else if (!/^[a-zA-Z\s]+$/.test(value.trim())) {
            newErrors.name = 'Name can only contain letters and spaces';
          } else if (value.trim().length < 2) {
            newErrors.name = 'Name must be at least 2 characters long';
          } else {
            delete newErrors.name;
          }
        }
        
        if (field === 'email') {
          if (!value.trim()) {
            newErrors.email = 'Email is required';
          } else if (!/^[\w\.-]+@[\w\.-]+$/.test(value)) {
            newErrors.email = 'Please enter a valid email address';
          } else {
            const domain = value.split('@')[1];
            if (domain) {
              const parts = domain.split('.');
              if (parts.length < 2 || parts.length > 4) {
                newErrors.email = 'Domain must have 2-4 parts';
              } else {
                const tld = parts[parts.length - 1].toLowerCase();
                if (tld !== 'sg' && tld !== 'com') {
                  newErrors.email = 'Email must end with .sg or .com';
                } else {
                  delete newErrors.email;
                }
              }
            } else {
              newErrors.email = 'Please enter a valid email address';
            }
          }
        }
        
        if (field === 'address') {
          if (!value.trim()) {
            newErrors.address = 'Address is required';
          } else if (value.trim().length < 10) {
            newErrors.address = 'Please enter a complete address (at least 10 characters)';
          } else if (!/[a-zA-Z]/.test(value) || !/[0-9]/.test(value)) {
            newErrors.address = 'Address must contain both letters and numbers';
          } else {
            delete newErrors.address;
          }
        }
        
        setErrors(newErrors);
      };

      const validateForm = () => {
        const newErrors = {};
        
        // Name validation
        if (!formData.name.trim()) {
          newErrors.name = 'Name is required';
        } else if (!/^[a-zA-Z\s]+$/.test(formData.name.trim())) {
          newErrors.name = 'Name can only contain letters and spaces';
        } else if (formData.name.trim().length < 2) {
          newErrors.name = 'Name must be at least 2 characters long';
        }
        
        // Email validation
        if (!formData.email.trim()) {
          newErrors.email = 'Email is required';
        } else if (!/^[\w\.-]+@[\w\.-]+$/.test(formData.email)) {
          newErrors.email = 'Please enter a valid email address';
        } else {
          const domain = formData.email.split('@')[1];
          if (domain) {
            const parts = domain.split('.');
            if (parts.length < 2 || parts.length > 4) {
              newErrors.email = 'Domain must have 2-4 parts';
            } else {
              const tld = parts[parts.length - 1].toLowerCase();
              if (tld !== 'sg' && tld !== 'com') {
                newErrors.email = 'Email must end with .sg or .com';
              }
            }
          } else {
            newErrors.email = 'Please enter a valid email address';
          }
        }
        
        // Address validation
        if (!formData.address.trim()) {
          newErrors.address = 'Address is required';
        } else if (formData.address.trim().length < 10) {
          newErrors.address = 'Please enter a complete address (at least 10 characters)';
        } else if (!/[a-zA-Z]/.test(formData.address) || !/[0-9]/.test(formData.address)) {
          newErrors.address = 'Address must contain both letters and numbers';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
      };

      const handleSubmit = (e) => {
        e.preventDefault();
        
        if (Object.keys(errors).length > 0) {
          return; // Don't submit if there are errors
        }
        
        if (validateForm()) {
          setIsSubmitting(true);
          onSubmit(formData);
        }
      };

      return (
        <div className="form-section" style={{background:'#fce5ff', padding:'40px', borderRadius:'15px', maxWidth:'600px', margin:'0 auto'}}>
          <h2 style={{textAlign:'center'}}>Payment and Shipping</h2>
          <form onSubmit={handleSubmit} className="form">
            <div className="form-group">
              <label>Name:</label>
              <input 
                type="text" 
                value={formData.name}
                onChange={(e) => handleInputChange('name', e.target.value)}
                placeholder="Enter your name"
                style={{
                  borderColor: errors.name ? '#dc2626' : '#e5c5ff',
                  borderWidth: '2px'
                }}
              />
              {errors.name && (
                <span style={{
                  color: '#dc2626', 
                  fontSize: '0.9em', 
                  display: 'block', 
                  marginTop: '4px',
                  fontWeight: 'bold'
                }}>
                  ⚠️ {errors.name}
                </span>
              )}
            </div>
            
            <div className="form-group">
              <label>Email:</label>
              <input 
                type="email" 
                value={formData.email}
                onChange={(e) => handleInputChange('email', e.target.value)}
                placeholder="Enter your email"
                style={{
                  borderColor: errors.email ? '#dc2626' : '#e5c5ff',
                  borderWidth: '2px'
                }}
              />
              {errors.email && (
                <span style={{
                  color: '#dc2626', 
                  fontSize: '0.9em', 
                  display: 'block', 
                  marginTop: '4px',
                  fontWeight: 'bold'
                }}>
                  ⚠️ {errors.email}
                </span>
              )}
            </div>
            
            <div className="form-group">
              <label>Address:</label>
              <input 
                type="text" 
                value={formData.address}
                onChange={(e) => handleInputChange('address', e.target.value)}
                placeholder="Enter your address"
                style={{
                  borderColor: errors.address ? '#dc2626' : '#e5c5ff',
                  borderWidth: '2px'
                }}
              />
              {errors.address && (
                <span style={{
                  color: '#dc2626', 
                  fontSize: '0.9em', 
                  display: 'block', 
                  marginTop: '4px',
                  fontWeight: 'bold'
                }}>
                  ⚠️ {errors.address}
                </span>
              )}
            </div>
            
            <div className="form-group">
              <label>Price: (auto)</label>
              <input 
                type="text" 
                value={`$${total.toFixed(2)}`}
                readOnly 
                style={{background:'#f5d5ff', fontWeight:'bold'}}
              />
            </div>
            
            <div className="form-group">
              <label>Payment method:</label>
              <select 
                value={formData.payment}
                onChange={(e) => setFormData({...formData, payment: e.target.value})}
              >
                <option>Credit Card</option>
                <option>PayPal</option>
                <option>Bank Transfer</option>
              </select>
            </div>
            
            <button 
              className="btn" 
              type="submit"
              disabled={isSubmitting || Object.keys(errors).length > 0 || !formData.name || !formData.email || !formData.address}
              style={{
                opacity: (isSubmitting || Object.keys(errors).length > 0 || !formData.name || !formData.email || !formData.address) ? 0.6 : 1,
                cursor: (isSubmitting || Object.keys(errors).length > 0 || !formData.name || !formData.email || !formData.address) ? 'not-allowed' : 'pointer',
                backgroundColor: (isSubmitting || Object.keys(errors).length > 0 || !formData.name || !formData.email || !formData.address) ? '#ccc' : ''
              }}
            >
              {isSubmitting ? 'Processing Order...' : 'Submit Order'}
            </button>
            
            {Object.keys(errors).length > 0 && (
              <div style={{
                background: '#ffe4e6',
                border: '2px solid #fecaca',
                borderRadius: '5px',
                padding: '10px',
                marginTop: '10px',
                color: '#dc2626'
              }}>
                <strong>⚠️ Please fix the errors above before submitting</strong>
              </div>
            )}
          </form>
        </div>
      );
    }

    function CartPage() {
      const [cartItems, setCartItems] = useState([]);
      const [total, setTotal] = useState(0);
      const [loading, setLoading] = useState(true);

      useEffect(() => {
        loadCart();
      }, []);

      const loadCart = async () => {
        try {
          const response = await fetch('load_cart.php', { headers: {'Accept':'application/json'} });
          if (response.ok) {
            const data = await response.json();
            setCartItems(data.items || []);
            setTotal(data.total || 0);
          }
          setLoading(false);
        } catch (error) {
          console.error('Failed to load cart:', error);
          setLoading(false);
        }
      };

      const updateItem = async (itemId, qty, size) => {
        try {
          const formData = new FormData();
          formData.append('action', 'update');
          formData.append('item_id', itemId);
          formData.append('qty', qty);
          
          const response = await fetch('update_cart.php', { method: 'POST', body: formData });
          const result = await response.text();
          
          if (response.ok) {
            loadCart();
          } else {
            alert(result);
            loadCart();
          }
        } catch (error) {
          alert('Failed to update item');
          loadCart();
        }
      };

      const deleteItem = async (itemId) => {
        if (!confirm('Remove this item from cart?')) return;
        
        try {
          const formData = new FormData();
          formData.append('action', 'delete');
          formData.append('item_id', itemId);
          
          await fetch('update_cart.php', { method: 'POST', body: formData });
          loadCart();
        } catch (error) {
          alert('Failed to delete item');
        }
      };

      const clearCart = async () => {
        if (!confirm('Clear entire cart?')) return;
        
        try {
          const formData = new FormData();
          formData.append('action', 'clear');
          
          await fetch('update_cart.php', { method: 'POST', body: formData });
          loadCart();
        } catch (error) {
          alert('Failed to clear cart');
        }
      };

      const submitOrder = async (orderData) => {
        try {
          // Store cart data for confirmation page
          const orderSummary = {
            items: cartItems.map(item => ({
              ...item,
              image: getProductImage(item.product_name)
            })),
            total: total,
            customer: orderData
          };
          sessionStorage.setItem('orderData', JSON.stringify(orderSummary));
          
          const formData = new FormData();
          formData.append('action', 'purchase');
          formData.append('customer_name', orderData.name);
          formData.append('customer_email', orderData.email);
          formData.append('customer_address', orderData.address);
          formData.append('total_amount', total);
          
          await fetch('update_cart.php', { method: 'POST', body: formData });
          
          const params = new URLSearchParams({
            name: orderData.name,
            email: orderData.email,
            address: orderData.address,
            price: `$${total.toFixed(2)}`
          });
          
          window.location.href = `order_confirmation.php?${params.toString()}`;
        } catch (error) {
          alert('Failed to complete order');
        }
      };
      
      const getProductImage = (productName) => {
        const imageMap = {
          'Elegant Blouse': 'elegant_blouse.png',
          'Casual T-Shirt': 'casual_tshirt.png',
          'Designer Jeans': 'designer_jeans.png',
          'Summer Shorts': 'summer_shorts.png'
        };
        return imageMap[productName] || 'placeholder.png';
      };

      if (loading) {
        return <div>Loading cart...</div>;
      }

      return (
        <div>
          <header>
            <h1>Got Everything You Need?</h1>
            <div className="logo"><img src="LaureTha.png" alt="LaureTha Logo" /></div>
          </header>
          
          <nav>
            <a href="index-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Home</a> |
            <a href="catalogue-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Catalogue</a> |
            <a href="cart-react.php" style={{color:'#9966cc', fontSize:'1.3em', fontWeight:'600', margin:'0 20px', textDecoration:'none'}}>Cart</a> |
            <a href="logout.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Logout</a>
          </nav>

          <div className="container">
            {cartItems.length > 0 ? (
              <div className="cart-box">
                <h3>Your Cart <span className="badge">React Version</span></h3>
                <div style={{marginBottom:'15px'}}>
                  <button className="btn" onClick={clearCart} style={{background:'#dc2626', color:'white'}}>
                    Clear Cart
                  </button>
                </div>
                
                <table className="table">
                  <thead>
                    <tr><th>Item</th><th>Price</th><th>Size</th><th>Qty</th><th>Total</th><th>Action</th></tr>
                  </thead>
                  <tbody>
                    {cartItems.map(item => (
                      <CartItem 
                        key={item.id} 
                        item={item} 
                        onUpdate={updateItem}
                        onDelete={deleteItem}
                      />
                    ))}
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colSpan="5" style={{textAlign:'right'}}><strong>Grand Total</strong></td>
                      <td><strong>${total.toFixed(2)}</strong></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            ) : (
              <div className="cart-box">
                <h3>Your cart is empty</h3>
                <p><a href="catalogue-react.php">Start shopping!</a></p>
              </div>
            )}

            <CheckoutForm total={total} onSubmit={submitOrder} />
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

    ReactDOM.render(<CartPage />, document.getElementById('react-cart'));
  </script>
</body>
</html>