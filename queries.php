<?php
// session_start();
require_once('../../info/dbinfo.php');
?>
<!-- <form action="queries.php" method="POST">
		<fieldset>
			<label>Email:</label>
			<input type="text" id="email" name="email" required>
			<label>Password:</label>
			<input type="password" id="password" name="password" required>
			<button type="submit" id="loginButton">login</button>
		</fieldset>
		</form> -->
<?php

// if(isset($_POST["password"]) && isset($_POST["email"]))
// 	{
// 		$email = $_POST["email"];
// 		$pass = $_POST["password"];
// 		if(!checkEmailExists($email))
// 			{ echo 2; }
// 		else if(validate($email, $pass))
// 			{ echo 1; }
// 		else{ $pass = sha1($pass); echo "0 <br> $pass"; }
// 	}


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
	if(!($stmt = $mysqli->prepare("SELECT COUNT(*), firstName FROM customer WHERE email = ? AND 
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
	$name = NULL;
	if(!($stmt->bind_result($result, $tmpname)))
	{
		echo "Couldn't bind the result (".$mysqli->errno.") ".$mysqli->error;
	}
	while($stmt->fetch())									//just get the count of names with that email (1 at most) and store it in $result
	{
		$total += $result;
		$name = $tmpname;
	}

	if($total === 1)
	{
		$_SESSION['validUser']=$name;
		$stmt->close();
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
		VALUES(?,?,?,?)")))
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
}

function getAllProducts()
{
	global $dbURL;
	global $username;
	global $password;
	global $database;

	$mysqli = new mysqli($dbURL, $username, $password, $database);
	if($mysqli->connect_errno){echo "Failed to connect to MySQL: ".$mysqli->connect_error;}

	if(!($stmt = $mysqli->prepare("SELECT id, picture_path, name, description, price, rating FROM product")))
	{
		echo "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	//Execute
	if(!$stmt->execute())
	{
		echo "Couldn't execute 'Validate Account' statement: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$id = NULL;
	$picPath = NULL;
	$name = NULL;
	$desc = NULL;
	$price = NULL;
	$rating = NULL;
	$products = array();
	
	if(!($stmt->bind_result($id, $picPath, $name, $desc, $price, $rating)))
	{
		echo "Couldn't bind the result (".$mysqli->errno.") ".$mysqli->error;
	}
	while($stmt->fetch())									//just get the count of names with that email (1 at most) and store it in $result
	{
		$products[$id] = array("name"=>$name, "desc"=>$desc, "path"=>$picPath, "price"=>$price, "rating"=>$rating); 	
	}

	$stmt->close();
	$mysqli->close();
	return $products;
}


?>