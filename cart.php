<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();
require_once('header.php');
require_once('../../info/dbinfo.php');


$loginPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/FrontPage.php';
$thisPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/cart.php';

if($_SERVER['HTTP_REFERER'] == $loginPage || $_SERVER['HTTP_REFERER'] == $thisPage)
{
	//If the user is buying a product, or has a cart set up 
	if(isset($_POST["product"]) || isset($_SESSION['cart']))
	{
		$productID = $_POST["product"];
		$price = $_POST["price"];

		//If they're logged in
		if(isset($_SESSION['validUser']))
		{

			//if they came to add a product to the cart, add it
			if(isset($_POST["product"]))
			{
				//if they already have a cart set up
				if(isset($_SESSION['cart']))
				{
					$done =false;
					//cycle through to see if the product is in the cart and increment the qty
					foreach ($_SESSION['cart'] as $key => $value) 
					{
						$key == $productID? $value ++: $done = true; 
					}
					//if not in the cart already, add that ($productID => 1) value to the cart array
					if($done == false)
					{
						$_SESSION['cart'][$productID] = 1;
					}
				}
				//Otherwise, create a new cart
				else
				{
					$newCart = array($productID => 1);
					$_SESSION['cart'] = $newCart;
				}
			}
			//If they've just hit the 'remove from cart button'
			else if(isset($_POST['remove']))
			{
				$idToRemove = $_POST['remove'];
				unset($_SESSION['cart'][$idToRemove]);
				if(count($_SESSION['cart']) == 0)
				{
					unset($_SESSION['cart']);
				}
			}
			
			//Display the items in the cart if there is one (there may not be if they just removed an item and emptied it)
			if(isset($_SESSION['cart']))
			{
				echo 
				"<form action=\"purchase.php\"method=\"POST\" ><input type=\"hidden\" name=\"checkout\" value=\"true\"><button>Checkout Now<button></form>
				<table id=\"cartTable\">
				<thead>
				<tr colspan=\"3\"><th>Items in your cart:</th></thead>
				<tbody>";
				
				foreach ($_SESSION['cart'] as $key => $value) 
				{
					$path = getPicPath($key);
					echo "<tr><td><img src=\"$path\" width=\"100\" height=\"100\"><td><label>Quantity: </label>$value<td>
					<form method=\"POST\"><input type=\"hidden\" name=\"remove\" value=\"$key\"><button>Remove from cart</button>";
				}
				echo "</tbody></table>";
			}
		}	
		
		else
		{
			echo "<h3>You must be logged in to make purchases. You can login above </h3>";
		}
	}
}

else
{
	echo "<h2>You can't see this page without logging in first.</h2><br>
	<h3>Just click the login button in the top right corner!</h3>";
	$ref = $_SERVER['HTTP_REFERER'];
	echo "$ref";
}

function makePurchase($productArray)
{

	global $dbURL;
  global $username;
  global $password;
  global $database;

  $mysqli = new mysqli($dbURL, $username, $password, $database);
  if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}
  
  //Don't need prepared statements, because the data comes from the website
	$userID = $_SESSION['validUser'];
	$orderQuery = "INSERT INTO order(fk_customer_id) VALUES ('$userID');";
	if(!($mysqli->real_query($orderQuery)))
	{
		echo "Couldn't update the order table.(" . $mysqli->errno . ") " . $mysqli->error;
	}

	//now update the many-to-many table
	$orderID = $mysqli->insert_id;
	foreach ($$productArray as $key => $value) 
	{
		$orderProductQuery = "INSERT INTO order(fk_order_id, fk_product_id, quantity) VALUES ('$orderID','$key', '$value');";

		if(!($mysqli->real_query($orderProductQuery)))
		{
			echo "Error updating the order_product table at product value $key(" . $mysqli->errno . ") " . $mysqli->error;
		}
	}
	$mysqli->close();
}

//Returns the path of the picture
function getPicPath($productID)			//might not be correct
{
	global $dbURL;
  global $username;
  global $password;
  global $database;

  $mysqli = new mysqli($dbURL, $username, $password, $database);
  if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}
  
  //Don't need prepared statements, because the data comes from the website
	$query = "SELECT picture_path FROM product WHERE id = $productID;";
	$result =null;
	$mysqli->real_query($query);
	$result = $mysqli->use_result();
	$path = $result->fetch_row();
	return $path[0];
}

?>