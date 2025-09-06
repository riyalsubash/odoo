<?php
include 'templates/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: my_listings.php"); exit(); }

$product_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$errors = [];
$categories = ['Electronics', 'Furniture', 'Clothing', 'Books', 'Other'];
$conditions = ['New', 'Used - Like New', 'Used - Good', 'Used - Fair'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    
    
    $quantity = $_POST['quantity'];
    $product_condition = $_POST['product_condition'];
    $year_of_manufacture = !empty($_POST['year_of_manufacture']) ? $_POST['year_of_manufacture'] : NULL;
    $brand = !empty($_POST['brand']) ? trim($_POST['brand']) : NULL;
    $model = !empty($_POST['model']) ? trim($_POST['model']) : NULL;
    $dimensions = !empty($_POST['dimensions']) ? trim($_POST['dimensions']) : NULL;
    $weight_kg = !empty($_POST['weight_kg']) ? $_POST['weight_kg'] : NULL;
    $material = !empty($_POST['material']) ? trim($_POST['material']) : NULL;
    $color = !empty($_POST['color']) ? trim($_POST['color']) : NULL;
    $has_original_packaging = isset($_POST['has_original_packaging']) ? 1 : 0;
    $has_manual = isset($_POST['has_manual']) ? 1 : 0;
    $working_condition = !empty($_POST['working_condition']) ? trim($_POST['working_condition']) : NULL;

    if (empty($title) || empty($price)) { $errors[] = "Title and Price are required."; }

    if (empty($errors)) {
        $sql = "UPDATE products SET 
                    title = ?, description = ?, category = ?, price = ?, quantity = ?, 
                    product_condition = ?, year_of_manufacture = ?, brand = ?, model = ?, dimensions = ?, 
                    weight_kg = ?, material = ?, color = ?, has_original_packaging = ?, has_manual = ?, 
                    working_condition = ?
                WHERE id = ? AND user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdisisssdsiiisii", 
            $title, $description, $category, $price, $quantity, $product_condition, 
            $year_of_manufacture, $brand, $model, $dimensions, $weight_kg, $material, 
            $color, $has_original_packaging, $has_manual, $working_condition,
            $product_id, $user_id);

        if ($stmt->execute()) {
            header("Location: my_listings.php?status=updated");
            exit();
        } else {
            $errors[] = "Failed to update product: " . $stmt->error;
        }
    }
}


$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='alert error'>Product not found or you don't have permission to edit it.</div>";
    include 'templates/footer.php';
    exit();
}
?>
<div class="form-container">
    <h2>Edit Product</h2>
    <?php if (!empty($errors)): ?><div class="alert error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

    <form action="edit_product.php?id=<?= htmlspecialchars($product_id); ?>" method="POST">
        <div class="form-group"><label>Product Title*</label><input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required></div>
        <div class="form-group"><label>Product Category*</label>
            <select name="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($product['category'] == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Product Description*</label><textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea></div>
        <div class="form-group"><label>Price (₹)*</label><input type="number" name="price" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required></div>
        <div class="form-group"><label>Quantity*</label><input type="number" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required></div>
        
        <hr class="section-divider">
        
        <h3>More Details</h3>
        <div class="form-group"><label>Condition</label>
            <select name="product_condition">
                <option value="">-- Select Condition --</option>
                <?php foreach ($conditions as $con): ?>
                     <option value="<?= $con ?>" <?= ($product['product_condition'] == $con) ? 'selected' : '' ?>><?= $con ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Year of Manufacture</label><input type="number" name="year_of_manufacture" value="<?= htmlspecialchars($product['year_of_manufacture']) ?>" placeholder="e.g., 2021"></div>
        <div class="form-group"><label>Brand</label><input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>"></div>
        <div class="form-group"><label>Model</label><input type="text" name="model" value="<?= htmlspecialchars($product['model']) ?>"></div>
        <div class="form-group"><label>Dimensions (L x W x H)</label><input type="text" name="dimensions" value="<?= htmlspecialchars($product['dimensions']) ?>" placeholder="e.g., 15x10x5 cm"></div>
        <div class="form-group"><label>Weight (kg)</label><input type="number" name="weight_kg" step="0.01" value="<?= htmlspecialchars($product['weight_kg']) ?>"></div>
        <div class="form-group"><label>Material</label><input type="text" name="material" value="<?= htmlspecialchars($product['material']) ?>" placeholder="e.g., Cotton, Plastic, Wood"></div>
        <div class="form-group"><label>Color</label><input type="text" name="color" value="<?= htmlspecialchars($product['color']) ?>"></div>
        <div class="form-group"><label>Working Condition Description</label><textarea name="working_condition" rows="3" placeholder="e.g., Battery works for 2 hours..."><?= htmlspecialchars($product['working_condition']) ?></textarea></div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="packaging" name="has_original_packaging" value="1" <?= ($product['has_original_packaging'] == 1) ? 'checked' : '' ?>>
            <label for="packaging">Original Packaging Included</label>
        </div>
        <div class="form-group checkbox-group">
            <input type="checkbox" id="manual" name="has_manual" value="1" <?= ($product['has_manual'] == 1) ? 'checked' : '' ?>>
            <label for="manual">Manual/Instructions Included</label>
        </div>
        
        <button type="submit" class="btn">Update Item</button>
    </form>
</div>
<?php include 'templates/footer.php'; ?>