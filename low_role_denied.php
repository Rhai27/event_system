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

if (isset($_GET['id']) && isset($_GET['username']) && isset($_GET['reason'])) {
    $eventId = $_GET['id'];
    $authorUsername = $_GET['username'];
    $reason = $_GET['reason'];

    try {
        // Get the user ID from the users table
        $stmtUserId = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtUserId->execute([$username]);
        $user = $stmtUserId->fetch();

        // Check if the user exists
        if ($user) {
            $userId = $user['id'];

            // Insert data into aevent table with Denied status and reason
            $insertSql = "INSERT INTO aevent (id, qevent_id, a_status, canceled_reason) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertSql);

            // Assuming you have a column named 'qevent_id' in the events table
            $stmt->execute([$userId, $eventId, 'Denied', $reason]);

            // Redirect back to the dashboard or any other page
            header("Location: low_role_dashboard.php");
            exit();
        } else {
            echo "User not found.";
        }
    } catch (PDOException $e) {
        echo "Error processing denial: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
