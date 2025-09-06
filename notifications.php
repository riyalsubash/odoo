<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];


$conn->execute_query("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$user_id]);


$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="page-header">
    <h1>Your Notifications</h1>
</div>
<div class="notifications-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <a href="<?= htmlspecialchars($row['link']) ?>" class="notification-item <?= ($row['is_read'] == 0 ? 'notification-unread' : '') ?>">
                <div class="notification-message"><?= htmlspecialchars($row['message']) ?></div>
                <div class="notification-time"><?= date('M j, Y, g:i a', strtotime($row['created_at'])) ?></div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no notifications.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>