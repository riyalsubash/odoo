<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];


if (isset($_POST['delete_product'])) {
    $product_id_to_delete = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id_to_delete, $user_id);
    if ($stmt->execute()) {
        header("Location: my_listings.php?status=deleted"); 
        exit();
    }
}


$stmt = $conn->prepare("SELECT id, title, price, image_paths, status FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id); 
$stmt->execute(); 
$result = $stmt->get_result();
?>
<div class="page-header">
    <h1>My Listings</h1>
    <a href="add_product.php" class="btn">+ Add New Product</a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="alert success" style="text-align:center;">Action was successful!</div>
<?php endif; ?>

<div class="listings-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
            
            $images = !empty($row['image_paths']) ? explode(',', $row['image_paths']) : [];
            $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
            ?>
            <div class="listing-card">
                <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" class="listing-card-image" alt="<?= htmlspecialchars($row['title']) ?>">
                
                <div class="listing-card-details">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p class="listing-card-price">₹<?= htmlspecialchars($row['price']) ?></p>
                    <span class="listing-card-status status-<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </div>

                <div class="listing-card-actions">
                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                    <form action="my_listings.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_product" class="btn btn-delete">Delete</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You haven't listed any products yet. <a href="add_product.php">Add one now!</a></p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>