<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();
require_once('header.php');
require_once('../../info/dbinfo.php');
?>



<!--This appears only for admin, plus other info to POST over to request.php, which will
upload the file and call addProduct($filepath, ...)-->

<?php 
//The admin account id is 14, pass: BrokenPlane
if(isset($_SESSION['validUser']) && $_SESSION['validUser'] == '14')
{ 
?>

<form enctype="multipart/form-data" action="request.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="500000">
    Image: <input name="uploadPic" type="file" >
    Title: <input type="text" name="name">
    Description: <input type="textarea" name="desc">
    Price: <input type="number" step="0.01" name="price">
    <input type="submit" value="Upload" >
</form>

<?php 
 }

echo '<table>';

$productArray = getAllProducts();
foreach ($productArray as $key => $product) 
{
    $id = $key;
    $picPath = $product['path'];
    $name = $product['name'];
    $desc = $product['desc'];
    $price = "$" . $product['price'];
    $product['rating'] == NULL? $rating = "Not Yet Rated" : $rating = $product['rating'];
    echo "<tr><td rowspan=\"3\">
    <form action=\"request.php\" method=\"POST\"><input type=\"hidden\" name=\"$id\" value=\"$price\"><button>Buy</button></form>
    <th>$name<th rowspan=\"2\">$desc<tr><td><img src=\"$picPath\" width=\"100\" height=\"100\">
    <tr><th>$price<th>$rating<tr>";
} 

echo'</table>';
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
    while($stmt->fetch())                                   //just get the count of names with that email (1 at most) and store it in $result
    {
        $products[$id] = array("name"=>$name, "desc"=>$desc, "path"=>$picPath, "price"=>$price, "rating"=>$rating);     
    }

    $stmt->close();
    $mysqli->close();
    return $products;
}
?>

<script type="text/javascript"></script>







<!--

Normal Front Page with a login button
loginBtn onclick = login();

//to display items in database -takes username or use username global
function pageDisplay() -Javascript (no page refresh) or PHP (must refresh the page)
if(isset($username && $username != admin))
{
	show the user's recommmended items / recent orders
	display($query)
}

else
{
	just show the regular front page
}

//function $login()
if the login button is clicked, pop up a window for authentication
call query for count of users in dB with that name and SHA1(password)
if(count!=0)
{
	set global $username = username;
	pageDisplay();
}

//Query function
returns the array of items from the dB

//



-->