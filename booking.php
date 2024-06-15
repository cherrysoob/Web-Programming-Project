<?php
require 'database.php';
session_start();

$hotel_id = isset($_GET['hotel_id']) ? $_GET['hotel_id'] : null;
$location = isset($_GET['location']) ? $_GET['location'] : null;
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : null;

if (!$hotel_id || !$location || !$branch_id) {
    die("Missing hotel_id, location, or branch_id.");
}

$booking_success = "";
$booking_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $room_type = $_POST['room_type'];
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    
    $sql = "SELECT price FROM room_types WHERE hotel_id = ? AND type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $hotel_id, $room_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
    
    if ($room) {
        $price_per_night = $room['price'];
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        $interval = $checkin->diff($checkout);
        $total_nights = $interval->days;
        $total_price = $total_nights * $price_per_night;
        
        $sql = "INSERT INTO bookings (user_id, hotel_id, branch_id, checkin_date, checkout_date, total_price) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiissd", $user_id, $hotel_id, $branch_id, $checkin_date, $checkout_date, $total_price);
        
        if ($stmt->execute()) {
            $booking_success = "Booking successful! Total price: $" . number_format($total_price, 2);
        }
        else {
            $booking_error = "Error booking the room: " . $stmt->error;
        }
        
        $stmt->close();
    }
    else {
        $booking_error = "Room type not found.";
    }
}

$sql = "SELECT h.name AS hotel_name, hb.location, rt.type, rt.price 
        FROM hotels h
        JOIN hotel_branches hb ON h.id = hb.hotel_id
        JOIN room_types rt ON h.id = rt.hotel_id
        WHERE h.id = ? AND hb.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $hotel_id, $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();
$room_types = [];

while ($row = $result->fetch_assoc()) {
    $room_types[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Page</title>
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
            <section class="booking-form-container">
                <h2>Swiss-Garden <?php echo htmlspecialchars($hotel['hotel_name']) . "<br>" . htmlspecialchars($location); ?></h2>
                
                <?php if ($booking_success): ?>
                    <p class="success"><?php echo $booking_success; ?></p>
                <?php elseif ($booking_error): ?>
                    <p class="error"><?php echo $booking_error; ?></p>
                <?php endif; ?>
                
                <form action="booking.php?hotel_id=<?php echo htmlspecialchars($hotel_id); ?>&location=<?php echo urlencode($location); ?>&branch_id=<?php echo htmlspecialchars($branch_id); ?>" method="POST" class="booking-form">
                    <label for="room_type">Room Type</label>
                    <select id="room_type" name="room_type" required>
                        <?php foreach ($room_types as $room): ?>
                            <option value="<?php echo htmlspecialchars($room['type']); ?>">
                                <?php echo htmlspecialchars($room['type']) . " - $" . htmlspecialchars($room['price']) . " per night"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="checkin_date">Check-in Date</label>
                    <input type="date" id="checkin_date" name="checkin_date" required>
                    <label for="checkout_date">Check-out Date</label>
                    <input type="date" id="checkout_date" name="checkout_date" required>
                    <button type="submit">Confirm Booking</button>
                </form>
            </section>
        </main>
    </body>
</html>