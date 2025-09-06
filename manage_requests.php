<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$seller_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $buyer_id = $_POST['buyer_id'];
    $buyer_email = $_POST['buyer_email'];
    $product_title = $_POST['product_title'];

    $verify_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND seller_id = ? AND order_status = 'pending_approval'");
    $verify_stmt->bind_param("ii", $order_id, $seller_id);
    $verify_stmt->execute();
    if ($verify_stmt->get_result()->num_rows == 1) {
        
        $seller_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $seller_stmt->bind_param("i", $seller_id);
        $seller_stmt->execute();
        $seller_email = $seller_stmt->get_result()->fetch_assoc()['email'];

        if (isset($_POST['approve'])) {
            $conn->execute_query("UPDATE orders SET order_status = 'approved' WHERE id = ?", [$order_id]);
            $conn->execute_query("UPDATE products SET status = 'sold' WHERE id = ?", [$product_id]);
            $conn->execute_query("INSERT INTO purchases (buyer_id, product_id) VALUES (?, ?)", [$buyer_id, $product_id]);
            
            $subject_approve = "Your purchase request for '{$product_title}' has been Approved!";
            $mail_message_approve = "Hi there,\n\nGreat news! The seller has approved your request for the product: '{$product_title}'.\n\nPlease contact the seller directly to arrange for payment and pickup/delivery.\n\nSeller's Email: {$seller_email}\n\nThank you for using EcoFinds!";
            $headers = "From: no-reply@ecofinds.com";
            mail($buyer_email, $subject_approve, $mail_message_approve, $headers);

            $notification_message_approve = "Your request for '{$product_title}' has been approved! Contact the seller.";
            $notification_link_approve = "my_orders.php";
            $conn->execute_query("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)", [$buyer_id, $notification_message_approve, $notification_link_approve]);

            $message = "<div class='alert success'>Request approved! The buyer has been notified.</div>";

        } elseif (isset($_POST['reject'])) {
            $conn->execute_query("UPDATE orders SET order_status = 'rejected' WHERE id = ?", [$order_id]);
            $conn->execute_query("UPDATE products SET status = 'available' WHERE id = ?", [$product_id]);
            
            $subject_reject = "Update on your purchase request for '{$product_title}'";
            $mail_message_reject = "Hi there,\n\nUnfortunately, the seller has rejected your request for the product: '{$product_title}'.\n\nThe item is now available for others to purchase. You can continue browsing for other items on EcoFinds.";
            $headers = "From: no-reply@ecofinds.com";
            mail($buyer_email, $subject_reject, $mail_message_reject, $headers);

            $notification_message_reject = "Your request for '{$product_title}' has been rejected.";
            $notification_link_reject = "my_orders.php";
            $conn->execute_query("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)", [$buyer_id, $notification_message_reject, $notification_link_reject]);

            $message = "<div class='alert success'>Request rejected. The buyer has been notified.</div>";
        }

    } else {
        $message = "<div class='alert error'>Invalid action. You do not have permission.</div>";
    }
}

$stmt = $conn->prepare("SELECT o.id as order_id, o.buyer_id, p.id as product_id, p.title, p.price, p.image_paths, u.name as buyer_name, u.username as buyer_username, u.email as buyer_email, u.phone_number as buyer_phone 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       JOIN users u ON o.buyer_id = u.id 
                       WHERE o.seller_id = ? AND o.order_status = 'pending_approval' ORDER BY o.created_at DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="page-header">
    <h1>Manage Purchase Requests</h1>
    <p>Review and respond to purchase requests for your items.</p>
</div>

<?php if ($message) echo $message; ?>

<div class="listings-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="request-card">
                <img src="uploads/<?= htmlspecialchars(!empty(explode(',', $row['image_paths'])) ? explode(',', $row['image_paths'])[0] : 'placeholder.png') ?>" class="listing-card-image" alt="<?= htmlspecialchars($row['title']) ?>">
                
                <div class="request-card-details">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p class="listing-card-price">Price: ₹<?= htmlspecialchars($row['price']) ?></p>
                    <p class="buyer-info">Requested by: <strong><?= htmlspecialchars($row['buyer_name']) ?></strong> (@<?= htmlspecialchars($row['buyer_username']) ?>)</p>
                </div>

                <div class="listing-card-actions">
                    <form action="manage_requests.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                        <input type="hidden" name="buyer_id" value="<?= $row['buyer_id'] ?>">
                        <input type="hidden" name="buyer_email" value="<?= $row['buyer_email'] ?>">
                        <input type="hidden" name="product_title" value="<?= htmlspecialchars($row['title']) ?>">
                        <button type="submit" name="approve" class="btn btn-success">Approve</button>
                    </form>

                    <a href="mailto:<?= htmlspecialchars($row['buyer_email']) ?>?subject=Regarding your request for '<?= rawurlencode($row['title']) ?>'" class="btn btn-contact">Email Buyer</a>
                    <?php if(!empty($row['buyer_phone'])): ?>
                    <a href="tel:<?= htmlspecialchars($row['buyer_phone']) ?>" class="btn btn-call">Call Buyer</a>
                    <?php endif; ?>
                    
                    <form action="manage_requests.php" method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                        <input type="hidden" name="buyer_id" value="<?= $row['buyer_id'] ?>">
                        <input type="hidden" name="buyer_email" value="<?= $row['buyer_email'] ?>">
                        <input type="hidden" name="product_title" value="<?= htmlspecialchars($row['title']) ?>">
                        <button type="submit" name="reject" class="btn btn-delete" onclick="return confirm('Are you sure you want to reject this request?');">Reject</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no pending purchase requests right now.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>