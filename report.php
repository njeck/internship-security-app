<?php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user'])) {
	header("Location: login.php?redirect=report");
	exit();
}

if ($_SESSION['role'] != 'admin') {
	header("Location: login.php");
	exit();
}

$conn = db_connect();

$payroll_id = isset($_GET['payroll_id']) ? intval($_GET['payroll_id']) : 0;
$result = $conn->query("SELECT id, payroll_name FROM payrolls");

if (isset($_POST['export'])) {
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=report.xls");

	echo "Matricule\tName\tAmount\tStatus\tPaid By\tPayment Date\n";

if ($payroll_id == 0) {
	$sql = "SELECT c.matricule, c.name, c.amount, c.status, p.paid_by, p.payment_date
		FROM customers c
		LEFT JOIN payments p ON c.id = p.customer_id";
} else {
	$sql = "SELECT c.matricule, c.name, c.amount, c.status, p.paid_by, p.payment_date
		FROM customers c
		LEFT JOIN payments p ON c.id = p.customer_id
		WHERE c.payroll_id = $payroll_id";
	}

	$result = $conn->query($sql);

	while($row = $result->fetch_assoc()) {
		echo $row['matricule']."\t".
		$row['name']."\t".
		$row['amount']."\t".
		$row['status']."\t".
		$row['paid_by']."\t".
		$row['payment_date']."\n";
	}

	exit();
}

if ($payroll_id == 0) {
$sql = "SELECT c.matricule, c.name, c.amount, c.status, p.paid_by, p.payment_date
	FROM customers c
	LEFT JOIN payments p ON c.id = p.customer_id";
} else {
	$sql = "SELECT c.matricule, c.name, c.amount, c.status, p.paid_by, p.payment_date
		FROM customers c
		LEFT JOIN payments p ON c.id = p.customer_id
		WHERE c.payroll_id = $payroll_id";
	}

$result = $conn->query($sql);

if (!$result) {
	die("Query failed: " . $conn->error);
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

<h2>Payment Report</h2>

<form method="POST">
	<button name="export">Export to Excel</button>
</form>
<br>

<form method="GET" action="report.php">
	<select name="payroll_id">
		<option value="0">All Payrolls</option>
		<?php
		$payrolls = $conn->query("SELECT id, payroll_name FROM payrolls");

		while ($p = $payrolls->fetch_assoc()) {
			$selected = ($payroll_id == $p['id']) ? 'selected' : '';
			echo "<option value='".$p['id']."' $selected>".$p['payroll_name']."</option>";
		}
		?>
	</select>
	<button type="submit">Filter</button>
</form>
<br>

<table border="1">
<tr>
	<th>Matricule</th>
	<th>Name</th>
	<th>Amount</th>
	<th>Status</th>
	<th>Paid By</th>
	<th>Payment Date</th>
</tr>
<?php while($row = $result->fetch_assoc()) { ?>
<tr>
	<td><?php echo $row['matricule']; ?></td>
	<td><?php echo $row['name']; ?></td>
	<td><?php echo $row['amount']; ?></td>
	<td><?php echo $row['status']; ?></td>
	<td><?php echo $row['paid_by']; ?></td>
	<td>
	<?php
	echo !empty($row['payment_date']) ? $row['payment_date'] : '-';
	?>
	</td>
</tr>
<?php } ?>
</table>

<?php
if ($payroll_id == 0) {
	$totalQuery = $conn->query("
		SELECT SUM(c.amount) as total
		FROM customers c
		WHERE c.status='paid'
	");
} else {
	$totalQuery = $conn->query("
		SELECT SUM(c.amount) as total
		FROM customers c
		WHERE c.status='paid' AND payroll_id=$payroll_id
	");
}

$totalRow = $totalQuery->fetch_assoc();
$total_paid = $totalRow['total'] ?? 0;
?>

<h3>Total Paid: <?php echo number_format($total_paid, 0, '.', ','); ?> FCFA</h3>

<?php
//dashboard data
if ($payroll_id == 0) {
	$total_users = $conn->query("SELECT COUNT(*) as total FROM customers")->fetch_assoc()['total'];
} else {
	$total_users = $conn->query("SELECT COUNT(*) as total FROM customers WHERE payroll_id=$payroll_id")->fetch_assoc()['total'];
}
if ($payroll_id == 0) {
	$total_paid = $conn->query("SELECT SUM(amount) as total FROM customers WHERE status='paid'")->fetch_assoc()['total'];
	$total_unpaid = $conn->query("SELECT SUM(amount) as total FROM customers WHERE status='unpaid'")->fetch_assoc()['total'];
} else {
	$total_paid = $conn->query("SELECT SUM(amount) as total FROM customers WHERE status='paid' AND payroll_id=$payroll_id")->fetch_assoc()['total'];
	$total_unpaid = $conn->query("SELECT SUM(amount) as total FROM customers WHERE status='unpaid' AND payroll_id=$payroll_id")->fetch_assoc()['total'];
};
?>

<div class="container">

<h3>Summary</h3>
<p>Total Employees: <?php echo $total_users; ?></p>
<p>Total Paid: <?php echo number_format($total_paid, 0, '.', ','); ?> FCFA</p>
<p>Total Unpaid: <?php echo number_format($total_unpaid, 0, '.', ','); ?> FCFA</p>

</div>

<br><br>
<a href="admin.php">
	<button>Back to Admin</button>
</a>
<br><br>
<a href="logout.php">
	<button style="background:8B1E1E;">Logout</button>
</a>
<br>

</div>
</body>
</html>
