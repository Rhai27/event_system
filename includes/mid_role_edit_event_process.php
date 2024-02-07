<?php
session_start();

include 'db_connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = connectDB();

    // Check if all required fields are present
    if (isset($_POST['title'], $_POST['date'], $_POST['location'], $_POST['description'], $_POST['event_id'])) {
        // Prepare data for insertion
        $title = $_POST['title'];
        $date = $_POST['date'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $event_id = $_POST['event_id'];

        // Update the event details in the events table
        try {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, date = ?, location = ?, description = ? WHERE event_id = ?");
            $stmt->execute([$title, $date, $location, $description, $event_id]);
        } catch (PDOException $e) {
            echo "Error updating event details: " . $e->getMessage();
        }

        // Update qevent_status to "pending" in qevents table
        try {
            $stmt = $pdo->prepare("UPDATE qevents SET qevent_status = 'Pending' WHERE event_id = ?");
            $stmt->execute([$event_id]);
        } catch (PDOException $e) {
            echo "Error updating qevent_status: " . $e->getMessage();
        }

        // Redirect back to the dashboard or any other desired page
        header("Location: ../mid_role_pending_events.php");
        exit();
    } else {
        echo "All fields are required.";
    }
} else {
    // Redirect to the appropriate page if accessed directly
    header("Location: ../index.html");
    exit();
}
?>
