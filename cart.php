<?php
include 'templates/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $product_id_to_remove = $_GET['remove'];
    if (isset($_SESSION['cart']) && ($key = array_search($product_id_to_remove, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
    }
    header("Location: cart.php");
    exit();
}

$cart_items = $_SESSION['cart'] ?? [];
$products = []; 
$total_price = 0;

if (!empty($cart_items)) {
    $placeholders = implode(',', array_fill(0, count($cart_items), '?'));
    $stmt = $conn->prepare("SELECT id, title, price, image_paths, user_id as seller_id FROM products WHERE id IN ($placeholders) AND status = 'available'");
    $stmt->bind_param(str_repeat('i', count($cart_items)), ...$cart_items);
    $stmt->execute(); 
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) { 
        $products[] = $row; 
        $total_price += $row['price']; 
    }
}


if (isset($_POST['request_purchase']) && !empty($products)) {
    $buyer_id = $_SESSION['user_id'];
    $conn->begin_transaction();
    try {
        foreach ($products as $p) {
            $product_id = $p['id'];
            $seller_id = $p['seller_id'];

            
            $order_stmt = $conn->prepare("INSERT INTO orders (product_id, buyer_id, seller_id) VALUES (?, ?, ?)");
            $order_stmt->bind_param("iii", $product_id, $buyer_id, $seller_id);
            $order_stmt->execute();
            
            
            $product_stmt = $conn->prepare("UPDATE products SET status = 'pending_approval' WHERE id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();

            
            $seller_info_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
            $seller_info_stmt->bind_param("i", $seller_id);
            $seller_info_stmt->execute();
            $seller_email = $seller_info_stmt->get_result()->fetch_assoc()['email'];
            
            if ($seller_email) {
                $subject = "You have a new purchase request on Re-Bayan!";
                $mail_message = "Hi there,\n\nA user wants to buy your product: '{$p['title']}'.\n\nPlease log in to your Rebayan account and visit the 'Manage Requests' page to review and respond.";
                $headers = "From: no-reply@rebayan.com";
                mail($seller_email, $subject, $mail_message, $headers);
            }
            
            
            $notification_message = $_SESSION['username'] . " wants to buy your product: '" . $p['title'] . "'.";
            $notification_link = "manage_requests.php";
            $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
            $notify_stmt->bind_param("iss", $seller_id, $notification_message, $notification_link);
            $notify_stmt->execute();
        }
        $conn->commit();
        $_SESSION['cart'] = [];
        header("Location: my_orders.php?status=requested"); 
        exit();
    } catch (Exception $e) { 
        $conn->rollback(); 
        echo "<div class='alert error'>Request failed: " . $e->getMessage() . "</div>"; 
    }
}
?>

<div class="page-header"><h1>Your Shopping Cart</h1></div>
<div class="cart-container">
    <?php if (!empty($products)): ?>
    <div class="cart-items">
        <?php foreach ($products as $product): ?>
            <?php
            $images = !empty($product['image_paths']) ? explode(',', $product['image_paths']) : [];
            $thumbnail = !empty($images) ? $images[0] : 'placeholder.png';
            ?>
            <div class="cart-item-card">
                <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                <div class="cart-item-details">
                    <h4><a href="product_detail.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['title']) ?></a></h4>
                    <p>₹<?= htmlspecialchars($product['price']) ?></p>
                </div>
                <a href="cart.php?remove=<?= $product['id'] ?>" class="cart-item-remove" title="Remove Item">&times;</a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="cart-summary">
        <h3>Cart Total: ₹<?= number_format($total_price, 2) ?></h3>
        <form method="POST" action="cart.php">
            <button type="submit" name="request_purchase" class="btn btn-large">Request to Purchase</button>
        </form>
    </div>
    <?php else: ?>
        <p style="text-align:center;">Your cart is empty. <a href="index.php">Continue shopping!</a></p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>