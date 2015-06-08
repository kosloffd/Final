<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();
if(!isset($_POST['checkout']) && !isset($_POST['addAddress']) && !isset($_POST['finalCheckout'])) 
{
	//If they wandered here by mistake, send them to the cart or the front page
	if(isset($_SESSION['cart']))
	{ header("Location: cart.php"); }
	else
	{ header("Location: FrontPage.php"); }
}
require_once('header.php');
require_once('../../info/dbinfo.php');




if((isset($_POST['checkout']) && count(getAddresses()) == 0) || isset($_POST['addAddress']))
{			
	//boolean, whether to show an input form on the page
	$needForm = false;
	//I there are no addresses on file
	$addressArray = getAddresses();
	
	//If they just filled out the form
	if(isset($_POST['addAddress']))
	{
		$street = $_POST['street'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip= $_POST['zip'];
		$default = true;
		
		//Check to make sure all fields are filled in
		if($street == "" || $city == "" || $state== "" || $zip== "")
		{
			echo '<h3>We didn\'t get all the information. Please fill out all fields.</h3><br>';	
			$needForm = true;
		}	
		//if all the information is filled, push it to the DB, refresh the page, which will make it show under "address on file"	
		else
		{
			submitAddress($street, $city, $state, $zip, $default);
			header("refresh:3");
		}
	}

	//if they DIDN'T just fill out the form, but they don't have an address on file
	else if(count($addressArray) == 0 )
	{
			echo '<h3>It looks like we\'re going to need your address first.</h3>';
			$needForm = true;
	}

	//show the address fields
	if($needForm == true)
	{
		echo
		'<form method="POST" id="addressForm">
		<fieldset>
		<legend>Please enter your address</legend>
		<label>Street:</label>
		<input type="text" name="street" required>
		<label>City:</label>
		<input type="text" name="city" required>
		<label>State:</label>
		<input type="text" name="state" required>
		<label>Zip:</label>
		<input type="number" name="zip" required>
		<label id=chklabel>Default:</label>
		<input type="checkbox" name="default" value="checked">
		<input type="hidden" name="addAddress" value="true">
		</fieldset>
		<button>Add</button>
		</form>';
	}
}
	//They have an address on file, we just need to retrieve it
else if(isset($_POST['checkout']))
{
	echo '<h3>This is the address we have on file for you:</h3>';
	$addressArray = getAddresses();
	$id = key($addressArray);
	$address = $addressArray[$id];
	$street = $address['street'];
	$city = $address['city'];
	$state = $address['state'];
	$zip = $address['ZIP'];

	echo "<div id = showAddress>
	<h4>$street</h4>
	<h4>$city</h4>
	<h4>$state</h4>
	<h4>$zip</h4></div>";
	echo '<div id="cfButtons"><form method="POST"><input type="hidden" name="finalCheckout" value="true"><button>Checkout</button></form>';
	echo '<form method="POST"><input type="hidden" name="addressChange" value="true"><button>Edit Address</button></form>';
}


else if((isset($_POST['finalCheckout'])))
{
	$order = $_SESSION['cart'];
	if(makePurchase($order))
	{
		unset($_SESSION['cart']);
		echo '<h3>Your order has been processed!<br><h4>Click <a href="FrontPage">here</a> to return to the front page.';
	}
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
	$orderQuery = "INSERT INTO `order` (fk_customer_id) VALUES ('$userID');";
	if(!($mysqli->real_query($orderQuery)))
	{
		echo "Couldn't update the order table.(" . $mysqli->errno . ") " . $mysqli->error;
	}

	//now update the many-to-many table
	$orderID = $mysqli->insert_id;
	foreach ($productArray as $key => $value) 
	{
		$orderProductQuery = "INSERT INTO order_product(fk_order_id, fk_product_id, quantity) VALUES ('$orderID','$key', '$value');";

		if(!($mysqli->real_query($orderProductQuery)))
		{
			echo "Error updating the order_product table at product value $key(" . $mysqli->errno . ") " . $mysqli->error;
		}
	}
	return true;
}
function getAddresses()
{
	global $dbURL;
  global $username;
  global $password;
  global $database;

  $mysqli = new mysqli($dbURL, $username, $password, $database);
  if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

  $query = "SELECT a.id, street, city, state, zip FROM address a 
		INNER JOIN customer_address ca ON ca.fk_address_id = a.id
		INNER JOIN customer c ON c.id = ca.fk_customer_id
		WHERE c.id = ? and a.default_address=true;";
  
  if(!($stmt = $mysqli->prepare($query)))
  {
      echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  
  $userID = $_SESSION['validUser'];
  //Bind
	if(!$stmt->bind_param("i", $userID))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}

  //Execute
  if(!$stmt->execute())
  {
      echo "Couldn't execute 'Get Addresses' statement: (" . $mysqli->errno . ") " . $mysqli->error;
  }

  $id = NULL;
  $street = NULL;
  $city = NULL;
  $state = NULL;
  $ZIP = NULL;
  $addresses = array();
  
  if(!($stmt->bind_result($id, $street, $city, $state, $ZIP)))
  {
      echo "Couldn't bind the result (".$mysqli->errno.") ".$mysqli->error;
  }
  while($stmt->fetch())                                   //just get the count of names with that email (1 at most) and store it in $result
  {
      $addresses[$id] = array("street"=>$street, "city"=>$city, "state"=>$state, "ZIP"=>$ZIP);     
  }

  $stmt->close();
  return $addresses;
}

function submitAddress($street, $city, $state, $zip, $default)
{
	global $dbURL;
  global $username;
  global $password;
  global $database;

  $mysqli = new mysqli($dbURL, $username, $password, $database);
  if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}
  
  $query = "INSERT INTO address(street, city, state, zip, default_address) VALUES (?,?,?,?,?);";
	

	//Prepare
	if(!($stmt = $mysqli->prepare($query)))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$street = mysqli_real_escape_string($mysqli, $street);
	$city = mysqli_real_escape_string($mysqli, $city);
	$state = mysqli_real_escape_string($mysqli, $state);
	$zip = mysqli_real_escape_string($mysqli, $zip);
	//Bind
	if(!$stmt->bind_param("sssii", $street, $city, $state, $zip, $default))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Update Address' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$addressID = $mysqli->insert_id;
	$userID = $_SESSION['validUser'];
	$secondquery = "INSERT INTO customer_address(fk_address_id, fk_customer_id) VALUES ('$addressID','$userID');";

	if(!($mysqli->real_query($secondquery)))
	{
		echo "Couldn't update the customer_address table.(" . $mysqli->errno . ") " . $mysqli->error;
	}

	$stmt->close();
	$mysqli->close();
}

?>