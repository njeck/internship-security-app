<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Session timeout after 10 minutes
$timeout_duration = 600;
if (isset($_SESSION['last_activity'])) {
	if ((time() - $_SESSION['last_activity']) > $timeout_duration) {

		session_unset();
		session_destroy();

		header("Location: login.php?timeout=1");
		exit();
	}
}
// Update last activity time
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../backend/database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
	header("Location: login.php");
	exit();
}

$conn = db_connect();
$user = $_SESSION['user'];

//handle upload
if (isset($_POST['upload'])) {
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	die("Invalid CSRF token");
}
	$payroll_name = $_POST['payroll_name'];
	$payroll_date = $_POST['payroll_date'];

	$stmt = $conn->prepare("INSERT INTO payrolls (payroll_name, payroll_date, uploaded_by) VALUES(?, ?, ?)");
	$stmt->bind_param("sss", $payroll_name, $payroll_date, $user);
	$stmt->execute();

	$payroll_id = $stmt->insert_id;

		// Check if file was uploaded properly
	if ($_FILES['file']['error'] !== 0) {
		die("File upload failed");
	}

	// Allow only CSV files
	$file_name = $_FILES['file']['name'];
	$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

	if ($file_extension !== 'csv') {
		die("Only CSV files are allowed");
	}

	// Limit file size to 2MB
	if ($_FILES['file']['size'] > 2 * 1024 * 1024) {
		die("File is too large. Maximum size is 2MB");
	}

	$file = $_FILES['file']['tmp_name'];

	$handle = fopen($file, "r");

	if ($handle === false) {
		die("Unable to open uploaded file");
	}

	$i = 0;
	while (($data = fgetcsv($handle, 1000, ",")) !==FALSE) {

		if ($i == 0) { $i++; continue; } // skip header

		$matricule = $data[0];
		$name = $data[1];
		$amount = $data[2];

		$stmt = $conn->prepare("INSERT INTO customers (payroll_id, matricule, name, amount, status) VALUES (?, ?, ?, ?, 'unpaid')");
		$stmt->bind_param("issd", $payroll_id, $matricule, $name, $amount);
		$stmt->execute();

		$i++;
	}

	fclose($handle);

	log_action($conn, $user, "Uploaded payroll: " . $payroll_name);
	echo "Upload Done";
}
?>


<!DOCTYPE html>
<html>
<head>
	<title>Payroll System</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h1>Welcome Admin <?php echo $user; ?></h1>
<p>Upload Payroll</p>
<form method="POST" enctype="multipart/form-data">
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
	<input type="text" name="payroll_name" placeholder="Payroll Name" required><br><br>
	<input type="date" name="payroll_date" required><br><br>
	<input type="file" name="file" required><br><br>
	<button name="upload">Upload Payroll</button>
</form>


<br>

<div class="go">
<a href="user.php"><button>Go to User Page</button></a><br><br>

<a href="report.php"><button>Go to Report</button></a><br>
</div>

<form method="POST" action="logout.php">
	<button type="submit">Logout</button>
</form>

</div>
</body>
</html>
