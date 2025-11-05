<?php
define('IS_ADMIN_PANEL', true);

// Load the core engine
require_once __DIR__ . '/../includes/init.php';

// Get the active theme from the database
$active_theme = get_active_theme($pdo);

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// --- LOGIC ---
$product = null;
$is_editing = false;
$message = '';
$current_image = null;

// Check if we are editing an existing product
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];
    $is_editing = true;

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_image = $product['image'] ?? null;

        if (!$product) {
            $_SESSION['message'] = "Product not found.";
            header('Location: products.php');
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching product: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image_filename = $current_image; // Default to the current image

    // Basic validation
    if (empty($name) || !is_numeric($price) || !is_numeric($stock)) {
        $message = "Please fill in all fields correctly.";
    } else {
        // --- IMAGE UPLOAD LOGIC ---
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];

            // Get file extension
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_extensions)) {
                // Create a unique name to prevent overwrites
                $new_file_name = uniqid('product_', true) . '.' . $file_ext;
                $upload_path = __DIR__ . '/../uploads/' . $new_file_name;

                // Move the file to the uploads directory
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_filename = $new_file_name;
                } else {
                    $message = "Error uploading the file.";
                }
            } else {
                $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            }
        }

        // If no image upload errors, proceed to save the product
        if (empty($message)) {
            try {
                if ($is_editing) {
                    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $stock, $image_filename, $product['id']]);
                    $_SESSION['message'] = "Product updated successfully!";
                } else {
                    $sql = "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $stock, $image_filename]);
                    $_SESSION['message'] = "Product created successfully!";
                }
                header('Location: products.php');
                exit();
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_editing ? 'Edit Product' : 'Add New Product'; ?> - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <!-- ADMIN MENU IS NOW INCLUDED -->
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1><?php echo $is_editing ? 'Edit Product' : 'Add New Product'; ?></h1>

    <?php if ($message): ?>
        <article style="background-color: #fcf0f1; border-left: 4px solid #d63638; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
            <?php echo htmlspecialchars($message); ?>
        </article>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

        <label for="price">Price ($)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>

        <label for="stock">Stock Quantity</label>
        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($product['stock'] ?? ''); ?>" required>

        <label for="product_image">Product Image</label>
        <input type="file" id="product_image" name="product_image" accept="image/*">
        <?php if ($is_editing && $current_image): ?>
            <p>Current image:</p>
            <img src="/uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Current Product Image" style="max-width: 150px; border: 1px solid #ddd; border-radius: 4px;">
        <?php endif; ?>

        <!-- BUTTONS ARE NOW WRAPPED IN A GROUP -->
        <div class="button-group">
            <button type="submit"><?php echo $is_editing ? 'Update Product' : 'Add Product'; ?></button>
            <a href="products.php" role="button" class="secondary">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>