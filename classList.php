<?php

declare(strict_types=1);

require 'database_requests.php';

validateCookie(); // Check if the user is logged in

// Get the student id from the cookie and sanitize it
$id = (int)htmlspecialchars($_COOKIE['id']);
$firstName = htmlspecialchars($_COOKIE['firstName']);
$lastName = htmlspecialchars($_COOKIE['lastName']);

// Get the student's class list from the database
$user_class_ids = getClassList($id);

// Get the students in the class list
$user_classes = getStudents($user_class_ids);

closeConnection();

// Add Header
include 'header.php';
echo getHeader('Class List');

echo "Classlist Info For: " . $firstName . " " . $lastName . "<br>";

foreach ($user_classes as $course => $class_list) {
    echo "<hr>";
    echo "Course: " . $course . "<br>";
    foreach ($class_list as $student) {
        echo $student['id'] . ", " . $student['firstName'] . " " . $student['lastName'] . "<br>";
    }
    echo "<br>";
}
echo "<hr>";

// Add Footer
include 'footer.php';

?>