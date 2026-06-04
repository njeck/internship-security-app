<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../backend/database.php';

$conn = db_connect();

if (isset($_POST['register'])) {
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	die("Invalid CSRF token");
}
	$fullname = trim($_POST['fullname']);
	$username = trim($_POST['username']);
	$email = trim($_POST['email']);
	$plain_password = $_POST['password'];
	$address = trim($_POST['address']);
	$phone = trim($_POST['phone']);
	$gender = $_POST['gender'];
	$dob = $_POST['dob'];

	$role = 'user';

	// Check if fields are empty
	if (
		empty($fullname) ||
		empty($username) ||
		empty($email) ||
		empty($plain_password)
	) {
		die("All fields are required");
	}

	// Check password length
	if (strlen($plain_password) < 6) {
		die("Password must be at least 6 characters");
	}

	// Check if username or email already exists
	$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");

	if (!$check) {
		die("SQL ERROR: " . $conn->error);
	}

	$check->bind_param("ss", $username, $email);
	$check->execute();

	$result = $check->get_result();

	if ($result->num_rows > 0) {
		die("Username or Email already exists");
	}

	// Hash password AFTER validation
	$password = password_hash($plain_password, PASSWORD_DEFAULT);

	$stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, address, phone, gender, dob, role) 
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

	if (!$stmt) {
		die("SQL ERROR: " . $conn->error);
	}

	$stmt->bind_param("sssssssss", $fullname, $username, $email, $password, $address, $phone, $gender, $dob, $role);

	if ($stmt->execute()) {
		header("Location: login.php");
		exit();
	} else {
		echo "Error: " .$stmt->error;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
<style>
body {
	font-family:Arial;
	background: #511010;
	display: flex;
	justify-content: center;
	align-items: center;
	height: 100vh;
}

.box {
	background: #D6E6EF;
	padding: 30px;
	border-radius: 30px;
	width: 300px;
	text-align: center;
	box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

input {
	width: 90%;
	padding: 10px;
	margin: 10px 0;
	border-radius: 5px;
	border: 1px solid #ccc;
}

button{
	background: #43e97b;
	color: white;
	border: none;
	padding: 10px;
	width: 100%;
	border-radius: 5px;
	cursor: pointer;
}

button:hover {
	background: #2ecc71;
}
</style>
</head>

<body>

<div class="box">
	<h2>Register</h2>

<form method="POST">
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
	<input type="text" name="fullname" placeholder="Fullname" required><br>
	<input type="text" name="username" placeholder="Username" required><br>
	<input type="email" name="email" placeholder="Email" required><br>
	<input type="password" name="password" placeholder="Password" required><br>
	<input type="text" name="address" placeholder="Address" required><br>
	<input type="text" name="phone" placeholder="+237..." required><br>
	Gender:<br>
	<select name="gender">
		<option value="Male">Male</option>
		<option value="Female">Female</option>
	</select><br>
	<input type="date" name="dob" required><br>
	<button type="submit" name="register">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>
