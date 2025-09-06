<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$buyer_id = $_SESSION['user_id'];


$sql = "SELECT p.title, p.price, p.image_paths, pur.purchase_date 
        FROM purchases pur 
        JOIN products p ON pur.product_id = p.id 
        WHERE pur.buyer_id = ? 
        ORDER BY pur.purchase_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="page-header">
    <h1>My Previous Purchases</h1>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert success">Thank you for your purchase!</div>
<?php endif; ?>

<div class="product-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
            
            $images = !empty($row['image_paths']) ? explode(',', $row['image_paths']) : [];
            $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
            ?>
            <div class="product-card">
                <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="product-image">
                <div class="product-info">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p class="product-price">Purchased for: ₹<?= htmlspecialchars($row['price']) ?></p>
                    <p class="purchase-date" style="font-size: 0.9em; color: #777;">Date: <?= date('F j, Y', strtotime($row['purchase_date'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have not purchased any items yet.</p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
include 'templates/footer.php';
?>