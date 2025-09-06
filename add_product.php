<?php
include 'templates/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$errors = [];
$categories = ['Electronics', 'Furniture', 'Clothing', 'Books', 'Other'];
$conditions = ['New', 'Used - Like New', 'Used - Good', 'Used - Fair'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id'];
    
    
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

    if (empty($title) || empty($description) || empty($category) || empty($price)) {
        $errors[] = "Title, Description, Category, and Price are required.";
    }

    
    $uploaded_image_paths = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024;

    
    if (isset($_FILES['product_images']) && count(array_filter($_FILES['product_images']['name'])) < 2) {
        $errors[] = "You must upload at least 2 images.";
    } else {
        $file_count = count($_FILES['product_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $file_name = $_FILES['product_images']['name'][$i];
            $file_tmp_name = $_FILES['product_images']['tmp_name'][$i];
            $file_size = $_FILES['product_images']['size'][$i];
            $file_error = $_FILES['product_images']['error'][$i];
            $file_type = $_FILES['product_images']['type'][$i];

            if ($file_error === 0) {
                if ($file_size > $max_size) {
                    $errors[] = "File '{$file_name}' is too large! Maximum size is 5 MB.";
                } elseif (!in_array($file_type, $allowed_types)) {
                    $errors[] = "Invalid file type for '{$file_name}'! Only JPG, JPEG, and PNG are allowed.";
                } else {
                    
                    $new_file_name = uniqid('', true) . '_' . basename($file_name);
                    $upload_path = 'uploads/' . $new_file_name;

                    if (move_uploaded_file($file_tmp_name, $upload_path)) {
                        $uploaded_image_paths[] = $new_file_name;
                    } else {
                        $errors[] = "Sorry, there was an error uploading '{$file_name}'.";
                    }
                }
            }
        }
    }
    


    if (empty($errors)) {
        
        $image_paths_string = implode(',', $uploaded_image_paths);

        $sql = "INSERT INTO products (user_id, title, description, category, price, quantity, product_condition, year_of_manufacture, brand, model, dimensions, weight_kg, material, color, has_original_packaging, has_manual, working_condition, image_paths) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param("isssdisisssdsiiiss", 
            $user_id, $title, $description, $category, $price, $quantity, $product_condition, $year_of_manufacture, $brand, $model, $dimensions, $weight_kg, $material, $color, $has_original_packaging, $has_manual, $working_condition, $image_paths_string);

        if ($stmt->execute()) {
            header("Location: my_listings.php?status=added");
            exit();
        } else {
            $errors[] = "Failed to add product: " . $stmt->error;
        }
    }
}
?>
<div class="form-container">
    <h2>Add a New Product</h2>
    <?php if (!empty($errors)): ?><div class="alert error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Product Images (minimum 2, JPG/PNG only)*</label>
            <input type="file" name="product_images[]" multiple required>
        </div>

        <div class="form-group"><label>Product Title*</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Product Category*</label>
            <select name="category" required>
                <?php foreach ($categories as $cat) echo "<option value='$cat'>$cat</option>"; ?>
            </select>
        </div>
        <div class="form-group"><label>Product Description*</label><textarea name="description" rows="4" required></textarea></div>
        <div class="form-group"><label>Price (₹)*</label><input type="number" name="price" step="0.01" required></div>
        <div class="form-group"><label>Quantity*</label><input type="number" name="quantity" value="1" required></div>
        
        <hr class="section-divider">
        
        <h3>More Details (Optional)</h3>
        <div class="form-group"><label>Condition</label>
            <select name="product_condition">
                <option value="">-- Select Condition --</option>
                <?php foreach ($conditions as $con) echo "<option value='$con'>$con</option>"; ?>
            </select>
        </div>
        <div class="form-group"><label>Year of Manufacture</label><input type="number" name="year_of_manufacture" placeholder="e.g., 2021"></div>
        <div class="form-group"><label>Brand</label><input type="text" name="brand"></div>
        <div class="form-group"><label>Model</label><input type="text" name="model"></div>
        <div class="form-group"><label>Dimensions (L x W x H)</label><input type="text" name="dimensions" placeholder="e.g., 15x10x5 cm"></div>
        <div class="form-group"><label>Weight (kg)</label><input type="number" name="weight_kg" step="0.01"></div>
        <div class="form-group"><label>Material</label><input type="text" name="material" placeholder="e.g., Cotton, Plastic, Wood"></div>
        <div class="form-group"><label>Color</label><input type="text" name="color"></div>
        <div class="form-group"><label>Working Condition Description</label><textarea name="working_condition" rows="3" placeholder="e.g., Battery works for 2 hours, screen has a minor scratch."></textarea></div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="packaging" name="has_original_packaging" value="1">
            <label for="packaging">Original Packaging Included</label>
        </div>
        <div class="form-group checkbox-group">
            <input type="checkbox" id="manual" name="has_manual" value="1">
            <label for="manual">Manual/Instructions Included</label>
        </div>
        
        <button type="submit" class="btn">Add Item</button>
    </form>
</div>
<?php include 'templates/footer.php'; ?>