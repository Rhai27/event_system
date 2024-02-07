<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mid_role_edit_event.css">
    <title>Dashboard</title>
</head>
<body>
    <?php
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: index.html");
        exit();
    }

    // Establish database connection
    include 'includes/db_connection.php'; // Include the database connection script

    $pdo = connectDB(); // Connect to the database

    $username = $_SESSION['username'];

    // Logout logic
    if (isset($_POST['logout'])) {
        // JavaScript confirmation prompt
        echo "<script>
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.html';
                }
            </script>";
    }

    // Retrieve user ID from database
    $user_id = null;
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user) {
            $user_id = $user['id'];
        }
    } catch (PDOException $e) {
        echo "Error fetching user ID: " . $e->getMessage();
    }
    ?>

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
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($user['profile_image']) . '" class="profile_image" alt="Profile Image">';
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

        <!-- filled form php -->
        <?php
        try {
        if (isset($_GET['id'])) {
            $eventId = $_GET['id'];
            $sql = "SELECT e.title, e.date, e.location, e.description, e.event_id, q.qevent_reason, q.qevent_status 
                    FROM events e 
                    JOIN qevents q ON e.event_id = q.event_id 
                    WHERE e.event_id = :event_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();

            $eventData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($eventData) {
        ?>
                <!-- Your dashboard content -->
                <div class="form_container">
                    <form action="includes/mid_role_edit_event_process.php" method="post">
                        <div class="form_d5">
                            <div class="form_d1">
                                <div class="form_d2">
                                    <label for="title">Event Title:</label>
                                    <input type="text" id="title" name="title"
                                        value="<?php echo $eventData['title']; ?>" readonly>
                                </div>

                                <div class="form_d3">
                                    <label for="date">Event Date:</label>
                                    <input type="date" id="date" name="date"
                                        value="<?php echo $eventData['date']; ?>" readonly>
                                </div>
                            </div>

                            <div class="form_d4">
                                <label for="location">Event Location:</label>
                                <input type="text" id="location" name="location"
                                    value="<?php echo $eventData['location']; ?>" readonly>

                                <label for="description">Event Description:</label>
                                <textarea id="description" name="description" rows="4" cols="50" readonly><?php echo $eventData['description']; ?></textarea>
                                
                                <?php if ($eventData['qevent_status'] === "Cancelled" || $eventData['qevent_status'] === "Declined"): ?>

                                    <label for="qevent_reason">Reason:</label>
                                    <textarea id="qevent_reason" name="qevent_reason" rows="4" cols="50" readonly><?php echo $eventData['qevent_reason']; ?></textarea>
                                <?php else: ?>
                                    <!-- Hidden field for event ID -->
                                    <input type="hidden" name="event_id" value="<?php echo $eventData['event_id']; ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
        <?php
            } else {
                echo "<p>User not found.</p>";
            }
            
        }
        } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        }
        ?>


    </div>
</body>
</html>
