<?php

declare(strict_types=1);

require 'database_requests.php';

// Check if the form was submitted and sanitize the input
if (isset($_POST['searchType'])) {
    $searchType = htmlspecialchars($_POST['searchType']);
} else {
    die ("Search Type Required.");
}

if ($searchType === "student") {
    // Get parameters from the form and sanitize them
    $firstName = htmlspecialchars($_POST['firstName']);
    $lastName = htmlspecialchars($_POST['lastName']);
    $type = htmlspecialchars($_POST['type']);

    // Search for students in the database
    $students = searchStudents($firstName, $lastName, $type);

    // Add Header
    include 'header.php';
    echo getHeader('Student Search');

    // Display the search results
    echo "<h1>Student Search Results</h1>";
    echo "<br>";

    if (count($students) === 0) {
        echo "No students found.";
    } else {
        foreach ($students as $student) {
            echo "<hr>";
            echo "Name: " . $student['firstName'] . " " . $student['lastName'] . "<br>";
            echo "<br>";
        }
    }

    // Add Footer
    include 'footer.php';
}

closeConnection();

?>