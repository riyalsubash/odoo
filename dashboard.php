<?php
include 'templates/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$update_message = '';
$password_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    
    if (isset($_POST['update_profile'])) {
        $new_name = trim($_POST['name']);
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $new_phone_number = trim($_POST['phone_number']); 

        
        if (!preg_match('/^[0-9]{10}$/', $new_phone_number)) {
            $update_message = "<div class='alert error'>Invalid phone number format. Please enter a 10-digit number.</div>";
        } else {
            $user_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $old_email = $user_stmt->get_result()->fetch_assoc()['email'];
            $user_stmt->close();

            
            if ($new_email !== $old_email) {
                $verification_token = bin2hex(random_bytes(32));
                $is_verified = 0;

                
                $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone_number = ?, is_verified = ?, verification_token = ? WHERE id = ?");
                $stmt->bind_param("ssssisi", $new_name, $new_username, $new_email, $new_phone_number, $is_verified, $verification_token, $user_id);

                if ($stmt->execute()) {
                    
                    $verification_link = "http://localhost/odoo/verify.php?token=" . $verification_token;
                    $subject = "Please Verify Your New Email Address";
                    $mail_message = "You have updated your email address. Please click the link to verify it:\n\n" . $verification_link;
                    $headers = "From: no-reply@rebayan.com";
                    mail($new_email, $subject, $mail_message, $headers);

                    session_unset();
                    session_destroy();
                    header("Location: login.php?status=emailchanged");
                    exit();
                } else {
                    $update_message = "<div class='alert error'>Error updating profile. The email or username might be taken.</div>";
                }
            } else {
                
                $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, phone_number = ? WHERE id = ?");
                $stmt->bind_param("sssi", $new_name, $new_username, $new_phone_number, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['username'] = $new_username;
                    $update_message = "<div class='alert success'>Profile updated successfully!</div>";
                } else {
                    $update_message = "<div class='alert error'>Error updating profile. Username might be taken.</div>";
                }
            }
        }
    }

    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $password_message = "<div class='alert error'>All password fields are required.</div>";
        } elseif ($new_password !== $confirm_new_password) {
            $password_message = "<div class='alert error'>New passwords do not match.</div>";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $hashed_password = $stmt->get_result()->fetch_assoc()['password'];
            
            if (password_verify($current_password, $hashed_password)) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                if ($update_stmt->execute()) {
                    $password_message = "<div class='alert success'>Password changed successfully!</div>";
                } else {
                    $password_message = "<div class='alert error'>Failed to change password.</div>";
                }
            } else {
                $password_message = "<div class='alert error'>Incorrect current password.</div>";
            }
        }
    }
}


$earnings_stmt = $conn->prepare("SELECT SUM(price) as total_earnings FROM products WHERE user_id = ? AND status = 'sold'");
$earnings_stmt->bind_param("i", $user_id);
$earnings_stmt->execute();
$total_earnings = $earnings_stmt->get_result()->fetch_assoc()['total_earnings'] ?? 0;


$stmt = $conn->prepare("SELECT name, username, email, phone_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="form-container">
    <h2>User Dashboard</h2>

    <div class="dashboard-summary">
        <h3>Your Earnings</h3>
        <p class="earnings-display">Total Sold Value: ₹<?= number_format($total_earnings, 2) ?></p>
    </div>
    
    <hr class="section-divider">

    <div class="dashboard-section">
        <h3>Edit Profile</h3>
        <?php if ($update_message) echo $update_message; ?>
        <form action="dashboard.php" method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
            </div>

            <button type="submit" name="update_profile" class="btn">Update Profile</button>
        </form>
    </div>

    <hr class="section-divider">

    <div class="dashboard-section">
        <h3>Change Password</h3>
        <?php if ($password_message) echo $password_message; ?>
        <form action="dashboard.php" method="POST">
            <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
            <div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
            <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_new_password" required></div>
            <button type="submit" name="change_password" class="btn">Change Password</button>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>