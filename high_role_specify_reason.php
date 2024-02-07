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

// Retrieve username from session
$username = $_SESSION['username'];

// Check if event ID is provided in the URL
if (!isset($_GET['event_id'])) {
    // Redirect if event ID is not provided
    header("Location: high_role_dashboard.php");
    exit();
}

$eventId = $_GET['event_id'];

// Handle form submission
if (isset($_POST['submit_reason'])) {
    $reason = $_POST['reason'];
    try {
        // Update qevents table to set qevent_reason
        $stmt = $pdo->prepare("UPDATE qevents SET qevent_reason = ? WHERE event_id = ?");
        $stmt->execute([$reason, $eventId]);
        
        // Redirect back to the dashboard after updating the reason
        header("Location: high_role_dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Error updating event reason: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mid_role_dashboard.css">
    <title>Specify Reason</title>
</head>
<body>
    <div class="container">
        <h2>Specify Reason for Cancellation</h2>
        <form method="post">
            <textarea name="reason" placeholder="Enter reason for cancellation" required></textarea>
            <input type="submit" name="submit_reason" value="Submit Reason">
        </form>
    </div>
</body>
</html>
