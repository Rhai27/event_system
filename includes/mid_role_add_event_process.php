<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

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

// Retrieve form data
$title = $_POST['title'];
$date = $_POST['date'];
$location = $_POST['location'];
$description = $_POST['description'];
$user_id = $_POST['user_id']; // This is the user's ID
$qevent_status = $_POST['qevent_status'];

// Insert event into events table
try {
    $stmt = $pdo->prepare("INSERT INTO events (title, date, location, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $date, $location, $description]);
    
    // Get the ID of the inserted event
    $event_id = $pdo->lastInsertId();

    // Insert event_id and user_id into child entity
    $stmt = $pdo->prepare("INSERT INTO qevents (event_id, user_id, qevent_status) VALUES (?, ?, ?)");
    $stmt->execute([$event_id, $user_id, $qevent_status]);

    // Redirect back to dashboard or wherever appropriate
    header("Location:../mid_role_pending_events.php");
    exit();
} catch (PDOException $e) {
    echo "Error inserting event: " . $e->getMessage();
}
?>
