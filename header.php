<?php
  if(isset($_POST['logout']) && $_POST['logout'] == true)
	{
		foreach ($_SESSION as $key => $value) 
		{
			unset($_SESSION[$key]);
		}
		session_destroy();
		header("Location: FrontPage.php");
	}
?>
<div id="header">
	<h1>Welcome to Sellit</h1>
	<div id="headerLogin">

		<?php
			
			//if there is no user logged in, show the login fields
			if(!isset($_SESSION['validUser']))
			{ 
		?>
				<label>Email:
				<input type="text" id="email" required/></label>
				<label>Password:
				<input type="password" id="password" required/></label>
				<span type="button" id="loginButton" style="cursor: pointer; text-decoration: underline">
				  login
				</span>
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
			$name = $_SESSION['validName'];
			echo '<div class="headerLogin">You are logged in as <em>'.$name.'</em>.<form method="POST">
			<input type="hidden" name="logout" value="true"> <button type="submit">logout</button></form></div>';
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
      	var reply = JSON.parse(httpRequest.responseText);
        //code 2 means the email is not in the database
				if(reply['valid'] === 2)
				{
					var header = document.getElementById("header");
					var h2 = document.createElement("h2");
					var text1 = document.createTextNode("This email hasn't been registered yet. Click ");
					var text2 = document.createTextNode(" to create an account.");
					var link = createLink("createAccount.html", "here");
					h2.appendChild(text1);
					h2.appendChild(link);
					h2.appendChild(text2);
					header.appendChild(h2);	
				}
				//code 1 means the email and password were found, just reload the page to update the login heading
				else if(reply['valid'] === 1)
				{
					document.location.reload(true);
				}
				//code 0 means the email was found, but it's not the right password
				else if(reply['valid'] === 0)
				{alert("Your password is incorrect, please enter it again.");}

				else
					{alert("An invalid response was received.  Please try again.");}
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
	link.setAttribute("href", url);
	var textLine = document.createTextNode(title);
	link.appendChild(textLine);
	return link;
}
</script>