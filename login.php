<?php
require 'database.php';
session_start();

$login_error = "";
$register_error = "";
$register_success = "";
$password_error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_to = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: $redirect_to");
                }
                else {
                    header("Location: home.php");
                }
                exit();
            }
            else {
                $login_error = "Invalid password.";
                $password_error = true;
            }
        }
        else {
            $login_error = "No user found with this email.";
        }
    }
    elseif (isset($_POST['register'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $register_error = "Passwords do not match.";
        }
        else {
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $register_error = "Email already registered.";
            }
            else {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
                $stmt->prepare($sql);
                $stmt->bind_param("sss", $name, $email, $password_hashed);
                
                if ($stmt->execute()) {
                    $register_success = "Registration successful.";
                }
                else {
                    $register_error = "Error: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login/Register - Hotel Booking Platform</title>
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
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a class="login-button" href="login.php">Login/Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
        <main>
            <div class="auth-container">
                <section class="auth-forms">
                    <div class="login-form">
                        <h2>Login</h2>
                        <?php if (!empty($login_error) && !$password_error): ?>
                            <p class="error"><?php echo $login_error; ?></p>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="Password" required
                                    class="<?php echo $password_error ? 'input-error' : ''; ?>">
                            <?php if ($password_error): ?>
                                <p class="error">Invalid password.</p>
                            <?php endif; ?>
                            <button type="submit" name="login">Login</button>
                        </form>
                    </div>
                    <div class="or">OR</div>
                    <div class="register-form">
                        <h2>Register</h2>
                        <?php if (!empty($register_error)): ?>
                            <p class="error"><?php echo $register_error; ?></p>
                        <?php elseif (!empty($register_success)): ?>
                            <p class="success"><?php echo $register_success; ?></p>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <input type="text" name="name" placeholder="Name" required>
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                            <button type="submit" name="register">Register</button>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>