<?php

declare(strict_types=1);

// .env file is located up one directory
$env = parse_ini_file('../.env');

// Get the database connection details from the environment
$servername = $env['DB_HOST'];
$username = $env['DB_USER'];
$password = $env['DB_PASS'];
$dbname = $env['DB_NAME'];

// Ensure the variables are set
if (!isset($servername, $username, $password, $dbname)) {
    die("Environment variables not set");
}

// D20 Roll
function d20Roll()
{
    return rand(1, 20);
}

echo "D20 Roll: " . d20Roll() . "<br><br>";

// SQLI Connection
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error) . "<br>";
} else {
    echo "Connected successfully" . "<br>";
}

// Drop the database
$sql = "DROP DATABASE IF EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database dropped successfully" . "<br>";
}

// Create the database
$sql = "CREATE DATABASE $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully" . "<br>";
} else {
    die("Error creating database: " . $conn->error . "<br>");
}

// Select the database for use
$conn->select_db($dbname);

// Create Users Table
$sql =
    "CREATE TABLE users (" .
    "user_id INT(9) UNSIGNED PRIMARY KEY," .
    "username VARCHAR(60) NOT NULL," .
    "first_name VARCHAR(30) NOT NULL," .
    "last_name VARCHAR(30) NOT NULL," .
    "password VARCHAR(255) NOT NULL" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Create Suppliers Table
$sql =
    "CREATE TABLE suppliers (" .
    "supplier_id INT(9) UNSIGNED PRIMARY KEY," .
    "supplier_name VARCHAR(60) NOT NULL," .
    "address VARCHAR(255) NOT NULL," .
    "phone VARCHAR(30) NOT NULL" .
    "email VARCHAR(60) NOT NULL" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table suppliers created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Create Products Table
$sql =
    "CREATE TABLE products (" .
    "product_id INT(9) UNSIGNED PRIMARY KEY," .
    "description VARCHAR(255) NOT NULL," .
    "product_name VARCHAR(60) NOT NULL," .
    "price DECIMAL(10,2) NOT NULL" .
    "quantity INT(9) UNSIGNED" .
    "status VARCHAR(30) NOT NULL" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table products created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Create Orders Table
$sql =
    "CREATE TABLE orders (" .
    "order_id INT(9) UNSIGNED," .
    "FOREIGN KEY (user_id) REFERENCES users(user_id)," .
    "FOREIGN KEY (product_id) REFERENCES products(product_id)," .
    "quantity INT(9) UNSIGNED," .
    "order_date timestamp DEFAULT CURRENT_TIMESTAMP," .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table orders created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// prepare and bind insert statement for users
$stmt = $conn->prepare("INSERT INTO users (user_id, username, first_name, last_name, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $username, $first_name, $last_name, $password);

// insert all students from the NameFile.txt
$filename = "data/Users.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $user_id = $line[0];
    $username = $line[1];
    $name = $line[2];
    $base_password = $line[3];
    $splitName = explode(" ", $name);
    $firstName = $splitName[0];
    $lastName = $splitName[1];
    $password = md5($base_password);
    $stmt->execute();
}

// close the statement
$stmt->close();


// prepare and bind insert statement for suppliers
$stmt = $conn->prepare("INSERT INTO suppliers (supplier_id, supplier_name, address, phone, email) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $supplier_id, $supplier_name, $address, $phone, $email);

// insert all suppliers from the Supplier.txt file
$filename = "data/Supplier.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $supplier_id = $line[0];
    $supplier_name = $line[1];
    $address = $line[2];
    $phone = $line[3];
    $email = $line[4];
    $stmt->execute();
}

// close the statement
$stmt->close();

// Add in the products to the products table
$stmt = $conn->prepare("INSERT INTO products (product_id, description, product_name, price, quantity, status, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssiss", $product_id, $description, $product_name, $price, $quantity, $status, $supplier_id);

// insert all products from the Product.txt file
$filename = "data/Product.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $product_id = $line[0];
    $description = $line[2];
    $product_name = $line[1];
    $price = $line[3];
    $quantity = $line[4];
    $status = $line[5];
    $supplier_id = $line[6];
    $stmt->execute();
}

// close the statement
$stmt->close();


// Print the data from the supplier table
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<br>Supplier Table: " . "<br>";
    /* create html table */
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Supplier Name</th><th>Address</th><th>Phone</th><th>Email</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["supplier_id"] . "</td><td>" . $row["supplier_name"] . 
        "</td><td>" . $row["address"] . "</td><td>" . $row["phone"] . 
        "</td><td>" . $row["email"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results" . "<br>";
}

// Print the data from the products table
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<br>Products Table: " . "<br>";
    /* create html table */
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Description</th><th>Product Name</th><th>Price</th><th>Quantity</th><th>Status</th><th>Supplier ID</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["product_id"] . "</td><td>" . $row["description"] . 
        "</td><td>" . $row["product_name"] . "</td><td>" . $row["price"] . 
        "</td><td>" . $row["quantity"] . "</td><td>" . $row["status"] . 
        "</td><td>" . $row["supplier_id"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results" . "<br>";
}

// Close the connection
$conn->close();

?>