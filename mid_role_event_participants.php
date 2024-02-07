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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mid_role_dashboard.css">
    <title>Dashboard</title>

    <script>
        function confirmDenied(eventId, username) {
            var reason = prompt("Please enter the reason for denial:");

            if (reason !== null && reason.trim() !== "") {
                // Redirect to the denial processing script with event ID, username, and reason
                window.location.href = low_role_denied.php?id=${eventId}&username=${username}&reason=${encodeURIComponent(reason)};
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var deniedButtons = document.querySelectorAll('.denied-button');

            deniedButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Extract event ID and username from the data attributes
                    var eventId = this.getAttribute('data-event-id');
                    var username = this.getAttribute('data-username');

                    // Call the confirmation function
                    confirmDenied(eventId, username);
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="nav_bar">
            <div class="lnk_container">
                <a href="mid_role_dashboard.php">Events</a>
                <a href="mid_role_pending_events.php">Pending Events</a>
                <a href="mid_role_add_event.php">Create an Event</a>
                <a href="mid_role_event_participants.php">Participants</a>
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
                <form method="post" id="logout_btn_form">
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
                            <th>Attending</th>
                            <th>Not Attending</th>
                            <th>Options</th>
                        </tr>";
                foreach ($result as $row) {
                    // Subquery to count accepted events for this particular event_id
                    $subqueryAccepted = "SELECT COUNT(*) AS count FROM aevent WHERE a_status = 'accepted' AND qevent_id = ?";
                    $stmtAccepted = $pdo->prepare($subqueryAccepted);
                    $stmtAccepted->execute([$row['event_id']]);
                    $countResultAccepted = $stmtAccepted->fetch(PDO::FETCH_ASSOC);
                    $acceptedCount = $countResultAccepted['count'];

                    // Subquery to count denied events for this particular event_id
                    $subqueryDenied = "SELECT COUNT(*) AS count FROM aevent WHERE a_status = 'denied' AND qevent_id = ?";
                    $stmtDenied = $pdo->prepare($subqueryDenied);
                    $stmtDenied->execute([$row['event_id']]);
                    $countResultDenied = $stmtDenied->fetch(PDO::FETCH_ASSOC);
                    $deniedCount = $countResultDenied['count'];

                    $viewButton = "<a href='mid_role_decline_reasons.php?id={$row['event_id']}&username={$row['username']}' class='edit-button'>View Reasons</a>";
                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>{$row['title']}</td>
                            <td>{$acceptedCount}</td>
                            <td>{$deniedCount}</td>
                            <td>$viewButton</td>
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