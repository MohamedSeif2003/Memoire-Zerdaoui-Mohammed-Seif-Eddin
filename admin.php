<?php
$host = 'localhost';
$dbname = 'alphapharm';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}


header('Content-Type: application/json');


function generateProductId($conn) {
    $prefix = "prod";
    $sql = "SELECT COUNT(*) as count FROM products";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1; 
    return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
        $product_id = $conn->real_escape_string($_GET['product_id']); 

        $query = "DELETE FROM products WHERE product_id = '$product_id'";
        if ($conn->query($query)) {
            $query = "SELECT product_id, desig, stock, sell, buy, exp, types, imag FROM products";
            $result = $conn->query($query);

            if ($result) {
                $products = [];
                while ($row = $result->fetch_assoc()) {
                    $products[] = [
                        'product_id' => $row['product_id'],
                        'desig' => $row['desig'],
                        'stock' => $row['stock'],
                        'sell' => $row['sell'],
                        'buy' => $row['buy'],
                        'exp' => $row['exp'],
                        'types' => $row['types'],
                        'imag' => $row['imag']
                    ];
                }

                echo json_encode(["success" => true, "message" => "Product deleted successfully.", "products" => $products]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to fetch updated products: " . $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete product: " . $conn->error]);
        }
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'get_product' && isset($_GET['product_id'])) {
        $product_id = $conn->real_escape_string($_GET['product_id']);

        $query = "SELECT product_id, desig, stock, sell, buy, exp, types, imag FROM products WHERE product_id = '$product_id'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(["success" => true, "product" => $product]);
        } else {
            echo json_encode(["success" => false, "message" => "Product not found."]);
        }
        exit;
    }

    $query = "SELECT product_id, desig, stock, sell, buy, exp, types, imag FROM products";

    // Check if sorting is requested
    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
        if ($sort === 'price_asc') {
            $query .= " ORDER BY sell ASC"; // Sort by price ascending
        } elseif ($sort === 'price_desc') {
            $query .= " ORDER BY sell DESC"; // Sort by price descending
        } elseif ($sort === 'alphabetical_asc') {
            $query .= " ORDER BY desig ASC"; // Sort by designation ascending
        } elseif ($sort === 'alphabetical_desc') {
            $query .= " ORDER BY desig DESC"; // Sort by designation descending
        }
    }

    $result = $conn->query($query);

    // Check if the query was successful
    if (!$result) {
        echo json_encode(["success" => false, "message" => "Failed to fetch products: " . $conn->error]);
        exit;
    }

    // Fetch the data and store it in an array
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'product_id' => $row['product_id'],
            'desig' => $row['desig'],
            'stock' => $row['stock'],
            'sell' => $row['sell'],
            'buy' => $row['buy'],
            'exp' => $row['exp'],
            'types' => $row['types'],
            'imag' => $row['imag']
        ];
    }

    // Return the products as JSON
    echo json_encode(["success" => true, "products" => $products]);
    exit;
}

// Handle POST requests (for adding or editing products)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'add'; // Determine if it's an edit or add action

    // Validate required fields
    if (empty($_POST['desig']) || empty($_POST['stock']) || empty($_POST['sell']) || empty($_POST['buy']) || empty($_POST['exp']) || empty($_POST['types'])) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Sanitize inputs
    $desig = $conn->real_escape_string($_POST['desig']);
    $stock = intval($_POST['stock']);
    $sell = floatval($_POST['sell']);
    $buy = floatval($_POST['buy']);
    $exp = $conn->real_escape_string($_POST['exp']);
    $types = $conn->real_escape_string($_POST['types']);
    $imag = ''; // Initialize image path

    // Handle image upload
    if (isset($_FILES['imag']) && $_FILES['imag']['error'] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES['imag']['name']);
        $imageTmpName = $_FILES['imag']['tmp_name'];
        $imagePath = 'Medications/' . $imageName;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($imageTmpName, $imagePath)) {
            echo json_encode(["success" => false, "message" => "Failed to upload image."]);
            exit;
        }

        $imag = $imagePath; // Set the image path
    }

    if ($action === 'edit') {
        // Edit product
        $product_id = $conn->real_escape_string($_POST['product_id']);

        // Build the SQL query
        $query = "UPDATE products SET 
                  desig = '$desig', 
                  stock = '$stock', 
                  sell = '$sell', 
                  buy = '$buy', 
                  exp = '$exp', 
                  types = '$types'";
        if ($imag) {
            $query .= ", imag = '$imag'";
        }
        $query .= " WHERE product_id = '$product_id'";

        // Execute the query
        if (!$conn->query($query)) {
            echo json_encode(["success" => false , "message" => "Failed to update product: " . $conn->error]);
            exit;
        }

        echo json_encode(["success" => true, "message" => "Product updated successfully."]);
    } else {
        // Add product
        $product_id = generateProductId($conn); // Generate the custom product_id

        $query = "INSERT INTO products (product_id, desig, stock, sell, buy, exp, types, imag) 
                  VALUES ('$product_id', '$desig', '$stock', '$sell', '$buy', '$exp', '$types', '$imag')";

        // Execute the query
        if (!$conn->query($query)) {
            echo json_encode(["success" => false, "message" => "Failed to add product: " . $conn->error]);
            exit;
        }

        echo json_encode(["success" => true, "message" => "Product added successfully."]);
    }
    exit;
}

// Close the database connection
$conn->close();
?>



