<?php
require 'database.php';
session_start();
$location = isset($_GET['location']) ? $_GET['location'] : '';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Search Results Page</title>
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
                        <li><a class="home-button" href="home.php">Home</a></li>
                        <li><a href="hotels.php">Hotels</a></li>
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
            <div class="search-results-container">
                <h2>Search Results for "<?php echo htmlspecialchars($location); ?>"</h2>
                <div class="search-results">
                    <?php
                    $sql = "
                    SELECT h.id AS hotel_id, h.name AS hotel_name, hb.description AS branch_description, hb.id AS branch_id, hb.location AS branch_location, hb.image_path AS image_path
                    FROM hotels h
                    LEFT JOIN hotel_branches hb ON h.id = hb.hotel_id
                    WHERE hb.location LIKE ?
                    ";
                    $stmt = $conn->prepare($sql);
                    $searchParam = "%" . $location . "%";
                    $stmt->bind_param('s', $searchParam);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $hotels = [];
                        while ($row = $result->fetch_assoc()) {
                            $hotels[$row['hotel_id']]['name'] = $row['hotel_name'];
                            $hotels[$row['hotel_id']]['branches'][$row['branch_id']] = [
                                'location' => $row['branch_location'],
                                'image_path' => $row['image_path'],
                                'description' => $row['branch_description']
                            ];
                        }
                        foreach ($hotels as $hotel_id => $hotel) {
                            if (!empty($hotel['branches'])) {
                                foreach ($hotel['branches'] as $branch_id => $branch) {
                                    echo '<div class="search-hotel">';
                                    echo '<div class="found-image-container">';
                                    echo '<img src="' . htmlspecialchars($branch['image_path']) . '" alt="' . htmlspecialchars($hotel['name']) . '">';
                                    echo '</div>';
                                    echo '<div class="hotel-found">';
                                    echo '<h3>Swiss-Garden ' . htmlspecialchars($hotel['name']) . ' ' . htmlspecialchars($branch['location']) . '</h3>';
                                    echo '<p>' . htmlspecialchars($branch['description']) . '</p>';
                                    echo '<a href="booking.php?hotel_id=' . htmlspecialchars($hotel_id) . '&branch_id=' . htmlspecialchars($branch_id) . '&location=' . urlencode($branch['location']) . '" class="details-button">Book Now</a>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            else {
                                echo '<div class="not-found">';
                                echo '<p>No branches found.</p>';
                                echo '</div>';
                            }
                        }
                    }
                    else {
                        echo '<div class="not-found">';
                        echo '<p>No hotel found in this location.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </body>
</html>