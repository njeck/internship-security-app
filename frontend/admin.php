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

		if (count($data) < 3) {
    		continue;
		}

		$matricule = trim($data[0]);
		$name = trim($data[1]);
		$amount = trim($data[2]);
		
		if (!preg_match('/^[A-Za-z0-9]+$/', $matricule)) {
			continue;
		}

		if (!preg_match('/^[A-Za-z ]+$/', $name)) {
			continue;
		}

		if (!is_numeric($amount) || $amount <= 0) {
			continue;
		}
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
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container">
<div class="go">
<h1>Welcome Admin <?php echo htmlspecialchars($user); ?></h1>
	<div class="menu">
        <a href="admin.php"><button>Admin</button></a>
        <a href="user.php"><button>User</button></a>
        <a href="report.php"><button>Report</button></a>
    </div>
</div>
<h2>Upload Payroll</h2>
<form method="POST" enctype="multipart/form-data">
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
	<input type="text" name="payroll_name" placeholder="Payroll Name" required><br><br>
	<input type="date" name="payroll_date" required><br><br>
	<input type="file" name="file" required><br><br>
	<button name="upload">Upload Payroll</button>
</form>


<br>
<form method="POST" action="logout.php">
	<button type="submit">Logout</button>
</form>

</div>
</body>
</html>
