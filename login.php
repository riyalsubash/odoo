<?php
require_once 'config/db_connect.php';
$errors = [];


if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $login_identifier = trim($_POST['login_identifier']);
    $password = $_POST['password'];

    if (empty($login_identifier) || empty($password)) {
        $errors[] = "All fields are required.";
    } else {
        
        $stmt = $conn->prepare("SELECT id, username, password, is_verified FROM users WHERE email = ? OR username = ?");
        
        $stmt->bind_param("ss", $login_identifier, $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                
                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: index.php");
                    exit();
                } else {
                    $errors[] = "Your account is not verified. Please check your email.";
                }
                
            } else {
                $errors[] = "Invalid login credentials.";
            }
        } else {
            $errors[] = "Invalid login credentials.";
        }
        $stmt->close();
    }
}

include 'templates/header.php';
?>

<div class="auth-form-container">
    <h2>Login to Rebayan</h2>
    
    <?php if (isset($_GET['status']) && $_GET['status'] == 'emailchanged'): ?>
        <div class="alert success"><p>Your email has been updated. Please check your new email address for a verification link before logging in.</p></div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'verified'): ?>
        <div class="alert success"><p>Email verified successfully! Please log in.</p></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert error"><?php foreach ($errors as $error) echo "<p>$error</p>"; ?></div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="login_identifier">Email or Username:</label>
            <input type="text" id="login_identifier" name="login_identifier" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p class="auth-switch" style="margin-top: 15px;">
        <a href="forgot_password.php">Forgot Password?</a>
    </p>
    <p class="auth-switch">
        Don't have an account? <a href="register.php">Sign up here</a>.
    </p>
</div>

<?php include 'templates/footer.php'; ?>