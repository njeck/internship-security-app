<?php
session_start();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../backend/database.php';

$conn = db_connect();

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    //CHECK LOGIN ATTEMPTS
    $stmt = $conn->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result_attempt = $stmt->get_result();

    if ($result_attempt->num_rows > 0) {
        $data = $result_attempt->fetch_assoc();

        if ($data['attempts'] >= 5 && strtotime($data['last_attempt']) > time() - 300) {
            die("Account locked. Try again in 5 minutes.");
        }
    }

    //CSRF CHECK
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    //CHECK USER
    $stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
				log_action($conn, $username, "Login successful");
            //RESET ATTEMPTS
            $stmt = $conn->prepare("DELETE FROM login_attempts WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();

            session_regenerate_id(true);

            $_SESSION['user'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['last_activity'] = time();

            if ($row['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit();

        } else {

            //WRONG PASSWORD → INCREASE ATTEMPTS
            $stmt = $conn->prepare("
                INSERT INTO login_attempts (username, attempts)
                VALUES (?, 1)
                ON DUPLICATE KEY UPDATE
                    attempts = attempts + 1,
                    last_attempt = CURRENT_TIMESTAMP
            ");
            $stmt->bind_param("s", $username);
            $stmt->execute();

			log_action($conn, $username, "Failed login - wrong password");
            echo "Wrong password!";
        }

    } else {

        //USER NOT FOUND → ALSO TRACK
        $stmt = $conn->prepare("
            INSERT INTO login_attempts (username, attempts)
            VALUES (?, 1)
            ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                last_attempt = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();

		log_action($conn, $username, "Failed login - user not found");
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
<title>Login</title>
<style>
body {
	font-family: Arial;
	background: #511010;
	display: flex;
	justify-content: center;
	align-items: center;
	height: 100vh;
}

.box {
	background: #D6E6EF;
	padding: 30px;
	border-radius: 10px;
	width: 300px;
	text-align: center;
	box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

input {
	width: 90%;
	padding: 10px;
	border-radius: 5px;
	border: 1px solid #ccc;
}

button {
	background: #4facfe;
	color: white;
	border: none;
	padding: 10px;
	width: 100%;
	border-radius: 5px;
	cursor: pointer;
}

button:hover {
	background: #00c6ff;
}
</style>
</head>


<body>

<div class="box">
<h2>Login</h2>

<form method="POST">
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
	<input type="text" name="username" placeholder="Username" required><br><br>
	<input type="password" name="password" placeholder="Password" required><br><br>
	<button type="submit" name="login">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>
