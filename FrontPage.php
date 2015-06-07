<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();

require_once('header.php');
require_once('queries.php');
?>



<!--This appears only for admin, plus other info to POST over to request.php, which will
upload the file and call addProduct($filepath, ...)-->

<?php 
// if($_SESSION['validUser'] == 'admin')
// { 
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
// }

echo '<table>';

$productArray = getAllProducts();
foreach ($productArray as $product) 
{
    $picPath = $product['path'];
    $name = $product['name'];
    $desc = $product['desc'];
    $price = $product['price'];
    $product['rating'] == NULL? $rating = "Not Yet Rated" : $rating = $product['rating'];
    echo "<tr><th>$name<th rowspan=\"2\">$desc<tr><td><img src=\"$picPath\" width=\"200\" height=\"200\">
    <tr><th>$price<th>$rating<tr>";
} 

echo'</table>';
?>







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