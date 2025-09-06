<?php
require_once 'config/db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $user_id = $user['id'];
        
        $token = bin2hex(random_bytes(32));
        
        
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = CURRENT_TIMESTAMP + INTERVAL 1 HOUR WHERE id = ?");
        
        
        $update_stmt->bind_param("si", $token, $user_id);
        $update_stmt->execute();
        
        
        $reset_link = "http://localhost/odoo/reset_password.php?token=" . $token;
        $subject = "Password Reset Request - Rebayan";
        $mail_message = "To reset your password, please click the link below:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.";
        $headers = "From: no-reply@rebayan.com";
        
        mail($email, $subject, $mail_message, $headers);
    }
    
    
    $message = "<div class='alert success'>If an account with that email exists, a password reset link has been sent. Please check your inbox and spam folder.</div>";
}

include 'templates/header.php';
?>

<div class="auth-form-container">
    <h2>Forgot Password</h2>
    <p>Enter your email address and we will send you a link to reset your password.</p>
    
    <?php if ($message) echo $message; ?>
    
    <form action="forgot_password.php" method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="btn">Send Reset Link</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>