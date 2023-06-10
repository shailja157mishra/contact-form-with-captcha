<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "contact_form";

// Create a new database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Verify the reCAPTCHA response
$recaptchaResponse = $_POST['g-recaptcha-response'];
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => '6Le4zoUmAAAAAMl7g_3ne9kwSCQQX-t-uM5WCGTp',
    'response' => $recaptchaResponse
);
$options = array(
    'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);
$context = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captchaResponse = json_decode($verify);

// Check if the reCAPTCHA verification was successful
if ($captchaResponse && $captchaResponse->success) {
    // Process the form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Sanitize the form data to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $message = mysqli_real_escape_string($conn, $message);

    // Insert the form data into the database
    $query = "INSERT INTO contacts(name, email, message) VALUES ('$name', '$email', '$message')";

    if (mysqli_query($conn, $query)) {
        // Redirect to the success page
        header("Location: success.html");
        exit;
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
} else {
    // Handle the case when reCAPTCHA verification fails
    echo "reCAPTCHA verification failed. Please try again.";
}

// Close the database connection
mysqli_close($conn);
?>
