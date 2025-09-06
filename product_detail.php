<?php
include 'templates/header.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$product_id = $_GET['id'];


if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
    }
    header("Location: cart.php");
    exit();
}


$stmt = $conn->prepare("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();


if (!$product) {
    echo "<div class='alert error'>Product not found.</div>";
    include 'templates/footer.php';
    exit();
}


$images = [];
if (!empty($product['image_paths'])) {
    $images = explode(',', $product['image_paths']);
}
$main_image = !empty($images) ? $images[0] : 'placeholder.png';
?>

<div class="product-detail-container">
    <div class="product-gallery">
        <div class="main-image-container">
            <img id="main-product-image" src="uploads/<?= htmlspecialchars($main_image) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
        <?php if(count($images) > 1): ?>
        <div class="thumbnail-container">
            <?php foreach($images as $img): ?>
                <img src="uploads/<?= htmlspecialchars($img) ?>" class="thumbnail" onclick="changeMainImage(this)">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="product-detail-info">
        <h1><?= htmlspecialchars($product['title']) ?></h1>
        <p class="product-price">₹<?= htmlspecialchars($product['price']) ?></p>
        <p><b>Sold by:</b> 
            <a class="seller-link" href="user_profile.php?id=<?= $product['user_id'] ?>">
                <?= htmlspecialchars($product['username']) ?>
            </a>
        </p>
        
        <h3>Description</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        
        <?php 
        if ($product['status'] == 'available' && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['user_id']): 
        ?>
            <form method="POST" action="product_detail.php?id=<?= $product_id ?>">
                <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            </form>
        <?php 
        elseif ($product['status'] == 'sold'): 
        ?>
            <div class="alert error" style="text-align:left;">This item has been sold.</div>
        <?php 
        endif; 
        ?>
    </div>
    </div>

<div class="product-specs-container">
    <h3>Specifications & Details</h3>
    <ul class="specs-list">
        <?php if(!empty($product['category'])): ?><li><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></li><?php endif; ?>
        <?php if(!empty($product['product_condition'])): ?><li><strong>Condition:</strong> <?= htmlspecialchars($product['product_condition']) ?></li><?php endif; ?>
        <?php if(!empty($product['brand'])): ?><li><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></li><?php endif; ?>
        <?php if(!empty($product['model'])): ?><li><strong>Model:</strong> <?= htmlspecialchars($product['model']) ?></li><?php endif; ?>
        <?php if(!empty($product['year_of_manufacture'])): ?><li><strong>Manufactured in:</strong> <?= htmlspecialchars($product['year_of_manufacture']) ?></li><?php endif; ?>
        <?php if(!empty($product['color'])): ?><li><strong>Color:</strong> <?= htmlspecialchars($product['color']) ?></li><?php endif; ?>
        <?php if(!empty($product['material'])): ?><li><strong>Material:</strong> <?= htmlspecialchars($product['material']) ?></li><?php endif; ?>
        <?php if(!empty($product['dimensions'])): ?><li><strong>Dimensions:</strong> <?= htmlspecialchars($product['dimensions']) ?></li><?php endif; ?>
        <?php if(!empty($product['weight_kg'])): ?><li><strong>Weight:</strong> <?= htmlspecialchars($product['weight_kg']) ?> kg</li><?php endif; ?>
        <?php if(!empty($product['working_condition'])): ?><li><strong>Working Condition:</strong> <?= htmlspecialchars($product['working_condition']) ?></li><?php endif; ?>
        <li><strong>Original Packaging:</strong> <?= $product['has_original_packaging'] ? 'Yes' : 'No' ?></li>
        <li><strong>Manual Included:</strong> <?= $product['has_manual'] ? 'Yes' : 'No' ?></li>
    </ul>
</div>

<script>
function changeMainImage(thumbnailElement) {
    document.getElementById('main-product-image').src = thumbnailElement.src;
}
</script>

<?php
$stmt->close();
include 'templates/footer.php';
?>