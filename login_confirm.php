<?php

	include_once "mysql-connect.php";

	$email = mysqli_real_escape_string($conn, stripcslashes($_POST["email"]));
	$password = mysqli_real_escape_string($conn, stripcslashes($_POST["password"]));
	$found = False;

	$query = "SELECT
                user.userid,
                user.password,
                user.firstname,
                user.lastname,
                user.email,
                user.resourceid,
                user.imageext,
                userdata.isfreelancer,
                userdata.isemployer
	        FROM user
	        LEFT JOIN userdata ON user.userid = userdata.userid
	        WHERE email = '$email';";
	$result = mysqli_query($conn, $query);
	$rows = mysqli_num_rows($result);
	if ($rows > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			if (password_verify($password, $row['password'])) {
				session_start();
				$_SESSION["id"] = session_create_id();
				$_SESSION["uid"] = $row['userid'];
				$_SESSION["firstname"] = $row['firstname'];
				$_SESSION["lastname"] = $row['lastname'];
				$_SESSION["email"] = $row['email'];
				$_SESSION["resourceid"] = $row['resourceid'];
				if (isset($row["imageext"])) {
					$_SESSION["imagepath"] =  "data/users/images/" . $row["resourceid"] . $row["imageext"];
				} else {
					unset($_SESSION["imagepath"]);
				}
				if ($row["isfreelancer"] && $row["isemployer"]) {
				    $_SESSION["usertype"] = "both";
                } else if ($row["isfreelancer"]) {
				    $_SESSION["usertype"] = "freelancer";
                } else if ($row["isemployer"]) {
				    $_SESSION["usertype"] = "employer";
                }
				$found = True;
			};
		};
	};

	if ($found) {
		header("Location: ./");
	};

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php if ($found) {
			echo("Login Successful - Techy.page");
		} else {
			echo("Login Failed - Techy.page");
		} ?></title>
		<link rel="stylesheet" type="text/css" href="styles/login_signup.css" />
	</head>
	<body>
		<div class="container">
			<div class="item">
				<?php if ($found) {
					$firstname = null;
					$query = "SELECT firstname FROM user WHERE email = '$email';";
					$result = mysqli_query($conn, $query);
					while($row = mysqli_fetch_assoc($result)) {
						$firstname = $row['firstname'];
					};
					header("Location: ./");
				} else {
					echo("Login attempt for " . $email . " failed, please try again");
				}; ?>
				<a href="login.html"><button style="font-size:24px; position: absolute; left: 0; bottom: -50px; color: red;">Return</button></a>
			</div>
		</div>
	</body>
</html>