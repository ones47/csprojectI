<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Index</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<!-- Topbar Start-->
	<div class="topbar">
		<div class="topbar-left">
			<img src="img/logo.png" alt="School Logo" class="logo">
			<span class="school-name">St Charles Lwanga</span>
		</div>
		<div class="topbar-right">
			<a href="about.html">About</a>
			<a href="contact.html">Contact</a>
		</div>
	</div>
	<!-- Topbar End-->
	<!-- Login Form Start-->
	<div class="container">
		<h1 class="label">Login</h1>
		<form class="login_form" action="login.php" method="post" name="form" onsubmit="return validated()">
			<?php
			if (isset($_GET['error'])) {
			echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
			}
			?>
			<div class="font">Username:</div>
			<input autocomplete="off" type="text" name="username">
			<div id="email_error">Please fill up with your username</div>
			<div class="font font2">Password:</div>
			<input type="password" name="password">
			<div id="pass_error">Please fill up your Password</div>
			<button type="submit">LOGIN</button>
		</form>
	</div>
	<!-- Login Form End-->
	<!-- Footer Start-->
	<!-- Footer End-->
</body>
</html>