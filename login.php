<?php

declare(strict_types=1);

// Add Header
include 'header.php';
echo getHeader('Login');
    
echo '<!-- Login Form -->
<div class="flex center">
<form id="login_form" action="login_form.php" method="post">
    <h1>Student Login</h1>
    <div><input type="text" id="username" name="username" placeholder="Student ID" required></div>
    <div><input type="password" id="password" name="password" placeholder="Password" required></div>
    <div><button id="button_login" class="center">Login</button></div>
</form>
</div>
<!-- End of Login Form -->';

// Add Footer
include 'footer.php';

?>