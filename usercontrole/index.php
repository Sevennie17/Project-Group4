<?php
require_once __DIR__ . '/../includes/auth.php';
start_app_session();
require_once __DIR__ . '/User.php';

$allowedRoles = ['admin', 'manager', 'sale_staff', 'cashier'];

if (
    isset($_POST['username'], $_POST['password'], $_POST['role'])
    && is_string($_POST['username'])
    && is_string($_POST['password'])
    && is_string($_POST['role'])
) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (in_array($role, $allowedRoles, true)) {
        $userModel = new User($con);
        $user = $userModel->authenticate($username, $password, $role);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['authenticated'] = true;
            $_SESSION['user'] = [
                'UserID' => $user['UserID'],
                'UserName' => $user['UserName'],
                'RuleID' => $user['RuleID'],
                'RuleName' => $user['RuleName'],
            ];
            header('Location: ' . app_base_path() . '/dashboards/index.php');
            exit();
        }
    }

    $error_message = 'Invalid role or incorrect credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login_style.css">

</head>
<body>

   <section class="login-container">
    <div class="login_p1">
        <h1>Jewelry Store</h1>
         <div class="login-brand-logo">💎</div>
         <div class="sc-lp1">
             <p>Premium Jewelry Management System</p>
            <p> Elegance in Every Transaction</p>

         </div>
    </div>
    <div class="login_line"></div>
    <div class="login_p2">
        <h2>Login</h2>
        <p>Please check your role!</p>

        
        <form action="" method="POST">
            <input type="hidden" name="role" id="selected-role" value="" required>

            <div class="role-container">
                <button type="button" class="role-btn" onclick="selectRole(this, 'admin')">Admin Login</button>
                <button type="button" class="role-btn" onclick="selectRole(this, 'manager')">Manager Login</button>
                <button type="button" class="role-btn" onclick="selectRole(this, 'sale_staff')">Sale Staff Login</button>
                <button type="button" class="role-btn" onclick="selectRole(this, 'cashier')">Cashier Login</button>
            </div>

            <div class="credentials-container" style="margin-top: 20px;">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <?php if (isset($error_message)): ?>
                    <p id="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
   </section>

   <script>
       function selectRole(buttonElement, roleName) {
           document.getElementById('selected-role').value = roleName;
           var buttons = document.querySelectorAll('.role-btn');
           buttons.forEach(function(btn) {
               btn.classList.remove('active');
           });
           buttonElement.classList.add('active');
       }
   </script>

</body>
</html>
