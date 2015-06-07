<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();
header('Content-Type: text/html');
require('../../info/dbinfo.php');
require_once('header.php');

$loginPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/FrontPage.php';
$signUpPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/createAccount.html';
$uploadDir = '/nfs/stak/students/k/kosloffd/public_html/Final/images/';
$updateAddressPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/updateAddress.html';

if($_SERVER['HTTP_REFERER'] == $loginPage)
{
	//If the user is logging in
	if(isset($_POST["pass"]) && isset($_POST["email"]))
	{
		$email = $_POST["email"];
		$pass = $_POST["pass"];
		$response = array();

		if(!checkEmailExists($email))
			{ $response['valid'] = 2; }
		else if(validate($email, $pass))
			{ $response['valid'] = 1; }
		else{ $response['valid'] = 0; }

		echo json_encode($response);
	}

	//if the admin is uploading a file 
	else if($_SESSION['validUser'] ==="admin" && isset($_FILES['uploadPic']['name']))
	{
		$uploadfile = $uploadDir . basename($_FILES['uploadPic']['name']);	
		if(move_uploaded_file($_FILES['uploadPic']['tmp_name'], $uploadfile))
		{
			echo "<h2>File is valid, and was successfully uploaded.</h2><br><a href=\"FrontPage.php\">Back</a>";
			$picPath = 'images/' . $_FILES["uploadPic"]["name"];
			$name = $_POST['name'];
			$desc = $_POST['desc'];
			$price = $_POST['price'];
			addProduct($picPath, $name, $desc, $price);
		}
	}

}

//If the request comes from the sign up page
else if($_SERVER['HTTP_REFERER'] == $signUpPage)
{
	$fname = $_POST["fname"];
	$lname = $_POST["lname"];
	$email = $_POST["email"];
	$pass = $_POST["pass"];

	if(checkEmailExists($email)) 
	{
		echo '<h2>That email address is already in use, you must choose a different one.</h2><br>
		<h3>Click <a href="createAccount.html">here</a> to go back.</h3>';
	}
	else
		{ 
			addCustomer($fname, $lname, $email, $pass);
			echo '<h2>Welcome to the site, '. $fname . "! <a href=\"FrontPage.php\">Start shopping<a> now.";
		}
}

//If the user is adding an address
else if($_SERVER['HTTP_REFERER'] == $updateAddressPage)
{
	$street = $_POST['street'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$def = null;
	isset($_POST['default'])? $def=true: $def=false;
	submitAddress($street, $city, $state, $zip, $def);
}

//If the user has come here indirectly, not from a linked page
else
{
	echo "<h2>You can't see this page without logging in first.</h2><br>
	<h3>Just click the login button in the top right corner!</h3>";
	$ref = $_SERVER['HTTP_REFERER'];
	echo "$ref";
}

//This adds a user to the database, all fields are required
function addCustomer($fname, $lname, $email, $pass)
{
	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}
	
	//Prepare
	if(!($stmt = $mysqli->prepare("INSERT INTO customer(firstName, lastName, email, password) 
		VALUES(?,?,?,?)")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$fname = mysqli_real_escape_string($mysqli, $fname);
	$lname = mysqli_real_escape_string($mysqli, $lname);
	$email = mysqli_real_escape_string($mysqli, $email);
	$pass = sha1(mysqli_real_escape_string($mysqli, $pass));

	//Bind
	if(!$stmt->bind_param("ssss", $fname, $lname, $email, $pass))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Add User' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->close();
}

//This validates a user against the names in the database
function validate($email, $pass)
{
	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

	//Prepare
	if(!($stmt = $mysqli->prepare("SELECT COUNT(*), id, firstName FROM customer WHERE email = ? AND 
		password = ?")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Clean input and Bind
	$email = mysqli_real_escape_string($mysqli, $email);
	$pass = sha1(mysqli_real_escape_string($mysqli, $pass));
	if(!$stmt->bind_param("ss", $email, $pass))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Validate Account' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$total = 0;
	$result = 0;
	$tmpname = null;
	$tmpid = 0;
	$id = 0;
	$name = null;
	if(!($stmt->bind_result($result, $tmpid, $tmpname)))
	{
		echo "Couldn't bind the result (".$mysqli->errno.") ".$mysqli->error;
	}
	while($stmt->fetch())									//just get the count of names with that email (1 at most) and store it in $result
	{
		$total += $result;
		$id = $tmpid;
		$name = $tmpname;
	}

	if($total === 1)
	{
		$_SESSION['validUser']=$id;
		$_SESSION['validName']=$name;
		$stmt->close();
		$mysqli->close();
		return true;
	}

	else{	$stmt->close();	return false;}
}

//Check to see if that email address is already in the database returns 0 if
//email not foud and 1 if found 
function checkEmailExists($email)
{
	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

	//Prepare
	if(!($stmt = $mysqli->prepare("SELECT COUNT(*) FROM customer WHERE email = ?")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Bind
	$email = mysqli_real_escape_string($mysqli, $email);
	if(!$stmt->bind_param("s", $email))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Check whether email exists' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$total = 0;
	$result = 0;
	if(!($stmt->bind_result($result)))
	{
		echo "Couldn't bind the result (".$mysqli->errno.") ".$mysqli->error;
	}
	while($stmt->fetch())									//just get the count of names with that email (1 at most) and store it in $result
	{
		$total += $result;
	}
	$stmt->close();
	return $total;
}

//Adds a product to the database
function addProduct($picPath, $name, $desc, $price)
{
	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

	//Prepare
	if(!($stmt = $mysqli->prepare("INSERT INTO product(picture_path, name, description, price)
		VALUES(?,?,?,?);")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Bind
	if(!$stmt->bind_param("sssd", $picPath, $name, $desc, $price))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Check whether email exists' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->close();
	$mysqli->close();
}

function getAddresses()
{
	global $dbURL;
  global $username;
  global $password;
  global $database;

  $mysqli = new mysqli($dbURL, $username, $password, $database);
  if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

  if(!($stmt = $mysqli->prepare("SELECT a.id, street, city, state, zip FROM address a 
		INNER JOIN customer_address ca ON ca.fk_address_id = a.id
		INNER JOIN customer c ON c.id = ca.fk_customer_id
		WHERE c.id = ?")))
  {
      echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  
  $userID = $_SESSION['validUser'];
  //Bind
	if(!$stmt->bind_param("s", $userID))
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
  $mysqli->close();
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

function postReview($productID, $review, $rating)
{

	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

	//Prepare
	if(!($stmt = $mysqli->prepare("INSERT INTO review(fk_customer_id, fk_product_id, review, rating) VALUES(?,?,?,?);")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$userID = $_SESSION['validUser'];
	$review = my_sqli_real_escape_string(mysqli, $review);
	//Bind
	if(!$stmt->bind_param("iisi", $userID, $productID, $review, $rating))
	{
		echo "Couldn't bind parameters: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Check whether email exists' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$stmt->close();
	$mysqli->close();
}

?>