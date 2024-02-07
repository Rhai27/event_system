<?php
session_start();

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

// Process login form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user data from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];

        // Check the role of the user
        $role = $user['role'];

        // Redirect to appropriate dashboard based on role
        if ($role === 'low') {
            header("Location: ../low_role_dashboard.php");
            exit();
        } elseif ($role === 'mid') {
            header("Location: ../mid_role_dashboard.php");
            exit();
        } elseif ($role === 'high') {
            header("Location: ../high_role_dashboard.php");
            exit();
        } else {
            // Unknown role, handle accordingly
            // You can add additional logic here if needed
            echo "<script>alert('Unknown role.'); window.location.href = '../index.html';</script>";
            exit();
        }
    } else {
        // Login failed
        echo "<script>alert('Invalid email or password.'); window.location.href = '../index.html';</script>";
    }
}
?>
