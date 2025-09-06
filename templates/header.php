<?php 
require_once __DIR__ . '/../config/db_connect.php'; 
$unread_notifications = 0;
// User login pannirundha, avanga unread notification count-ah edukurom
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $unread_notifications = $result['unread_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rebayan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">🌿 Rebayan</a>
            <button class="menu-toggle" id="mobile-menu" aria-label="Open navigation menu">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </button>
            <ul class="nav-links" id="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php">Browse</a></li>
                    <li><a href="my_listings.php">My Listings</a></li>
                    <li><a href="manage_requests.php">Manage Requests</a></li>
                    <li><a href="my_orders.php">My Orders</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li class="nav-icon">
                        <a href="notifications.php" class="notification-bell">
                            🔔
                            <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?= $unread_notifications ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php">Browse</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="container">