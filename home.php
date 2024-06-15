<?php
require 'database.php';
session_start();

$guestName = '';
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $guestName = $user['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home Page</title>
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
            <div class="home-container">
                <div class="greeting">
                    <h2>Welcome, <?php echo htmlspecialchars($guestName ?: 'Guest'); ?>!</h2>
                </div>
                <section class="slider-container">
                    <div class="slider-wrapper">
                        <div class="slider">
                            <img id="slide-1" src="slide1.jpg" alt="slide1"/>
                            <img id="slide-2" src="slide2.jpg" alt="slide2"/>
                            <img id="slide-3" src="slide3.jpg" alt="slide3"/>
                            <img id="slide-4" src="slide4.jpg" alt="slide4"/>
                            <img id="slide-5" src="slide5.jpg" alt="slide5"/>
                        </div>
                    <div class="slider-nav">
                        <a href="#slide-1"></a>
                        <a href="#slide-2"></a>
                        <a href="#slide-3"></a>
                        <a href="#slide-4"></a>
                        <a href="#slide-5"></a>
                    </div>
                </div>
            </section>
            <section class="search-bar">
                <form action="search.php" method="GET">
                    <input type="text" name="location" placeholder="Enter a location" required>
                    <button type="submit">Search</button>
                </form>
            </section>
            <section class="featured-hotels">
                <h2>Featured Hotels</h2>
                <div class="hotels-lists">
                    <?php
                    $sql = "
                    SELECT h.id AS hotel_id, h.name AS hotel_name, hb.id AS branch_id, hb.location AS branch_location, hb.image_path AS image_path
                    FROM hotels h
                    LEFT JOIN hotel_branches hb ON h.id = hb.hotel_id
                    ";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $hotels = [];
                        while ($row = $result->fetch_assoc()) {
                            $hotels[$row['hotel_id']]['name'] = $row['hotel_name'];
                            $hotels[$row['hotel_id']]['branches'][$row['branch_id']] = ['location' => $row['branch_location'], 'image_path' => $row['image_path']];
                        }
                        foreach ($hotels as $hotel_id => $hotel) {
                            echo '<div class="hotels-list">';
                            echo '<h3>' . htmlspecialchars($hotel['name']) . '</h3>';
                            echo '<div class="hotel-branches">';
                            if (!empty($hotel['branches'])) {
                                echo '<ul>';
                                foreach ($hotel['branches'] as $branch_id => $branch) {
                                    echo '<li>';
                                    echo '<a href="booking.php?hotel_id=' . htmlspecialchars($hotel_id) . '&branch_id=' . htmlspecialchars($branch_id) . '&location=' . urlencode($branch['location']) . '">';
                                    echo '<img src="' . htmlspecialchars($branch['image_path']) . '" alt="' . htmlspecialchars($hotel['name']) . '">';
                                    echo '<div>';
                                    echo '<p>Swiss-Garden ' . htmlspecialchars($hotel['name']) . ' ' . htmlspecialchars($branch['location']) . '</p>';
                                    echo '</div>';
                                    echo '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            else {
                                echo '<p>No branches found.</p>';
                            }
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
            </div>
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const slider = document.querySelector('.slider');
                const slides = slider.querySelectorAll('img');
                let currentIndex = 0;
                
                function showSlide(index) {
                    const offset = slider.clientWidth * index;
                    slider.scrollTo({ left: offset, behavior: 'smooth' });
                }
                
                function nextSlide() {
                    currentIndex = (currentIndex + 1) % slides.length;
                    showSlide(currentIndex);
                }
                
                setInterval(nextSlide, 3500);
            });
        </script>
    </body>
</html>