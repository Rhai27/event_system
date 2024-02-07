<?php
// Establish database connection
$dsn = 'mysql:host=localhost;dbname=event_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $image = file_get_contents($_FILES['image']['tmp_name']); // Read image file contents

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('Username already taken. Please choose a different username.'); window.location.href = '../signup.html';</script>";
        exit();
    }

    // Insert user data into database
    $sql = "INSERT INTO users (username, email, role, password, profile_image) VALUES (?, ?, ?, ?, ?)";
    $stmt= $pdo->prepare($sql);
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $email);
    $stmt->bindParam(3, $role);
    $stmt->bindParam(4, $hashed_password);
    $stmt->bindParam(5, $image, PDO::PARAM_LOB); // Bind image data as a LOB parameter
    $stmt->execute();

    echo "<script>alert('Sign up successful'); window.location.href = '../index.html';</script>";
    exit();
}
?>
