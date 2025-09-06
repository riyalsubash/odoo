<?php
include 'templates/header.php';


$categories = ['Electronics', 'Furniture', 'Clothing', 'Books', 'Other'];


$sql = "SELECT p.id, p.title, p.price, p.image_paths, u.username, p.user_id FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'available'";

$params = [];
$types = '';


if (!empty($_GET['search'])) {
    $sql .= " AND p.title LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types .= 's';
}

if (!empty($_GET['category'])) {
    $sql .= " AND p.category = ?";
    $params[] = $_GET['category'];
    $types .= 's';
}

$sql .= " ORDER BY p.created_at DESC";


$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-header">
    <h1>Find Your Next Treasure</h1>
    <p>Buy and sell pre-owned goods, and contribute to a sustainable future.</p>
</div>

<div class="filter-bar">
    <form action="index.php" method="GET" class="search-form">
        <div class="search-input-wrapper">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 0 0 1.48-5.34c-.47-2.78-2.79-5-5.59-5.34a6.505 6.505 0 0 0-7.27 7.27c.34 2.8 2.56 5.12 5.34 5.59a6.5 6.5 0 0 0 5.34-1.48l.27.28v.79l4.25 4.25a1 1 0 1 0 1.41-1.41L15.5 14zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
            <input type="text" name="search" class="search-input" placeholder="Search for anything..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <select name="category" class="category-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= (($_GET['category'] ?? '') == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-filter">Search</button>
    </form>
</div>

<div class="product-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
            
            $images = !empty($row['image_paths']) ? explode(',', $row['image_paths']) : [];
            
            $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
            ?>
            <div class="product-card">
                <a href="product_detail.php?id=<?= $row['id'] ?>">
                    <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="product-image">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($row['title']) ?></h3>
                        <p class="product-price">₹<?= htmlspecialchars($row['price']) ?></p>
                        
                        <div class="product-seller-info">
                            Sold by <a href="user_profile.php?id=<?= $row['user_id'] ?>" class="seller-link"><?= htmlspecialchars($row['username']) ?></a>
                        </div>

                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; width: 100%; grid-column: 1 / -1;">No products found matching your criteria.</p>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
include 'templates/footer.php'; 
?>