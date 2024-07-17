<?php

declare(strict_types=1);

require 'database_requests.php';

validateCookie(); // Check if the user is logged in

// Get student information from the cookie and sanitize it
$id = (int)htmlspecialchars($_COOKIE['id']);

// Get the student's grades from the database
$grades = getGrades($id);
closeConnection();

// Add Header
include 'header.php';
echo getHeader('Grades');

// Display the student's grades
echo "Student ID: " . $id . "<br>";
echo "Name: " . $_COOKIE['firstName'] . " " . $_COOKIE['lastName'] . "<br><br>";
foreach ($grades as $grade) {
    echo "<hr>";
    echo "Course: " . $grade['course'] . "<br>";
    echo "Test 1: " . $grade['test1'] . "<br>";
    echo "Test 2: " . $grade['test2'] . "<br>";
    echo "Test 3: " . $grade['test3'] . "<br>";
    echo "Final Exam: " . $grade['finalExam'] . "<br>";
    echo "Final Grade: " . $grade['finalGrade'] . "<br><br>";
}
echo "<hr>";

// Add Footer
include 'footer.php';

?>
