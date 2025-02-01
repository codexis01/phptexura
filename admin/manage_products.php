<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$host = "localhost"; 
$user = "root"; 
$pass = "";  
$dbname = "texura_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM products_table WHERE id='$id'");
    header("Location: manage_products.php");
    exit();
}

// Handle Product Addition with Image Upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];

    // File Upload Handling
    $targetDir = "uploads/";
    $image1 = $targetDir . basename($_FILES["image1"]["name"]);
    $image2 = $targetDir . basename($_FILES["image2"]["name"]);
    $image3 = $targetDir . basename($_FILES["image3"]["name"]);

    move_uploaded_file($_FILES["image1"]["tmp_name"], $image1);
    move_uploaded_file($_FILES["image2"]["tmp_name"], $image2);
    move_uploaded_file($_FILES["image3"]["tmp_name"], $image3);

    $sql = "INSERT INTO products_table (name, description, price, category, image1, image2, image3, stock)
            VALUES ('$name', '$description', '$price', '$category', '$image1', '$image2', '$image3', '$stock')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product added successfully!'); window.location='manage_products.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch All Products
$result = $conn->query("SELECT * FROM products_table");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h2 class="text-center">Admin Panel - Manage Products</h2>

    <!-- Product Add Form -->
    <h3>Add Product</h3>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="description" class="form-label">Description:</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="col-md-6">
            <label for="price" class="form-label">Price:</label>
            <input type="number" name="price" id="price" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="category" class="form-label">Category:</label>
            <input type="text" name="category" id="category" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="stock" class="form-label">Stock:</label>
            <input type="number" name="stock" id="stock" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="image1" class="form-label">Upload Image 1:</label>
            <input type="file" name="image1" id="image1" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="image2" class="form-label">Upload Image 2:</label>
            <input type="file" name="image2" id="image2" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label for="image3" class="form-label">Upload Image 3:</label>
            <input type="file" name="image3" id="image3" class="form-control" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Add Product</button>
        </div>
    </form>

    <hr>

    <!-- Product List -->
    <h3>Existing Products</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Images</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <img src="<?php echo $row['image1']; ?>" height="90px" width="200px" class="img-thumbnail">
                    <img src="<?php echo $row['image2']; ?>" height="90px" width="200px" class="img-thumbnail">
                    <img src="<?php echo $row['image3']; ?>" height="90px" width="200px" class="img-thumbnail">
                </td>
                <td>
                    <a href="manage_products.php?delete_id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure?');" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        <a href="admin_logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
