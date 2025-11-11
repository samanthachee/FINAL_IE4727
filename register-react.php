<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Account - React Version</title>
  <link rel="stylesheet" href="style.css">
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
  <div id="react-register"></div>

  <script type="text/babel">
    const { useState } = React;

    function RegisterForm() {
      const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        confirmPassword: ''
      });
      const [errors, setErrors] = useState({});
      const [isSubmitting, setIsSubmitting] = useState(false);
      //constraints for form validation
      const validateName = (name) => {
        if (!name.trim()) return 'Name is required';
        if (!/^[a-zA-Z\s]+$/.test(name.trim())) return 'Name can only contain letters and spaces';
        if (name.trim().length < 2) return 'Name must be at least 2 characters long';
        return '';
      };

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

      const validateConfirm = (password, confirm) => {
        if (!confirm) return 'Please confirm password';
        if (password !== confirm) return 'Passwords do not match';
        return '';
      };

      const handleInputChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        
        //Real-time validation
        const newErrors = { ...errors };
        
        if (field === 'name') {
          const nameError = validateName(value);
          if (nameError) newErrors.name = nameError;
          else delete newErrors.name;
        }
        
        if (field === 'email') {
          const emailError = validateEmail(value);
          if (emailError) newErrors.email = emailError;
          else delete newErrors.email;
        }
        
        if (field === 'password') {
          const passwordError = validatePassword(value);
          if (passwordError) newErrors.password = passwordError;
          else delete newErrors.password;
          
          //Re-validate confirm password
          if (formData.confirmPassword) {
            const confirmError = validateConfirm(value, formData.confirmPassword);
            if (confirmError) newErrors.confirmPassword = confirmError;
            else delete newErrors.confirmPassword;
          }
        }
        
        if (field === 'confirmPassword') {
          const confirmError = validateConfirm(formData.password, value);
          if (confirmError) newErrors.confirmPassword = confirmError;
          else delete newErrors.confirmPassword;
        }
        
        setErrors(newErrors);
      };

      const handleSubmit = async (e) => {
        e.preventDefault();
        
        const nameError = validateName(formData.name);
        const emailError = validateEmail(formData.email);
        const passwordError = validatePassword(formData.password);
        const confirmError = validateConfirm(formData.password, formData.confirmPassword);
        
        if (nameError || emailError || passwordError || confirmError) {
          setErrors({
            name: nameError,
            email: emailError,
            password: passwordError,
            confirmPassword: confirmError
          });
          return;
        }

        setIsSubmitting(true);
        
        try {
          const form = new FormData();
          form.append('name', formData.name);
          form.append('email', formData.email);
          form.append('password', formData.password);
          
          const response = await fetch('register-react.php', {
            method: 'POST',
            body: form
          });
          
          const text = await response.text();
          
          if (text.includes('Account created')) {
            alert('Account created successfully! Please log in.');
            window.location.href = 'login-react.php';
          } else if (text.includes('already registered')) {
            setErrors({ general: 'That email is already registered.' });
          } else {
            setErrors({ general: 'Registration failed. Please try again.' });
          }
        } catch (error) {
          setErrors({ general: 'Registration failed. Please try again.' });
        }
        
        setIsSubmitting(false);
      };

      return (
        <div>
          <header>
            <h1>Register</h1>
            <div className="logo"><img src="LaureTha.png" alt="LaureTha Logo" /></div>
          </header>
          
          <nav>
            <a href="index-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Home</a> | 
            <a href="catalogue-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Catalogue</a> |
            <a href="cart-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Cart</a> | 
            <a href="login-react.php" style={{fontSize:'1.3em', fontWeight:'600', margin:'0 20px', color:'#1f1f1f', textDecoration:'none'}}>Login</a>
          </nav>

          <div className="auth-card">
            <h2>Create Account</h2>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              $name  = trim($_POST['name'] ?? '');
              $email = trim($_POST['email'] ?? '');
              $pass  = $_POST['password'] ?? '';

              $errors = [];
              if ($name==='' || $email==='' || $pass==='') $errors[]='All fields are required.';
              if (!preg_match('/^[a-zA-Z\\s]+$/', $name)) $errors[]='Name can only contain letters and spaces.';
              
              if (!preg_match('/^[\\w\\.-]+@[\\w\\.-]+$/', $email)) {
                $errors[] = "Invalid email address format.";
              } else {
                list(, $domain) = explode('@', $email, 2);
                $parts = explode('.', $domain);
                
                if (count($parts) < 2 || count($parts) > 4) {
                  $errors[] = "Domain must have 2–4 parts (e.g., name@ntu.edu.sg).";
                } else {
                  for ($i = 0; $i < count($parts) - 1; $i++) {
                    if (!preg_match('/^\\w+$/', $parts[$i])) {
                      $errors[] = "Each domain label must contain only word characters.";
                      break;
                    }
                  }
                  $tld = $parts[count($parts) - 1];
                  if (!in_array(strtolower($tld), ['sg', 'com'])) {
                    $errors[] = "Email must end with .sg or .com";
                  }
                }
              }
              
              if (strlen($pass) < 8 || !preg_match('/\\d/', $pass)) $errors[]='Password must be at least 8 characters with one number.';

              if (!$errors) {
                try {
                  $stmt = db()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
                  $stmt->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
                  echo '<p class="ok">Account created! <a href="login-react.php">Log in</a></p>';
                } catch(PDOException $e) {
                  echo ($e->getCode()==23000)
                    ? '<p class="err">That email is already registered.</p>'
                    : '<p class="err">Error: '.htmlspecialchars($e->getMessage()).'</p>';
                }
              } else {
                echo '<ul class="err">';
                foreach($errors as $er) echo '<li>'.htmlspecialchars($er).'</li>';
                echo '</ul>';
              }
            }
            ?>
            
            {errors.general && (
              <div style={{color: '#dc2626', padding: '10px', background: '#ffe4e6', border: '2px solid #fecaca', borderRadius: '10px', margin: '8px 0'}}>
                {errors.general}
              </div>
            )}
            
            <form onSubmit={handleSubmit} className="form">
              <label>
                Name
                <input 
                  type="text" 
                  value={formData.name}
                  onChange={(e) => handleInputChange('name', e.target.value)}
                  required
                  style={{
                    borderColor: errors.name ? '#dc2626' : '#e5c5ff'
                  }}
                />
                {errors.name && (
                  <span style={{color: '#dc2626', fontSize: '0.9em', marginTop: '4px', display: 'block'}}>
                    {errors.name}
                  </span>
                )}
              </label>
              
              <label>
                Email
                <input 
                  type="email" 
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
              
              <label>
                Confirm Password
                <input 
                  type="password" 
                  value={formData.confirmPassword}
                  onChange={(e) => handleInputChange('confirmPassword', e.target.value)}
                  required
                  style={{
                    borderColor: errors.confirmPassword ? '#dc2626' : '#e5c5ff'
                  }}
                />
                {errors.confirmPassword && (
                  <span style={{color: '#dc2626', fontSize: '0.9em', marginTop: '4px', display: 'block'}}>
                    {errors.confirmPassword}
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
                {isSubmitting ? 'Creating Account...' : 'Create Account'}
              </button>
              
              <p>Already have an account? <a href="login-react.php">Log in</a></p>
            </form>
          </div>
        </div>
      );
    }

    ReactDOM.render(<RegisterForm />, document.getElementById('react-register'));
  </script>
</body>
</html>