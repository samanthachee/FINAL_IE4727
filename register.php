<?php 
require __DIR__ . '/db.php';
// Redirect to React version for better UX
header('Location: register-react.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Account</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .error-msg { color: #dc2626; font-size: 0.9em; margin-top: 4px; display: block; }
  </style>
</head>
<body>
  <header><h1>Register</h1><div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div></header>
  <nav>
    <a href="index.php">Home</a> | <a href="catalogue.php">Catalogue</a> |
    <a href="cart.php">Cart</a> | <a href="login.php">Login</a>
  </nav>

  <div class="auth-card">
    <h2>Create Account</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name  = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $pass  = $_POST['password'] ?? '';

      $errors = [];
      if ($name==='' || $email==='' || $pass==='') $errors[]='All fields are required.';
      if (!preg_match('/^[a-zA-Z\s]+$/', $name)) $errors[]='Name can only contain letters and spaces.';
      // Email: must have 2–4 domain parts, final part .sg or .com
      if (!preg_match('/^[\w\.-]+@[\w\.-]+$/', $email)) {
        $errors[] = "Invalid email address format.";
      } else {
        list(, $domain) = explode('@', $email, 2);
        $parts = explode('.', $domain);
        
        if (count($parts) < 2 || count($parts) > 4) {
          $errors[] = "Domain must have 2–4 parts (e.g., name@ntu.edu.sg).";
        } else {
          for ($i = 0; $i < count($parts) - 1; $i++) {
            if (!preg_match('/^\w+$/', $parts[$i])) {
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
      if (strlen($pass) < 8 || !preg_match('/\d/', $pass)) $errors[]='Password must be at least 8 characters with one number.';

      if (!$errors) {
        try {
          $stmt = db()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
          $stmt->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
          echo '<p class="ok">Account created! <a href="login.php">Log in</a></p>';
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
    <form id="registerForm" method="post" class="form">
      <label>Name
        <input type="text" id="name" name="name" required>
        <span class="error-msg" id="nameError"></span>
      </label>
      <label>Email
        <input type="email" id="email" name="email" required>
        <span class="error-msg" id="emailError"></span>
      </label>
      <label>Password
        <input type="password" id="password" name="password" required>
        <span class="error-msg" id="passwordError"></span>
      </label>
      <label>Confirm Password
        <input type="password" id="confirmPassword" required>
        <span class="error-msg" id="confirmError"></span>
      </label>
      <button class="btn" type="submit">Create Account</button>
      <p>Already have an account? <a href="login.php">Log in</a></p>
    </form>
    
    <script>
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    
    function validateName() {
      const name = nameInput.value.trim();
      const error = document.getElementById('nameError');
      if (name && !/^[a-zA-Z\s]+$/.test(name)) {
        error.textContent = 'Only letters and spaces allowed';
        return false;
      }
      error.textContent = '';
      return true;
    }
    
    function validateEmail() {
      const email = emailInput.value.trim();
      const error = document.getElementById('emailError');
      if (email) {
        if (!/^[\w\.-]+@[\w\.-]+$/.test(email)) {
          error.textContent = 'Invalid email format';
          return false;
        }
        const domain = email.split('@')[1];
        if (domain) {
          const parts = domain.split('.');
          if (parts.length < 2 || parts.length > 4) {
            error.textContent = 'Domain must have 2-4 parts';
            return false;
          }
          const tld = parts[parts.length - 1].toLowerCase();
          if (tld !== 'sg' && tld !== 'com') {
            error.textContent = 'Email must end with .sg or .com';
            return false;
          }
        }
      }
      error.textContent = '';
      return true;
    }
    
    function validatePassword() {
      const password = passwordInput.value;
      const error = document.getElementById('passwordError');
      if (password && (password.length < 8 || !/\d/.test(password))) {
        error.textContent = 'Min 8 characters with one number';
        return false;
      }
      error.textContent = '';
      return true;
    }
    
    function validateConfirm() {
      const password = passwordInput.value;
      const confirm = confirmInput.value;
      const error = document.getElementById('confirmError');
      if (confirm && password !== confirm) {
        error.textContent = 'Passwords do not match';
        return false;
      }
      error.textContent = '';
      return true;
    }
    
    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', () => { validatePassword(); validateConfirm(); });
    confirmInput.addEventListener('input', validateConfirm);
    
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      if (!validateName() || !validateEmail() || !validatePassword() || !validateConfirm()) {
        e.preventDefault();
      }
    });
    </script>
  </div>
</body>
</html>
