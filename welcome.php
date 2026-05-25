<?php
session_start();

if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
	<title>Welcome</title>
<style>
body {
	font-family: Arial;
	background: linear-gradient(to right, #C56A30, #2A7F7F);
	display: flex;
	justify-content: center;
	align-items: center;
	height: 100vh;
}

.box {
	background: #D4D9C8;
	padding: 40px;
	border-radius: 10px;
	text-align: center;
	box-shadow: 0 0 15px rgba(0,0,0,0.2);
}
</style>
</head>

<body>
<div class="box">
	<h1>Welcome, <?php echo $user; ?></h1>
<form method="POST" action="logout.php">
	<button type="submit">Logout</button>
</form>
</div>
</body>

</html>
