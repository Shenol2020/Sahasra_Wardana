<?php
require_once 'config.php';
requireLogin();

if (!isUserType('farmer')) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name']);
    $description = sanitize($_POST['description']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $quantity_kg = floatval($_POST['quantity_kg']);
    $category = sanitize($_POST['category']);
    
    if (empty($product_name) || empty($description)) {
        $error = 'Please fill in all required fields';
    } elseif ($price_per_kg <= 0) {
        $error = 'Price must be greater than 0';
    } elseif ($quantity_kg <= 0) {
        $error = 'Quantity must be greater than 0';
    } else {
        $upload_result = uploadProductImage($_FILES['product_image']);
        
        if (!$upload_result['success'] && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = $upload_result['message'];
        } else {
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO products (farmer_id, product_name, description, price_per_kg, quantity_kg, category, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("issddss", 
                $_SESSION['user_id'],
                $product_name,
                $description,
                $price_per_kg,
                $quantity_kg,
                $category,
                $upload_result['path']
            );
            
            if ($stmt->execute()) {
                header('Location: dashboard.php?added=1');
                exit();
            } else {
                $error = 'Failed to add product. Please try again.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - FarmMarket</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

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
        .navbar a { color: var(--green-700); text-decoration: none; font-weight: 800; }
        .navbar a:hover { text-decoration: underline; }

        .container { max-width: 900px; margin: 0 auto; padding: 0 20px 40px; }

        .form-card {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
        }

        h1 { color: var(--green-700); margin-bottom: 8px; }
        .subtitle { color: var(--text-600); margin-bottom: 20px; }

        .alert-error {
            background: var(--danger-100);
            color: var(--danger-700);
            border: 1px solid #ffcdd2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; color: var(--text-900); font-weight: 700; font-size: 14px; }
        .required { color: #d32f2f; }

        input[type="text"], input[type="number"], textarea, select {
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

        textarea { resize: vertical; min-height: 110px; }

        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px dashed #dfe8df;
            border-radius: 10px;
            background: #fbfbfb;
            cursor: pointer;
        }
        .file-hint { font-size: 12px; color: var(--text-600); margin-top: 6px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .btn-group { display: flex; gap: 12px; margin-top: 22px; }

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
        .btn-primary { background: var(--green-700); color: #fff; }
        .btn-primary:hover { background: var(--green-600); }

        .btn-secondary {
            background: var(--brown-700);
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover { background: var(--brown-600); }

        @media (max-width: 768px) {
            .form-card { padding: 22px; }
            .form-row { grid-template-columns: 1fr; }
            .btn-group { flex-direction: column-reverse; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
    </nav>
    
    <div class="container">
        <div class="form-card">
            <h1>Add New Product</h1>
            <p class="subtitle">List a new product for buyers to discover</p>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">Product Name <span class="required">*</span></label>
                    <input type="text" id="product_name" name="product_name" placeholder="e.g., Red Onions (Jaffna) / Coconuts / Rice (Nadu)" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" placeholder="Describe freshness, grade, harvest date, and pickup/delivery details..." required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price_per_kg">Price per Kg (Rs) <span class="required">*</span></label>
                        <input type="number" id="price_per_kg" name="price_per_kg" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity_kg">Available Quantity (Kg) <span class="required">*</span></label>
                        <input type="number" id="quantity_kg" name="quantity_kg" step="0.01" min="0.01" placeholder="e.g., 100" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="vegetable">Vegetables</option>
                        <option value="fruit">Fruits</option>
                        <option value="grain">Grains</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*">
                    <p class="file-hint">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                </div>
                
                <div class="btn-group">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>