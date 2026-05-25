<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../backend/database.php';

$conn = db_connect();


if (isset($_GET['undo'])) {
	$customer_id = intval($_GET['undo']);
	$payroll_id = intval($_GET['payroll_id']);

	$conn->query("UPDATE customers SET status='unpaid' WHERE id=$customer_id");
	$conn->query("DELETE FROM payments WHERE customer_id=$customer_id");

	header("Location: user.php?payroll_id=" . $payroll_id);
	exit();

}

$payroll_id = isset($_GET['payroll_id']) ? intval($_GET['payroll_id']) : 0;

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (isset($_GET['pay'])) {

	$customer_id = intval($_GET['pay']);
	$user = $_SESSION['user'];

	$conn->query("UPDATE customers SET status='paid' WHERE id=$customer_id");

	$stmt = $conn->prepare("INSERT INTO payments (customer_id, paid_by, payment_date) VALUES (?, ?, NOW())");

	if (!$stmt) {
		die("Prepare failed: " . $conn->error);
	}

$check = $conn->prepare("SELECT id FROM payments WHERE customer_id=?");
$check->bind_param("i", $customer_id);
$check->execute();
$res = $check->get_result();

	$stmt->bind_param("is", $customer_id, $user);
	$stmt->execute();

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

<h1>Welcome <?php echo $user; ?></h1>

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
</tbody id="tableBody">
<?php
if ($customers && $customers->num_rows > 0) {
	while ($row = $customers->fetch_assoc()) {
?>
<tr>
	<td><?php echo $row['matricule']; ?></td>
	<td><?php echo $row['name']; ?></td>
	<td><?php echo $row['amount']; ?></td>
	<td><?php echo $row['status']; ?></td>
 	<td>
	<?php if ($row['status'] == 'unpaid') { ?>
		<a href="?pay=<?php echo $row['id']; ?>&payroll_id=<?php echo $payroll_id ?>">
			<button>PAY</button>
		</a>
	<?php } else { ?>
		<?php if ($_SESSION['role'] == 'admin') { ?>
		<a href="?undo=<?php echo $row['id']; ?>&payroll_id=<?php echo $payroll_id; ?>" onclick="return confirm('Undo this payment?')">
			<button style="background:red;">Undo</button>
		</a>
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
<script>
window.onload = function () {
	let searchInput = document.getElementById("search");
	if (!searchInput) {
		console.log("Search input NOT found");
		return;
	}
searchInput.addEventListener("keyup", function () {

	let value = this.value.toLowerCase();
	let rows = document.querySelectorAll("#tableBody tr");

	row.forEach(function(row) {
		let text = row.textContent.toLowerCase();

		row.style.display = text.includes(value) ? "" : "none";
	});
)};
};
</script>
<script>
let row = document.querySelector("tr[style='']");
let (row) {
	row.scrollIntoview({ behavior: "smooth", block: "center"});
}
</script>

</body>
</html>
