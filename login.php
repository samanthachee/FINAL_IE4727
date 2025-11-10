<?php
require __DIR__ . '/db.php';
if (!empty($_SESSION['user_id'])) { header('Location: index-react.php'); exit; }
// Redirect to React version for better UX
header('Location: login-react.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .error-msg { color: #dc2626; font-size: 0.9em; margin-top: 4px; display: block; }
  </style>
</head>
<body>
  <header><h1>Login</h1><div class="logo"><img src="LaureTha.png" alt="LaureTha Logo"></div></header>
  <nav>
    <a href="index.php">Home</a> | <a href="catalogue.php">Catalogue</a> |
    <a href="cart.php">Cart</a> | <a href="register.php">Register</a>
  </nav>

  <div class="auth-card">
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
        header('Location: index.php'); exit;
      } else {
        echo '<p class="err">Invalid email or password.</p>';
      }
    }
    ?>
    <form id="loginForm" method="post" class="form">
      <label>Email
        <input type="email" id="email" name="email" required>
        <span class="error-msg" id="emailError"></span>
      </label>
      <label>Password
        <input type="password" id="password" name="password" required>
        <span class="error-msg" id="passwordError"></span>
      </label>
      <button class="btn" type="submit">Log in</button>
      <p>No account? <a href="register.php">Create one</a></p>
    </form>
    
    <script>
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
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
      if (password && password.length < 8) {
        error.textContent = 'Password too short';
        return false;
      }
      error.textContent = '';
      return true;
    }
    
    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', validatePassword);
    
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      if (!validateEmail() || !validatePassword()) {
        e.preventDefault();
      }
    });
    </script>
  </div>
</body>
</html>
