<?php

declare(strict_types=1);

require 'database_requests.php';

// Add Header
include 'header.php';
echo getHeader('Directory');

$firstName = "";
$lastName = "";
$type = "";


// Check if the form was submitted
if (isset($_POST['searchType'])) {
    $searchType = htmlspecialchars($_POST['searchType']);
}
if (isset($_POST['firstName'])) {
    $firstName = htmlspecialchars($_POST['firstName']);
}
if (isset($_POST['lastName'])) {
    $lastName = htmlspecialchars($_POST['lastName']);
}
if (isset($_POST['type'])) {
    $type = htmlspecialchars($_POST['type']);
}

// Search Directory for Students
echo '<!-- Student Search Form -->
<div class="flex center directory_search">
<form id="student_directory" action="directory.php" method="post">
    <h1>Student Directory</h1>
    <br>
    <div>
        <input type="hidden" id="searchType" name="searchType" value="student">
        <input type="text" id="firstName" name="firstName" placeholder="First Name" value="'.$firstName.'">
        <input type="text" id="lastName" name="lastName" placeholder="Last Name" value="'.$lastName.'">
        <select id="type" name="type" value="'.$type.'" required>
            <option value="startsWith">Starts With</option>
            <option value="contains">Contains</option>
        </select>
        <button type="submit" id="button_search" class="center">Search</button>
    </div>
</form>
</div>
<!-- End of Search Form -->';

if (isset($searchType)) {
    if ($searchType === "student") {
        // Get parameters from the form and sanitize them
        $firstName = htmlspecialchars($_POST['firstName']);
        $lastName = htmlspecialchars($_POST['lastName']);
        $type = htmlspecialchars($_POST['type']);

        // Search for students in the database
        $students = searchStudents($firstName, $lastName, $type);

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
    }
}

// Search Directory for Courses
echo '<!-- Course Search Form -->
<div class="flex center directory_search">
<form id="course_directory" action="directory.php" method="post">
    <h1>Course Directory</h1>
    <br>
    <div>
        <input type="hidden" id="searchType" name="searchType" value="course">
        <input type="text" id="prefix" name="prefix" placeholder="Department Prefix">
        <input type="text" id="code" name="code" placeholder="Course Number">
        <button id="button_search" class="center">Search</button>
    </div>
</form>
</div>
<!-- End of Search Form -->';

if (isset($searchType)) {
    if ($searchType === "course") {
        // Get parameters from the form and sanitize them
        $prefix = htmlspecialchars($_POST['prefix']);
        $code = htmlspecialchars($_POST['code']);

        // Search for courses in the database
        $courses = searchCourses($prefix, $code);

        // Display the search results
        echo "<h1>Course Search Results</h1>";
        echo "<br>";

        if (count($courses) === 0) {
            echo "No courses found.";
        } else {
            foreach ($courses as $course) {
                $prefix = substr($course['course'], 0, 2);
                $code = substr($course['course'], 2);

                echo "<hr>";
                echo "Course Code: " . $prefix . $code . " | ";
                echo "Course Name: " . $course['courseName'] . "<br>";
                echo "<br>";
            }
        }
    }
}

// Add Footer
include 'footer.php';