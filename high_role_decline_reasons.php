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

// Get the event ID from the URL
$event_id = isset($_GET['id']) ? $_GET['id'] : null;

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
    <title>View Reasons</title>
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
                <form method="post" id="logout_btn_form">
                    <input type="submit" name="logout" value="Log Out" id="logout">
                </form>
            </div>
        </div>

        <div id="tbl_container">
            <?php
            try {
                // Retrieve data from the aevent and users tables for the specific event
                $sql = "SELECT users.username, aevent.canceled_reason
                        FROM aevent
                        INNER JOIN users ON aevent.id = users.id
                        WHERE aevent.qevent_id = ? AND aevent.a_status = 'denied'";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$event_id]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<table>
                        <tr>
                            <th>Name</th>
                            <th>Reason of Not Attending</th>
                        </tr>";
                foreach ($result as $row) {
                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>{$row['canceled_reason']}</td>
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