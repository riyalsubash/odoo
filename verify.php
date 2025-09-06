<?php
require_once 'config/db_connect.php';
$message = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if ($update_stmt->execute()) {
            $message = "<div class='alert success'>Your email has been successfully verified! You can now <a href='login.php'>log in</a>.</div>";
        } else {
            $message = "<div class='alert error'>Failed to verify your account. Please try again later.</div>";
        }
    } else {
        $message = "<div class='alert error'>This verification link is invalid or has already been used.</div>";
    }
} else {
    $message = "<div class='alert error'>No verification token provided.</div>";
}

include 'templates/header.php';
?>
<div class="container" style="text-align: center; padding-top: 50px;">
    <h2>Email Verification</h2>
    <?= $message ?>
</div>
<?php include 'templates/footer.php'; ?>