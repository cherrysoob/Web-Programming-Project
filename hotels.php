<?php
require 'database.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hotels Page</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <header>
            <div class="logo">
                <img src="logo.jpeg" alt="HotelBooking Logo">
            </div>
            <div class="navigation">
                <nav>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a class="hotels-button" href="hotels.php">Hotels</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login/Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
        <main>
            <section class="hotels-listing">
                <div class="hotels">
                    <div class="hotel">
                        <h2>Our Hotels</h2>
                    </div>
                    <?php
                    $sql = "
                    SELECT h.id AS hotel_id, h.name AS hotel_name, hb.description, hb.id AS branch_id, hb.location, hb.image_path
                    FROM hotels h
                    JOIN hotel_branches hb ON h.id = hb.hotel_id
                    ";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($hotel = $result->fetch_assoc()) {
                            echo '<div class="hotel">';
                            echo '<div class="hotel-image-container">';
                            echo '<img src="' . htmlspecialchars($hotel['image_path']) . '" alt="' . htmlspecialchars($hotel['hotel_name']) . '">';
                            echo '</div>';
                            echo '<div class="hotel-content">';
                            echo '<h3>Swiss-Garden ' . htmlspecialchars($hotel['hotel_name']) . ' ' . htmlspecialchars($hotel['location']) . '</h3>';
                            echo '<p>' . htmlspecialchars($hotel['description']) . '</p>';
                            echo '<a href="booking.php?hotel_id=' . $hotel['hotel_id'] . '&location=' . urlencode($hotel['location']) . '&branch_id=' . $hotel['branch_id'] . '" class="details-button">Book Now</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    else {
                        echo '<p>No hotels found.</p>';
                    }
                    ?>
                </div>
            </section>
        </main>
    </body>
</html>