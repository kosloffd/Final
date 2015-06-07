<link rel=\"stylesheet\" href=\"style.css\">
<div class="header">
	<h1>Welcome to Sellit</h1>
	<div class="headerLogin">

<?php
	if(isset($_POST['logout']) && $_POST['logout'] == true)
	{
		unset($_SESSION['validUser']);
		session_destroy();
		echo "You have been logged out!";
	}
	//if there is no user logged in, show the login fields
	if(!isset($_SESSION['validUser']))
	{ 
?>
		<form>
		<fieldset legend="login">
			<label>Email:</label>
			<input type="text" id="email" required>
			<label>Password:</label>
			<input type="password" id="password" required>
			<button type="submit" id="loginButton">login</button>
		</fieldset>
		</form>
	</div>
	<p>or <a href="createAccount.html">signup</a></p>
	
	<script type="text/javascript">	(function addButtonAction()
	{
		document.getElementById("loginButton").onclick = function() 
		{ 
		  if(document.getElementById("email").value === "")
		  	{ alert("You need to enter your email."); }
		  else if(document.getElementById("password").value === "")
		    { alert("You need to enter your password."); }
		  else
		  {
		    var pass=document.getElementById("password").value;
		    var email= document.getElementById("email").value;
		    makeRequest('request.php', email, pass); 
		  }
		};
	})()
	</script>
	
<?php	
	}
	else
	{
		$name = $_SESSION['validUser'];
		echo '<div class="headerLogin">You are logged in as <em>'.$name.'</em>. 
		<form method="POST">
		<input type="hidden" name="logout" value="true">
		<button type="submit">logout</button></form></div>';
	}
	//logout button function goes here
?>
</div>

<script type="text/javascript">
function makeRequest(url, email, pass)
{
	var httpRequest;

	if(window.XMLHttpRequest)
	{ httpRequest = new XMLHttpRequest(); }
	else if (window.ActiveXObject)
	{
		try
		{ httpRequest = new ActiveXObject("Msxml2.XMLHTTP"); }
		catch (e)
		{
			try
			{ httpRequest = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (e)	{}
		}
	}
	if(!httpRequest)
	{
		alert("Cannot create a request");
		return false;
	}

	httpRequest.onreadystatechange = validateUser;
	httpRequest.open('POST', url);
	httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	var postData = "email=" + encodeURIComponent(email) + "&pass=" + encodeURIComponent(pass);
	httpRequest.send(postData);

	function validateUser()
	{	
    if (httpRequest.readyState === 4) 
    {
      if (httpRequest.status == 200) 
      {
				alert(httpRequest.responseText);
/*21
        //code 2 means the email is not in the database
				if(httpRequest.responseText === 2)
				{
					var body = getElementsByTagName("body");
					var para = document.createElement("p");
					var text1 = document.createTextNode("This email hasn't been registered yet.<br>Click ");
					var text2 = document.createTextNode(" here to create an account.");
					var link = createLink("createAccount.php", "here");
					para.appendChild(text1);
					para.appendChild(link);
					para.appendChild(text2);
					body.appendChild(para);	
				}
				//code 1 means the email and password were found
				else if(httpRequest.responseText === 1)
				{
					var body = getElementsByTagName("body");
					var message = document.createElement("h2");
					message.appendChild(createTextNode("You have successfully logged in!"));
					body.appendChild(message);
				}
				//code 0 means the email was found, but it's not the right password
				else if(httpRequest.responseText === 0)
				{alert("Your password is incorrect, please enter it again.");}

				else
					{alert("An invalid response was received.  Please try again.");}*/
			}
			else
			{
				alert(httpRequest.status + "\n -the connection could not be established");
			}
		}	
	}
}


function createLink(url, title)
{
	var link = document.createElement("a");
	var line = document.createElement("p");
	link.setAttribute("href", url);
	var textLine = document.createTextNode(title);
	line.appendChild(link);
	line.appendChild(textLine);
	return line;
}
</script>