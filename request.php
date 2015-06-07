<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();
header('Content-Type: text/html');
require_once('queries.php');


$testPage = 'http://web.engr.oregonstate.edu/~kosloffd/Testing/FrontPage.php';
$loginPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/FrontPage.php';
$signUpPage = 'http://web.engr.oregonstate.edu/~kosloffd/Final/createAccount.html';
$uploadDir = '/nfs/stak/students/k/kosloffd/public_html/Final/images/';


if($_SERVER['HTTP_REFERER'] == $loginPage || $_SERVER['HTTP_REFERER'] == $testPage)
{
	//If the user is logging in
	if(isset($_POST["pass"]) && isset($_POST["email"]))
	{
		$email = $_POST["email"];
		$pass = $_POST["pass"];
		if(!checkEmailExists($email))
			{ echo 2; }
		else if(validate($email, $pass))
			{ echo 1; }
		else{ echo 0; }
	}

	//if the admin is uploading a file $_SESSION['validUser'] ==="admin" &&
	else if(isset($_FILES['uploadPic']['name']))
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

//need a LOT more error checking here, but check if the request comes from the login page
else if($_SERVER['HTTP_REFERER'] == $signUpPage)
{
	//filter and check errors here if you think of anything to filter

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
		{ addCustomer($fname, $lname, $email, $pass); }
}

else
{
	?>
	<h2>You can't access this page without logging in first.</h2><br>
	<h3>Click <a href="FrontPage.php">here</a> to go to the Front Page.<br>Just click the login button in the top right corner!</h3>
<?php
$ref = $_SERVER['HTTP_REFERER'];
echo "$ref";
}

?>