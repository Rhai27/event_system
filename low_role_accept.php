<?php
include 'includes/db_connection.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Establish database connection
$pdo = connectDB();

// Retrieve username and user ID from session
$username = $_SESSION['username'];

if (isset($_GET['id']) && isset($_GET['username'])) {
    $eventId = $_GET['id'];
    $authorUsername = $_GET['username'];

    try {
        // Get the user ID from the users table
        $stmtUserId = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtUserId->execute([$username]);
        $user = $stmtUserId->fetch();

        // Check if the user exists
        if ($user) {
            $userId = $user['id'];

            // Insert data into aevents table
            $insertSql = "INSERT INTO aevent (id, qevent_id, a_status) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($insertSql);

            // Assuming you have a column named 'qevent_id' in the events table
            $stmt->execute([$userId, $eventId, 'Accepted']);

            // Redirect back to the dashboard or any other page
            header("Location: low_role_dashboard.php");
            exit();
        } else {
            echo "User not found.";
        }
    } catch (PDOException $e) {
        echo "Error accepting event: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
