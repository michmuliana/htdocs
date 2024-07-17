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

// Create Name Table
$sql =
    "CREATE TABLE students (" .
    "id INT(9) UNSIGNED PRIMARY KEY," .
    "name VARCHAR(60) NOT NULL," .
    "firstName VARCHAR(30) NOT NULL," .
    "lastName VARCHAR(30) NOT NULL," .
    "passHash VARCHAR(255) NOT NULL" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table students created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Create Course Table // finalGrade is a virtual column (test1*0.2 + test2*0.2 + test3*0.2 + finalExam*0.4)
$sql =
    "CREATE TABLE course (" .
    "id INT(9) UNSIGNED," .
    "FOREIGN KEY (id) REFERENCES students(id)," .
    "course VARCHAR(5) NOT NULL," .
    "test1 float(5,1)," .
    "test2 float(5,1)," .
    "test3 float(5,1)," .
    "finalExam float(5,1)," .
    "feedback TEXT" .  // Adding the feedback column
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table course created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Add Virtual Column finalGrade to the course table
$sql = "ALTER TABLE course ADD finalGrade float(5,1) AS (test1*0.2 + test2*0.2 + test3*0.2 + finalExam*0.4)";
if ($conn->query($sql) === TRUE) {
    echo "Virtual column finalGrade added to course table" . "<br>";
} else {
    die("Error adding virtual column: " . $conn->error . "<br>");
}

// Create Course Index table
$sql =
    "CREATE TABLE courseIndex (" .
    "course VARCHAR(5) NOT NULL PRIMARY KEY," .
    "courseName VARCHAR(60)" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table courseIndex created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// Create Feedback Table
$sql =
    "CREATE TABLE feedback (" .
    "id INT(9) UNSIGNED," .
    "FOREIGN KEY (id) REFERENCES students(id)," .
    "course VARCHAR(5) NOT NULL," .
    "feedback TEXT NOT NULL" .
    ");";
if ($conn->query($sql) === TRUE) {
    echo "Table feedback created successfully" . "<br>";
} else {
    die("Error creating table: " . $conn->error . "<br>");
}

// prepare and bind insert statement for students
$stmt = $conn->prepare("INSERT INTO students (id, name, firstName, lastName, passHash) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $id, $name, $firstName, $lastName, $passHash);

// insert all students from the NameFile.txt
$filename = "data/NameFile.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $id = $line[0];
    $name = $line[1];
    $splitName = explode(" ", $name);
    $firstName = $splitName[0];
    $lastName = $splitName[1];
    # default password is the first 4 characters of the lastname and the last 4 characters of the id
    $defaultPassword = substr($lastName, 0, 4) . substr($id, -4);
    $passHash = md5($defaultPassword);
    $stmt->execute();
}

// close the statement
$stmt->close();


// prepare and bind insert statement for course
$stmt = $conn->prepare("INSERT INTO course (id, course, test1, test2, test3, finalExam, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdddds", $id, $course, $test1, $test2, $test3, $finalExam, $feedback); // Assuming feedback is a string

// insert all students from the CourseFile.txt
$filename = "data/CourseFile.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $id = $line[0];
    $course = $line[1];
    $test1 = $line[2];
    $test2 = $line[3];
    $test3 = $line[4];
    $finalExam = $line[5];
    $stmt->execute();
}

// close the statement
$stmt->close();

// Add in the Course Index Table data (get from CourseNames.txt)
$stmt = $conn->prepare("INSERT INTO courseIndex (course, courseName) VALUES (?, ?)");
$stmt->bind_param("ss", $course, $courseName);

$filename = "data/CourseNames.txt";
$lines = file($filename);
foreach ($lines as $line) {
    $line = trim($line);
    $line = explode(", ", $line);
    $course = $line[0];
    $courseName = $line[1];
    $stmt->execute();
}

// close the statement
$stmt->close();


// Print the data from the students table
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<br>Students Table: " . "<br>";
    /* create html table */
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>First Name</th><th>Last Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name"] . "</td><td>" . $row["firstName"] . "</td><td>" . $row["lastName"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results" . "<br>";
}

// Print the data from the course table
$sql = "SELECT * FROM course";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "<br>Course Table: " . "<br>";
    /* create html table */
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Course</th><th>Test 1</th><th>Test 2</th><th>Test 3</th><th>Final Exam</th><th>Final Grade</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["course"] . 
        "</td><td>" . $row["test1"] . "</td><td>" . $row["test2"] . 
        "</td><td>" . $row["test3"] . "</td><td>" . $row["finalExam"] . 
        "</td><td>" . $row["finalGrade"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results" . "<br>";
}

// Close the connection
$conn->close();

?>