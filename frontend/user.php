<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("Invalid CSRF token");
    }

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

if (
    !isset($_SESSION['user']) ||
    !isset($_SESSION['role'])
) {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/../backend/database.php';

$conn = db_connect();


if (isset($_POST['undo'])) {
	$customer_id = intval($_POST['customer_id']);
	$payroll_id = intval($_GET['payroll_id']);

	$stmt = $conn->prepare("UPDATE customers SET status='unpaid' WHERE id=?");
	$stmt->bind_param("i", $customer_id);
	$stmt->execute();
	log_action($conn, $_SESSION['user'], "Undo payment for customer ID: " . $customer_id);
	$stmt = $conn->prepare("DELETE FROM payments WHERE customer_id=?");
	$stmt->bind_param("i", $customer_id);
	$stmt->execute();

	header("Location: user.php?payroll_id=" . $payroll_id);
	exit();

}

$payroll_id = isset($_GET['payroll_id']) ? intval($_GET['payroll_id']) : 0;

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (isset($_POST['pay'])) {

	$customer_id = intval($_POST['customer_id']);
	$user = $_SESSION['user'];

	log_action($conn, $user, "Marked payment for customer ID: " . $customer_id);

	$check = $conn->prepare("SELECT id FROM payments WHERE customer_id=?");
	$check->bind_param("i", $customer_id);
	$check->execute();
	$res = $check->get_result();

	if ($res->num_rows > 0) {
		die("Payment already exists for this employee");
	}
	/* Insert payment record */
	$paymentStmt = $conn->prepare(
		"INSERT INTO payments (customer_id, paid_by, payment_date)
		VALUES (?, ?, NOW())"
	);
	$paymentStmt->bind_param("is", $customer_id, $user);
	$paymentStmt->execute();
	/* Update customer status */
	$updateStmt = $conn->prepare(
		"UPDATE customers SET status='paid' WHERE id=?"
	);
	$updateStmt->bind_param("i", $customer_id);
	$updateStmt->execute();

	header("Location: user.php?payroll_id=".$_GET['payroll_id']);
	exit();
}

if (!isset($_SESSION['user']) ||
	($_SESSION['role'] != 'user' && $_SESSION['role'] != 'admin')) {
	header("Location: login.php");
	exit();
}

$user = $_SESSION['user'];

$sql = "SELECT * FROM customers WHERE payroll_id=$payroll_id";

//get payroll
$payroll = $conn->query("
	SELECT DISTINCT p.id, p.payroll_name
	FROM payrolls p
	JOIN customers c on p.id = c.payroll_id
");
?>


<!DOCTYPE html>
<html>
<head>
	<title>Payroll System</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h1>Welcome <?php echo htmlspecialchars($user); ?></h1>

<h2>Selct Payroll</h2>

<form method="GET">
	<select name="payroll_id">
		<?php
		$result = $conn->query("SELECT * FROM payrolls");
		while ($row = $result->fetch_assoc()) {
			$selected = (isset($_GET['payroll_id']) && $_GET['payroll_id'] == $row['id']) ? 'selected' : '';
			echo "<option value='".$row['id']."' $selected>".$row['payroll_name']."</option>";
		}
		?>
	</select>
	<button type="submit">Load</button>
</form>

<?php
if (isset($_GET['payroll_id'])) {
	$payroll_id = intval($_GET['payroll_id']);
	$customers = $conn->query("SELECT * FROM customers WHERE payroll_id=$payroll_id");
if (!$customers) {
	die("Query failed: " . $conn->error);
}
?>

<h3>Customers</h3>


<input type="text" id="search" placeholder="Seach by Name or Matricule">


<?php
if ($customers->num_rows == 0){
	echo "<p>No results found</p>";
}
?>

<table border="1">
<thead>
<tr>
	<th>Matricule</th>
	<th>Name</th>
	<th>Amount</th>
	<th>Status</th>
	<th>Action</th>
</tr>
</thead>
<tbody id="tableBody">
<?php
if ($customers && $customers->num_rows > 0) {
	while ($row = $customers->fetch_assoc()) {
?>
<tr>
	<td><?php echo htmlspecialchars($row['matricule']); ?></td>
	<td><?php echo htmlspecialchars($row['name']); ?></td>
	<td><?php echo htmlspecialchars($row['amount']); ?></td>
	<td><?php echo htmlspecialchars($row['status']); ?></td>
 	<td>
	<?php if ($row['status'] == 'unpaid') { ?>
		
		<form method="POST">
		<input type="hidden"
			name="csrf_token"
			value="<?php echo $_SESSION['csrf_token']; ?>">
		<input type="hidden"
			name="customer_id"
			value="<?php echo $row['id']; ?>">
		<input type="hidden"
			name="payroll_id"
			value="<?php echo $payroll_id; ?>">
		<button type="submit" name="pay">
			PAY
		</button>
		</form>

	<?php } else { ?>
		<?php if ($_SESSION['role'] == 'admin') { ?>
		
		<form method="POST">
		<input type="hidden"
			name="csrf_token"
			value="<?php echo $_SESSION['csrf_token']; ?>">
		<input type="hidden"
			name="customer_id"
			value="<?php echo $row['id']; ?>">
		<input type="hidden"
			name="payroll_id"
			value="<?php echo $payroll_id; ?>">
		<button
			type="submit"
			name="undo"
			style="background:red;"
			onclick="return confirm('Undo this payment?')">
			Undo
		</button>
		</form>

	<?php } else { ?>
		<span style="color:gray;">Paid</span>
	<?php } ?>
	<?php } ?>
	</td>
</tr>

<?php
}
} else {
	echo '<tr><td colspan="5">No data found</td><>/tr';
}
?>
</tbody>
</table>

<?php } ?>

<br><br>
<?php
if ($_SESSION['role'] == 'admin') {
	echo '<a href="admin.php"><button>Go to Admin Page</button></a>';
}
?>

<br><br>
<form method="POST" action="logout.php">
	<button type="submit">Logout</button>
</form>

</div>
</body>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search");
    if (!searchInput) {
        return;
    }
    searchInput.addEventListener("keyup", function () {
        const value = this.value.toLowerCase();
        const rows = document.querySelectorAll("#tableBody tr");
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            if (text.includes(value)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
</script>
</html>
