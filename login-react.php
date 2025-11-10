<?php
require __DIR__ . '/db.php';
if (!empty($_SESSION['user_id'])) { header('Location: index-react.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - React Version</title>
  <link rel="stylesheet" href="style.css">
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <div id="react-login"></div>

  <script type="text/babel">
    const { useState } = React;

    function LoginForm() {
      const [formData, setFormData] = useState({ email: '', password: '' });
      const [errors, setErrors] = useState({});
      const [isSubmitting, setIsSubmitting] = useState(false);

      const validateEmail = (email) => {
        if (!email.trim()) return 'Email is required';
        if (!/^[\w\.-]+@[\w\.-]+$/.test(email)) return 'Please enter a valid email address';
        
        const domain = email.split('@')[1];
        if (domain) {
          const parts = domain.split('.');
          if (parts.length < 2 || parts.length > 4) return 'Domain must have 2-4 parts';
          const tld = parts[parts.length - 1].toLowerCase();
          if (tld !== 'sg' && tld !== 'com') return 'Email must end with .sg or .com';
        }
        return '';
      };

      const validatePassword = (password) => {
        if (!password) return 'Password is required';
        if (password.length < 8) return 'Password must be at least 8 characters long';
        if (!/[a-zA-Z]/.test(password)) return 'Password must contain at least 1 letter';
        if (!/[0-9]/.test(password)) return 'Password must contain at least 1 number';
        return '';
      };

      const handleInputChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        
        // Real-time validation
        const newErrors = { ...errors };
        if (field === 'email') {
          const emailError = validateEmail(value);
          if (emailError) newErrors.email = emailError;
          else delete newErrors.email;
        }
        if (field === 'password') {
          const passwordError = validatePassword(value);
          if (passwordError) newErrors.password = passwordError;
          else delete newErrors.password;
        }
        setErrors(newErrors);
      };

      const handleSubmit = async (e) => {
        const emailError = validateEmail(formData.email);
        const passwordError = validatePassword(formData.password);
        
        if (emailError || passwordError) {
          e.preventDefault();
          setErrors({ email: emailError, password: passwordError });
          return;
        }
        
        // Let the form submit normally to PHP if validation passes
        setIsSubmitting(true);
      };

      return (
        <div>
          <header>
            <h1>Login</h1>
            <div className="logo"><img src="LaureTha.png" alt="LaureTha Logo" /></div>
          </header>
          
          <nav>
            <a href="index-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Home</a> | 
            <a href="catalogue-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Catalogue</a> |
            <a href="cart-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Cart</a> | 
            <a href="register-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Register</a>
          </nav>

          <div className="auth-card">
            <h2>Login</h2>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              $email = trim($_POST['email'] ?? '');
              $pass  = $_POST['password'] ?? '';
              $stmt = db()->prepare('SELECT id,name,password_hash FROM users WHERE email=?');
              $stmt->execute([$email]);
              $u = $stmt->fetch();
              if ($u && password_verify($pass, $u['password_hash'])) {
                $_SESSION['user_id'] = $u['id']; $_SESSION['user_name'] = $u['name'];
                header('Location: index-react.php'); exit;
              } else {
                echo '<p className="err">Invalid email or password.</p>';
              }
            }
            ?>
            
            {errors.general && (
              <div style={{color: '#dc2626', padding: '10px', background: '#ffe4e6', border: '2px solid #fecaca', borderRadius: '10px', margin: '8px 0'}}>
                {errors.general}
              </div>
            )}
            
            <form onSubmit={handleSubmit} method="post" className="form">
              <label>
                Email
                <input 
                  type="email" 
                  name="email"
                  value={formData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  required
                  style={{
                    borderColor: errors.email ? '#dc2626' : '#e5c5ff'
                  }}
                />
                {errors.email && (
                  <span style={{color: '#dc2626', fontSize: '0.9em', marginTop: '4px', display: 'block'}}>
                    {errors.email}
                  </span>
                )}
              </label>
              
              <label>
                Password
                <input 
                  type="password" 
                  name="password"
                  value={formData.password}
                  onChange={(e) => handleInputChange('password', e.target.value)}
                  required
                  style={{
                    borderColor: errors.password ? '#dc2626' : '#e5c5ff'
                  }}
                />
                {errors.password && (
                  <span style={{color: '#dc2626', fontSize: '0.9em', marginTop: '4px', display: 'block'}}>
                    {errors.password}
                  </span>
                )}
              </label>
              
              <button 
                className="btn" 
                type="submit" 
                disabled={isSubmitting || Object.keys(errors).length > 0}
                style={{
                  opacity: isSubmitting || Object.keys(errors).length > 0 ? 0.6 : 1,
                  cursor: isSubmitting || Object.keys(errors).length > 0 ? 'not-allowed' : 'pointer'
                }}
              >
                {isSubmitting ? 'Logging in...' : 'Log in'}
              </button>
              
              <p>No account? <a href="register-react.php">Create one</a></p>
            </form>
          </div>
        </div>
      );
    }

    ReactDOM.render(<LoginForm />, document.getElementById('react-login'));
  </script>
</body>
</html>