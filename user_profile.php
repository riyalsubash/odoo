<?php
include 'templates/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert error'>Invalid user profile.</div>";
    include 'templates/footer.php';
    exit();
}
$profile_user_id = $_GET['id'];

// User details-ah edukurom
$user_stmt = $conn->prepare("SELECT name, username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $profile_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "<div class='alert error'>User not found.</div>";
    include 'templates/footer.php';
    exit();
}

// Antha user-oda products-ah edukurom
$products_stmt = $conn->prepare("SELECT id, title, price, image_paths FROM products WHERE user_id = ? AND status = 'available' ORDER BY created_at DESC");
$products_stmt->bind_param("i", $profile_user_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
?>

<div class="user-profile-header">
    <h1><?= htmlspecialchars($user['name']); ?>'s Listings</h1>
    <p>(@<?= htmlspecialchars($user['username']); ?>)</p>
</div>

<hr class="section-divider">

<h3>Products from this seller:</h3>
<br>
<div class="product-grid">
    <?php if ($products_result->num_rows > 0): ?>
        <?php while($product = $products_result->fetch_assoc()): ?>
            <?php
            // ## INTHA PUTHU LOGIC THAAN PROBLEM-AH FIX PANNUTHU ##
            // Database-la irunthu varra image paths string-ah array-ah maathrom
            $images = !empty($product['image_paths']) ? explode(',', $product['image_paths']) : [];
            // Antha array-la irunthu mudhal image-ah o, image illana placeholder-ah o eduthukurom
            $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
            ?>
            <div class="product-card">
                <a href="product_detail.php?id=<?= $product['id'] ?>">
                    <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['title']) ?></h3>
                        <p class="product-price">₹<?= htmlspecialchars($product['price']) ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p><?= htmlspecialchars($user['name']); ?> has no active listings right now.</p>
    <?php endif; ?>
</div>

<?php 
$user_stmt->close();
$products_stmt->close();
include 'templates/footer.php'; 
?>