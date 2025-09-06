<?php
require_once 'config/db_connect.php';
$token = $_GET['token'] ?? '';
$errors = [];
$message = '';

$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    
    include 'templates/header.php';
    echo "<div class='container'><div class='alert error'>This password reset link is invalid or has expired.</div></div>";
    include 'templates/footer.php';
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($password) || empty($password_confirm)) {
        $errors[] = "Please enter and confirm your new password.";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($update_stmt->execute()) {
            $message = "<div class='alert success'>Your password has been changed successfully! You can now <a href='login.php'>log in</a>.</div>";
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
}

include 'templates/header.php';
?>

<div class="auth-form-container">
    <h2>Reset Your Password</h2>
    
    <?php if ($message) echo $message; ?>
    <?php if (!empty($errors)): ?><div class="alert error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

    <?php if (!$message): ?>
    <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
        <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm New Password:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Reset Password</button>
    </form>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>