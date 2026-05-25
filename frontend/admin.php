<?php
session_start();

require_once __DIR__ . '/../backend/database.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
	header("Location: login.php");
	exit();
}

$conn = db_connect();
$user = $_SESSION['user'];

//handle upload
if (isset($_POST['upload'])) {

	$payroll_name = $_POST['payroll_name'];
	$payroll_date = $_POST['payroll_date'];

	$stmt = $conn->prepare("INSERT INTO payrolls (payroll_name, payroll_date, uploaded_by) VALUES(?, ?, ?)");
	$stmt->bind_param("sss", $payroll_name, $payroll_date, $user);
	$stmt->execute();

	$payroll_id = $stmt->insert_id;

	$file = $_FILES['file']['tmp_name'];
	$handle = fopen($file, "r");

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
