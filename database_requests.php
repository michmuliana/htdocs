<?php

declare(strict_types=1);

// Backend for the database requests

$env = parse_ini_file('../.env');

$servername = $env['DB_HOST'];
$username = $env['DB_USER'];
$password = $env['DB_PASS'];
$dbname = $env['DB_NAME'];

if (!isset($servername, $username, $password, $dbname)) {
    die("Environment variables not set");
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->select_db($dbname);

function getClassList($id) {
    /*
    *  Get the list of classes for a student
    *  @param string $id - the student's id
    *  @return array - the list of classes
    */
    global $conn;

    $stmt = $conn->prepare("SELECT course FROM course WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get the list of classes
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    $stmt->close();

    return $courses;
}

function getStudents($courses) {
    /*
    *  Get the list of students for a class
    *  @param array $courses - the list of classes
    *  @return array - the list of students
    */
    global $conn;

    $stmt = $conn->prepare(
        "SELECT s.id, s.firstName, s.lastName
        FROM students s
        JOIN course c ON s.id = c.id
        WHERE c.course = ?"
    );
    
    // Get the list of students for each class
    $user_classes = [];
    foreach ($courses as $course) {
        $class = $course['course'];
        $stmt->bind_param("s", $class);
        $stmt->execute();
        $result = $stmt->get_result();

        // Get the list of students
        $class_list = [];
        while ($row = $result->fetch_assoc()) {
            $class_list[] = $row;
        }

        // Add the list of students to the list of classes
        $user_classes[$class] = $class_list;
    }
    $stmt->close();

    return $user_classes;
}

function getGrades($id) {
    /*
    *  Get the grades for a student
    *  @param string $id - the student's id
    *  @return array - list of all individual grades and final grade
    */
    global $conn;

    $stmt = $conn->prepare("SELECT course, test1, test2, test3, finalExam, finalGrade FROM course WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the grades for each class
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();

    return $grades;
}

function getStudentInfo($id) {
    /*
    *  Get the student's first and last name
    *  @param string $id - the student's id
    *  @return array - the student's first and last name
    */
    global $conn;

    $stmt = $conn->prepare("SELECT firstName, lastName FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ensure that the student exists
    if ($result->num_rows === 0) {
        die("Invalid user id");
    }

    $row = $result->fetch_assoc();
    $firstName = $row['firstName'];
    $lastName = $row['lastName'];
    $stmt->close();

    return [
        'firstName' => $firstName,
        'lastName' => $lastName
    ];
}

function getPasswordHash($id) {
    /*
    *  Get the password hash for a student
    *  @param string $id - the student's id
    *  @return string - the password hash
    */
    global $conn;

    $stmt = $conn->prepare("SELECT passHash FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ensure the student exists
    if ($result->num_rows === 0) {
        die("Invalid user id");
    }
    $row = $result->fetch_assoc();
    $passHash = $row['passHash'];
    $stmt->close();

    return $passHash;
}

function closeConnection() {
    /*
    *  Close the connection to the database
    *  @return void
    */
    global $conn;

    $conn->close();
}

function validateCookie() {
    /*
    *  Validate the user's cookie
    *  @return bool - whether the cookie is valid
    */
    $valid = false;

    if (isset($_COOKIE['user'])) {
        $id = (int)htmlspecialchars($_COOKIE['id']);
        $user = htmlspecialchars($_COOKIE['user']);

        $passHash = getPasswordHash($id);

        if ($user == md5($id . $passHash)) {
            $valid = true;
        }
    }
        
    if (!$valid) {
        setcookie('user', '', time() - 3600);
        setcookie('id', '', time() - 3600);
        setcookie('firstName', '', time() - 3600);
        setcookie('lastName', '', time() - 3600);

        header('Location: login.php');
    }
}

function sendFeedback($id, $course, $feedback) {
    /*
    *  Send feedback for a course
    *  @param string $id - the student's id
    *  @param string $course - the course
    *  @param string $feedback - the feedback
    *  @return bool - whether the feedback was sent
    */
    global $conn;

    $stmt = $conn->prepare("UPDATE course SET feedback = ? WHERE id = ? AND course = ?");
    $stmt->bind_param("sis", $feedback, $id, $course);
    $return = $stmt->execute();
    $stmt->close();

    return $return;
}

function getTableNames() {
    /*
    *  Get the names of all tables in the database
    *  @return array - the names of all tables
    */
    global $conn;
    global $dbname;

    $stmt = $conn->prepare("SHOW TABLES");
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the names of all tables
    $tables = [];
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row["Tables_in_" . strtolower($dbname)];
    }
    $stmt->close();

    return $tables;
}

function getTableColumns($table) {
    /*
    *  Get the columns of a table
    *  @param string $table - the table
    *  @return array - the columns of the table
    */
    global $conn;

    $stmt = $conn->prepare("SHOW COLUMNS FROM " . $table);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the columns of the table
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $stmt->close();

    return $columns;
}

function search($table, $column, $value) {
    /*
    *  Search for a value in a column of a table
    *  @param string $table - the table
    *  @param string $column - the column
    *  @param string $value - the value
    *  @return array - the rows that match the search
    */
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM " . $table . " WHERE " . $column . " LIKE ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the rows that match the search
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

function searchStudents($firstName, $lastName, $type) {
    /*
    *  Search for students by first name and last name
    *  @param string $firstName - the first name
    *  @param string $lastName - the last name
    *  @param string $type - the search type
    *  @return array - the students that match the search
    */
    global $conn;

    // Search for students based on the inputs / lack thereof
    // Sets the Statement and binds parameters based on the search params and type
    if ($firstName == '' && $lastName == '') {
        $stmt = $conn->prepare("SELECT firstName, lastName FROM students");
        $stmt->execute();
    } else {
        if ($type == 'contains') {
            $firstName = '%' . $firstName . '%';
            $lastName = '%' . $lastName . '%';
        } else if ($type == 'startsWith') {
            $firstName = $firstName . '%';
            $lastName = $lastName . '%';
        } else {
            die("Invalid search type");
        }

        if ($firstName == '') {
            $stmt = $conn->prepare("SELECT firstName, lastName FROM students WHERE lastName LIKE ?");
            $stmt->bind_param("s", $lastName);
            $stmt->execute();
        } else if ($lastName == '') {
            $stmt = $conn->prepare("SELECT firstName, lastName FROM students WHERE firstName LIKE ?");
            $stmt->bind_param("s", $firstName);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT firstName, lastName FROM students WHERE firstName LIKE ? AND lastName LIKE ?");
            $stmt->bind_param("ss", $firstName, $lastName);
            $stmt->execute();
        }
    } 
    
    $result = $stmt->get_result();
    
    // Get the students that match the search
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

function searchCourses($prefix, $code) {
    /*
    *  Search for courses by prefix and code
    *  @param string $prefix - the prefix
    *  @param string $code - the code
    *  @return array - the courses that match the search
    */
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM courseindex WHERE course LIKE ?");

    // Search for courses based on the inputs / lack thereof
    // Sets the Statement and binds parameters based on the search params
    if ($prefix == '' && $code == '') {
        $value = '%';
    } else if ($prefix == '') {
        $value = '%' . $code . '%';
    } else if ($code == '') {
        $value = $prefix . '%';
    } else {
        $value = $prefix . $code . '%';
    }
    
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the courses that match the search
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

function registerCourse($id, $course) {
    /*
    *  Register a student for a course
    *  @param string $id - the student's id
    *  @param string $course - the course
    *  @return bool - whether the student was registered for the course
    */
    global $conn;

    // Check that the course exists
    $stmt = $conn->prepare("SELECT * FROM courseindex WHERE course = ?");
    $stmt->bind_param("s", $course);
    $stmt->execute();
    $result = $stmt->get_result();

    // Only continue if the course exists
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }

    // Check that the student is not already registered for the course
    $stmt = $conn->prepare("SELECT * FROM course WHERE id = ? AND course = ?");
    $stmt->bind_param("is", $id, $course);
    $stmt->execute();
    $result = $stmt->get_result();

    // Only continue if the student is not already registered for the course
    if ($result->num_rows > 0) {
        $stmt->close();
        return false;
    }

    // Register the student for the course
    $stmt = $conn->prepare("INSERT INTO course (id, course) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $course);
    $return = $stmt->execute();
    $stmt->close();

    return $return;
}

function dropCourse($id, $course) {
    /*
    *  Drop a course for a student
    *  @param string $id - the student's id
    *  @param string $course - the course
    *  @return bool - whether the student was dropped from the course
    */
    global $conn;

    // Confirm that the student is registered for the course
    $stmt = $conn->prepare("SELECT * FROM course WHERE id = ? AND course = ?");
    $stmt->bind_param("is", $id, $course);
    $stmt->execute();
    $result = $stmt->get_result();

    // Only continue if the student is registered for the course
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }

    // Drop the course
    $stmt = $conn->prepare("DELETE FROM course WHERE id = ? AND course = ?");
    $stmt->bind_param("is", $id, $course);
    $return = $stmt->execute();
    $stmt->close();

    return $return;
}

function dropOut($id, $passHash) {
    /*
    *  Drop a student from all courses and delete the student
    *  @param string $id - the student's id
    *  @param string $passHash - the student's password hash
    *  @return bool - whether the student was dropped from all courses and deleted
    */
    global $conn;

    // Confirm that the student exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Only continue if the student exists
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }

    // Confirm that the password is correct
    $stmt = $conn->prepare("SELECT passHash FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Only continue if the password is correct
    if ($row['passHash'] !== $passHash) {
        $stmt->close();
        return false;
    }

    // Drop the student from all courses
    $stmt = $conn->prepare("DELETE FROM course WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Drop the student
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $return = $stmt->execute();
    $stmt->close();

    return $return;
}

?>