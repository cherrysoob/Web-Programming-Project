<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$update_success = "";
$update_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($current_password, $user['password'])) {
        $update_error = "Current password is incorrect.";
    }
    else {
        if ($new_password !== $confirm_new_password) {
            $update_error = "New password and confirm new password do not match.";
        }
        else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?";
            $params = array($name, $email);
            if ($new_password !== '') {
                $sql .= ", password = ?";
                $params[] = $hashed_password;
            }
            $sql .= " WHERE id = ?";
            $params[] = $user_id;
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            
            if ($stmt->execute()) {
                $update_success = "Profile updated successfully!";
            }
            else {
                $update_error = "Error updating profile: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile Page</title>
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
                        <li><a href="hotels.php">Hotels</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a class="profile-button" href="profile.php">My Profile</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login/Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
        <main>
            <section class="profile-container">
                <h2>Profile</h2>
                <?php if ($update_success): ?>
                    <p class="success"><?php echo $update_success; ?></p>
                <?php elseif ($update_error): ?>
                    <p class="error"><?php echo $update_error; ?></p>
                <?php endif; ?>
                <form action="profile.php" method="POST" class="profile-form">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    
                    <div class="password-toggle">
                        <label for="change-password">Change Password?</label>
                        <input type="checkbox" id="change-password" name="change-password">
                    </div>
                    <div id="password-field">
                        <div class="password-container">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Current Password" required>
                        </div>
                        <div class="password-container">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="New Password">
                        </div>
                        <div class="password-container">
                            <label for="confirm_new_password">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm New Password">
                        </div>
                    </div>
                    <button type="submit">Update Profile</button>
                </form>
            </section>
        </main>
        <script>
            document.getElementById('change-password').addEventListener('change', function() {
                var passwordField = document.getElementById('password-field');
                var nameField = document.getElementById('name');
                var emailField = document.getElementById('email');
                
                if (this.checked) {
                    passwordField.classList.remove('hidden');
                    nameField.disabled = false;
                    emailField.disabled = false;
                }
                else {
                    passwordField.classList.add('hidden');
                    nameField.disabled = true;
                    emailField.disabled = true;
                }
            });
            
            window.addEventListener('DOMContentLoaded', function() {
                var changePasswordCheckbox = document.getElementById('change-password');
                var passwordField = document.getElementById('password-field');
                var nameField = document.getElementById('name');
                var emailField = document.getElementById('email');
                
                if (changePasswordCheckbox.checked) {
                    passwordField.classList.remove('hidden');
                    nameField.disabled = false;
                    emailField.disabled = false;
                }
                else {
                    passwordField.classList.add('hidden');
                    nameField.disabled = true;
                    emailField.disabled = true;
                }
            });
        </script>
    </body>
</html>