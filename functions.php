<?php

if(isset($_GET["action"])){
	switch($_GET["action"]){
		case "addToCart":
			addToCart($conn, $_GET["productId"], $customer_id);
			break;
		case "clearCart":
			clearCart($conn, $customer_id);
			break;
	}
}

function currency($amount){
	return "$" . money_format('%.2n', $amount);
}

function checkPromotions($conn, $product_id){
	$sql = "SELECT promotions.promotion_id, promotion_types.promotion_name FROM promotions 
	INNER JOIN promotion_types ON promotions.promotion_id = promotion_types.id
	WHERE product_id = $product_id";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		return $row["promotion_name"];
	} else {
		return false;
	}
}

function displayProducts($conn){
	$sql = "SELECT id, name, description, price FROM products";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        echo "<h3>" . $row["name"] . " - " . currency($row["price"]) . "</h3>";
	        echo $row["description"] . "<br />";
	        echo "<a href=\"?action=addToCart&productId=" . $row["id"] . "\">Add to Cart</a>";
	    }
	} else {
	    echo "There are no products to display.";
	}
}

function displayCart($conn, $customer_id){
	$sql = "SELECT carts.id, carts.customer_id, carts.status, products.name, products.price, products.id AS 'product_id', cart_items.quantity FROM (
				(
					carts INNER JOIN cart_items ON carts.id = cart_items.cart_id
				)
				INNER JOIN products ON cart_items.product_id = products.id
			)
			WHERE carts.customer_id = '$customer_id' AND carts.status = 'active'";

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$total_quantity = 0;
		$total_price = 0;

	    echo "<table><tr><th>Product</th><th>Quantity</th><th>Price</th></tr>";

	    while($row = $result->fetch_assoc()) {

	    	$quantity = $row["quantity"];
	    	$promotion = ""; //none yet

	    	//check promotions
	    	switch(checkPromotions($conn, $row["product_id"])){
	    		case "Buy one get one free": //using name not id for clarity to coder
	    			$quantity = ceil($row["quantity"] / 2);
	    			$promotion = "<span>Buy one get one free</span>";
	    			break;
	    	}

	    	$total_quantity = $total_quantity + $row["quantity"];
	    	$total_price = $total_price + ($quantity * $row["price"]);

	    	echo "<tr>";
	        echo "<td>" . $row["name"] . "</td>";
	        echo "<td>" . $row["quantity"] . "</td>";
	        echo "<td>" . currency($quantity * $row["price"]) . $promotion . "</td>";
	        echo "</tr>";
	    }
	    echo "<tr><td><strong>Total</strong><td>" . $total_quantity. "</td><td>" . currency($total_price) . "</td></tr>";
	    echo "<table>";
	    
	} else {
	    echo "There's nothing in your cart.";
	}
}

function addToCart($conn, $product_id, $customer_id){
	//check to see if the user has an ACTIVE cart (old and completed carts never erased, just unpublished)
	$sql = "SELECT id, status, customer_id FROM carts WHERE customer_id = '$customer_id' AND status = 'active'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$current_cart = $row["id"];
	} else {
		//create a new cart
		$sql = "INSERT INTO carts (customer_id, status) VALUES ('$customer_id', 'active')";
		if ($conn->query($sql) === TRUE) {
		    $current_cart = $conn->insert_id;
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}

	//add new cart item if doesn't exist in cart, otherwise increment quantity
	$sql = "INSERT INTO cart_items (product_id, cart_id, quantity)
			VALUES ($product_id, $current_cart, 1)
			ON DUPLICATE KEY UPDATE quantity = quantity + 1;";

			//if product and cart_id already exist, new one

	if ($conn->query($sql) === TRUE) {
	    echo "Successfully added to cart!";
	} else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}
}

function clearCart($conn, $customer_id){
	$sql = "UPDATE carts SET status = 'inactive' WHERE customer_id = '$customer_id'";
	if ($conn->query($sql) === TRUE) {
		echo "Cart has been emptied.";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
}

//get user name
?>