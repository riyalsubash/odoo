<?php
require_once 'config/db_connect.php';
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']); 
    $password = $_POST['password'];

    
    if (empty($name) || empty($username) || empty($email) || empty($phone_number) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) { 
        $errors[] = "Invalid phone number format. Please enter a 10-digit number.";
    } else {
        
        $stmt_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();
        
        
        $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_user->bind_param("s", $username);
        $stmt_user->execute();
        $stmt_user->store_result();
        
        if ($stmt_email->num_rows > 0) {
            $errors[] = "Email already registered.";
        } elseif ($stmt_user->num_rows > 0) {
            $errors[] = "Username already taken.";
        } else {
            $verification_token = bin2hex(random_bytes(32));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            
            $insert_stmt = $conn->prepare("INSERT INTO users (name, username, email, phone_number, password, verification_token) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $name, $username, $email, $phone_number, $hashed_password, $verification_token);
            
            if ($insert_stmt->execute()) {
                
                $verification_link = "http://localhost/odoo/verify.php?token=" . $verification_token; // Unga project path correct-a kudunga
                $subject = "Verify Your Email - Rebayan";
                $message = "Welcome to Rebayan! Please click the link below to verify your email address:\n\n" . $verification_link;
                $headers = "From: no-reply@rebayan.com";

                if (mail($email, $subject, $message, $headers)) {
                    $success_message = "Registration successful! A verification link has been sent to your email. Please check your inbox (and spam folder) to activate your account.";
                } else {
                    $errors[] = "Could not send verification email. Please contact support.";
                }
            } else {
                $errors[] = "Error: Could not register. Please try again.";
            }
        }
    }
}
include 'templates/header.php';
?>
<div class="auth-form-container">
    <h2>Sign Up for Rebayan</h2>
    <?php if (!empty($errors)): ?><div class="alert error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>
    <?php if ($success_message): ?><div class="alert success"><p><?= $success_message; ?></p></div>
    <?php else: ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="phone_number" placeholder="10-digit number" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Sign Up</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="login.php">Login here</a>.</p>
    <?php endif; ?>
</div>
<?php include 'templates/footer.php'; ?>