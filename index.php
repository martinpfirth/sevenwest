<?php
	$customer_id = 1; //skipping a login and dictating the user

	require("connection.php");
	require("functions.php");
?>

<html>
	<head>
		<title>Seven West Store</title>
		<style>
			h3{
				margin-bottom: 0;
			}
			th{
				text-align: left;
			}

			th, td{
				padding: 5px;
			}

			span{
				display: inline-block;
				padding-left: 5px;
				color: green;
			}
		</style>
	</head>
	<body>
		<h1>Seven West Store</h1>
		<h2>Products</h2>
		<?php
			displayProducts($conn);
		?>
		<hr />
		<h2>Your Cart</h2>
		<?php
			displayCart($conn, $customer_id)
		?>
		<p><a href="index.php?action=clearCart">Clear Cart</a></p>
	</body>
</html>

<?php
	$conn->close();
?>