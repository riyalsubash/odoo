<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$buyer_id = $_SESSION['user_id'];

// ## PUTHU CHANGE INGA: Seller-oda 'phone_number'-ah yum serthu edukurom ##
$stmt = $conn->prepare("SELECT o.order_status, p.title, p.price, p.image_paths, s.email as seller_email, s.phone_number as seller_phone
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       JOIN users s ON o.seller_id = s.id 
                       WHERE o.buyer_id = ? AND o.order_status != 'rejected' 
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="page-header"><h1>My Purchase Requests</h1></div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'requested'): ?>
    <div class="alert success" style="text-align:center;">Your request has been sent to the seller. You will be notified of their response.</div>
<?php endif; ?>

<div class="listings-container">
    <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
        <?php
        $images = !empty($row['image_paths']) ? explode(',', $row['image_paths']) : [];
        $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
        
        $card_class = '';
        if ($row['order_status'] == 'approved') {
            $card_class = 'order-approved';
        } elseif ($row['order_status'] == 'pending_approval') {
            $card_class = 'order-pending';
        }
        ?>
        <div class="listing-card <?= $card_class ?>">
            <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" class="listing-card-image">
            <div class="listing-card-details">
                <h3><?= htmlspecialchars($row['title']) ?></h3>
                <p class="listing-card-price">₹<?= htmlspecialchars($row['price']) ?></p>
                <span class="listing-card-status status-<?= strtolower($row['order_status']) ?>">
                    Status: <?= ucfirst(str_replace('_', ' ', $row['order_status'])) ?>
                </span>
            </div>
            
            <div class="listing-card-actions">
                <a href="mailto:<?= htmlspecialchars($row['seller_email']) ?>?subject=Regarding my request for '<?= rawurlencode($row['title']) ?>'" class="btn btn-contact">
                    Email Seller
                </a>
                
                <?php if(!empty($row['seller_phone'])): ?>
                <a href="tel:<?= htmlspecialchars($row['seller_phone']) ?>" class="btn btn-call">
                    Call Seller
                </a>
                <?php endif; ?>
            </div>

        </div>
    <?php endwhile; else: ?>
        <p>You have no active purchase requests.</p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>