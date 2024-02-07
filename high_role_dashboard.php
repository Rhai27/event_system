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

// Logout logic
if (isset($_POST['logout'])) {
    echo "<script>
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'index.html';
            }
          </script>";
}

if (isset($_POST['cancel_event'])) {
    $eventId = $_POST['event_id'];

    try {
        // Update qevents table to set qevent_status to "deleted"
        $stmt = $pdo->prepare("UPDATE qevents SET qevent_status = 'Cancelled' WHERE event_id = ?");
        $stmt->execute([$eventId]);
    } catch (PDOException $e) {
        echo "Error updating event status: " . $e->getMessage();
    }

    // Redirect to the reason page
    header("Location: high_role_specify_reason.php?event_id=$eventId");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mid_role_dashboard.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="container">
        <div class="nav_bar">
            <div class="lnk_container">
                <a href="high_role_dashboard.php">Events</a>
                <a href="high_role_pending_events.php">Pending Events</a>
                <a href="high_role_event_participants.php">Participants</a>
            </div>

            <div class="user_container">
                <div class="username_container">
                <?php echo "$username"; ?>
            </div>

            <div class="user_image_container">
                <?php
                    try {
                        // Retrieve profile image blob from database
                        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE username = ?");
                        $stmt->execute([$username]);
                        $user = $stmt->fetch();

                        // Check if profile image exists and render it
                        if ($user && $user['profile_image']) {
                            // Render profile image
                            echo '<img src="data:image/jpeg;base64,'.base64_encode($user['profile_image']).'" class="profile_image" alt="Profile Image">';
                        } else {
                            // Render default profile image if no image found
                            echo '<img src="default_profile_image.jpg" class="profile-image" alt="Profile Image">';
                        }
                    } catch (PDOException $e) {
                        // Error handling for database queries
                        echo "Error fetching profile image: " . $e->getMessage();
                    }
                ?>
            </div>
                <form method="post" id=logout_btn_form>
                    <input type="submit" name="logout" value="Log Out" id="logout">
                </form>
            </div>
        </div>

        <div id="tbl_container">
            <?php
                try {
                    // Retrieve events data with usernames, event titles, and dates
                    $sql = "SELECT users.username, events.event_id, events.title, events.date, qevents.qevent_status 
                            FROM qevents 
                            INNER JOIN users ON qevents.user_id = users.id 
                            INNER JOIN events ON qevents.event_id = events.event_id
                            WHERE qevents.qevent_status IN ('Confirmed', 'Cancelled')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo "<table>
                            <tr>
                                <th>Author</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Options</th>
                            </tr>";
                    foreach ($result as $row) {
                        // Show edit button only if logged-in user is the author
                        $editButton = '';
                        $viewButton = '';
                        $cancelButton = '';
                        

                        if ($row['username'] !== $username) {
                            $viewButton = "<a href='other_high_role_edit_event.php?id={$row['event_id']}&username={$row['username']}' class='edit-button'>View</a>";
                        }

                        if ($row['qevent_status'] === 'Confirmed') {
                            $cancelButton = "<form method='post'>
                                            <input type='hidden' name='event_id' value='{$row['event_id']}'>
                                            <button type='submit' name='cancel_event' class='edit-button' onclick='return confirm(\"Are you sure you want to cancel this event?\")'>Cancel Event</button>
                                        </form>";
                        }

                        echo "<tr>
                                <td>{$row['username']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['qevent_status']}</td>
                                <td>$editButton $viewButton $cancelButton</td>
                            </tr>";
                    }
                    echo "</table>";
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            ?>

        </div>
    </div>
</body>
</html>
