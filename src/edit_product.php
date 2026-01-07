<?php
require_once 'config.php';
requireLogin();

if (!isUserType('farmer')) {
    header('Location: dashboard.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$product_id = (int)$_GET['id'];
$conn = getDBConnection();

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND farmer_id = ?");
$stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$product = $result->fetch_assoc();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name']);
    $description = sanitize($_POST['description']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $quantity_kg = floatval($_POST['quantity_kg']);
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    
    if (empty($product_name) || empty($description)) {
        $error = 'Please fill in all required fields';
    } elseif ($price_per_kg <= 0 || $quantity_kg <= 0) {
        $error = 'Price and quantity must be greater than 0';
    } else {
        $image_path = $product['image_path'];
        
        // Handle new image upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = uploadProductImage($_FILES['product_image']);
            
            if (!$upload_result['success']) {
                $error = $upload_result['message'];
            } else {
                // Delete old image if exists
                if (!empty($product['image_path']) && file_exists($product['image_path'])) {
                    unlink($product['image_path']);
                }
                $image_path = $upload_result['path'];
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price_per_kg = ?, quantity_kg = ?, category = ?, status = ?, image_path = ? WHERE product_id = ? AND farmer_id = ?");
            $stmt->bind_param("ssddsssii", 
                $product_name,
                $description,
                $price_per_kg,
                $quantity_kg,
                $category,
                $status,
                $image_path,
                $product_id,
                $_SESSION['user_id']
            );
            
            if ($stmt->execute()) {
                header('Location: dashboard.php?updated=1');
                exit();
            } else {
                $error = 'Failed to update product';
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - FarmMarket</title>
    <style>
        :root {
            --green-700: #2e7d32;
            --green-600: #388e3c;
            --green-100: #e8f5e9;
            --brown-700: #6d4c41;
            --brown-600: #8d6e63;
            --brown-100: #efebe9;
            --earth-bg-1: #f1f8e9;
            --earth-bg-2: #f9f6ef;
            --text-900: #2b2b2b;
            --text-600: #5b5b5b;
            --danger-700: #b71c1c;
            --danger-100: #ffebee;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(165deg, var(--earth-bg-1) 0%, var(--earth-bg-2) 100%);
            color: var(--text-900);
        }
        
        .navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            margin-bottom: 30px;
            border-bottom: 4px solid var(--green-700);
        }
        
        .navbar a {
            color: var(--green-700);
            text-decoration: none;
            font-weight: 800;
        }
        .navbar a:hover { text-decoration: underline; }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        
        .form-card {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
        }
        
        h1 {
            color: var(--green-700);
            margin-bottom: 8px;
        }
        .subtitle {
            color: var(--text-600);
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: var(--danger-100);
            color: var(--danger-700);
            border: 1px solid #ffcdd2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-900);
            font-weight: 700;
            font-size: 14px;
        }
        
        .required {
            color: #d32f2f;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #dfe8df;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(56,142,60,0.15);
        }
        
        textarea {
            resize: vertical;
            min-height: 110px;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px dashed #dfe8df;
            border-radius: 10px;
            cursor: pointer;
            background: #fbfbfb;
        }
        
        .current-image {
            margin-top: 10px;
            max-width: 220px;
            border-radius: 8px;
        }
        .file-hint {
            font-size: 12px;
            color: var(--text-600);
            margin-top: 6px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }
        
        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-primary {
            background: var(--green-700);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--green-600);
        }
        
        .btn-secondary {
            background: var(--brown-700);
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: var(--brown-600);
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 22px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .btn-group {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
    </nav>
    
    <div class="container">
        <div class="form-card">
            <h1>Edit Product</h1>
            <p class="subtitle">Update your product information</p>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Product Name <span class="required">*</span></label>
                    <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price_per_kg">Price per Kg (Rs) <span class="required">*</span></label>
                        <input type="number" id="price_per_kg" name="price_per_kg" step="0.01" min="0.01" value="<?php echo $product['price_per_kg']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity_kg">Available Quantity (Kg) <span class="required">*</span></label>
                        <input type="number" id="quantity_kg" name="quantity_kg" step="0.01" min="0.01" value="<?php echo $product['quantity_kg']; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="vegetable" <?php echo $product['category'] == 'vegetable' ? 'selected' : ''; ?>>Vegetables</option>
                            <option value="fruit" <?php echo $product['category'] == 'fruit' ? 'selected' : ''; ?>>Fruits</option>
                            <option value="grain" <?php echo $product['category'] == 'grain' ? 'selected' : ''; ?>>Grains</option>
                            <option value="other" <?php echo $product['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Current image" class="current-image">
                        <p class="file-hint" style="margin-bottom: 10px;">Current image above. Upload a new one to replace it.</p>
                    <?php endif; ?>
                    <input type="file" id="product_image" name="product_image" accept="image/*">
                    <p class="file-hint">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                </div>
                
                <div class="btn-group">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>