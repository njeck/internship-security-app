<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';

$conn = db_connect();

if (isset($_POST['register'])) {

	$fullname = $_POST['fullname'];
	$username = $_POST['username'];
	$email = $_POST['email'];
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$address = $_POST['address'];
	$phone = $_POST['phone'];
	$gender = $_POST['gender'];
	$dob = $_POST['dob'];

	$role = 'user';

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
