<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../backend/database.php';

$conn = db_connect();

if (isset($_POST['login'])) {

	$username = $_POST['username'];
	$password = $_POST['password'];

	echo "You entered: " . $username . "<br>";

	$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		if (password_verify($password, $row['password'])) {

			$_SESSION['user'] = $row['username'];
			$_SESSION['role'] = $row['role'];

			if ($row['role'] == 'admin') {

			if (isset($_GET['redirect']) && $_GET['redirect'] == 'report'){
				header("Location: report.php");
			} else {
				header("Location: admin.php");
			}
			} else {
				header("Location: user.php");
			}
			exit();

		} else {
			echo "Wrong password!";
		}

	} else {
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
	<input type="text" name="username" placeholder="Username" required><br><br>
	<input type="password" name="password" placeholder="Password" required><br><br>
	<button type="submit" name="login">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>
