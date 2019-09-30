<?php

	include_once "mysql-connect.php";

	// Sign up form data
	$firstname = mysqli_real_escape_string($conn, stripcslashes($_POST["firstname"]));
	$lastname = mysqli_real_escape_string($conn, stripcslashes($_POST["lastname"]));
	$email = mysqli_real_escape_string($conn, stripcslashes($_POST["email"]));
	$password = mysqli_real_escape_string($conn, stripcslashes($_POST["password"]));
	$description = mysqli_real_escape_string($conn, stripcslashes($_POST["description"]));
	$minwage = mysqli_real_escape_string($conn, stripcslashes($_POST["minwage"]));
	$city = mysqli_real_escape_string($conn, stripcslashes($_POST["city"]));
	$state = mysqli_real_escape_string($conn, stripcslashes($_POST["state"]));

	// Confirm that critical info was received
	if ($firstname != null && $lastname != null && $email != null && $password != null) {

	    // Convert languages input to an array
		$languages = array();
		if (isset($_POST["languages"])) {
			foreach($_POST["languages"] as $language) {
				array_push($languages, intval($language));
			};
		};

		// Declare some variables
		$isEmployer = false;
		$isFreelancer = false;
		$metadata = "";
        $locationid = 0;
        $userid = null;
        $ext = null;
        $resourceid = null;
        $success = false;
        $hashedpass = password_hash($password, PASSWORD_DEFAULT);

        // Meta data regarding user type
		if (isset($_POST["usertype"])) {
			foreach($_POST["usertype"] as $type) {
				if ($type == "employer") {
					$isEmployer = true;
					$metadata .= "employer";
				} else if ($type == "freelancer") {
					$isFreelancer = true;
					$metadata .= "freelancer";
				};
			};
		};

		// Get profile picture file extension
		if (strrpos($_FILES["profile_pic"]["name"], ".jpg") != null || strrpos($_FILES["profile_pic"]["name"], ".jpeg") != null) {
			$ext = ".jpg";
		};
		if (strrpos($_FILES["profile_pic"]["name"], ".png") != null) {
			$ext = ".png";
		};

		// Assign a unique resourceid to the user for file storage
		$resourceid = uniqid(); // TODO: use uuid for resourceid to prevent overlap

        // Move profile picture to a permanent location
		if ($ext != null) {
			$imagepath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . $resourceid . $ext;
			if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $imagepath)) {
				$success = true;
			};
		} else {
			$success = true;
		};

		// Once the input is validated, start inserting user documents into db
		if ($success) {

			// Insert location and retrieve locationid
			if ($city != null && $state != null) {
				$query = "INSERT INTO location (city, state) VALUES ('$city', '$state');";
				if (!mysqli_query($conn, $query)) {
					echo "Failed to insert location document";
					$success = false;
				} else {
					$query = "SELECT locationid FROM location WHERE location.city = '$city' AND location.state = '$state';";
					$result = mysqli_query($conn, $query);
					$rows = mysqli_num_rows($result);
					if ($rows > 0) {
						while ($row = mysqli_fetch_assoc($result)) {
							$locationid = $row["locationid"];
						}
					} else {
						$success = false;
						echo "An error occurred while retrieving locationid";
					};
				};
			};

			// Insert user, retrieve userid, and set session variables
			$query = "INSERT INTO user (firstname, lastname, email, password, resourceid, imageext, locationid) VALUES ('$firstname', '$lastname', '$email', '$hashedpass', '$resourceid', '$ext', '$locationid');";
			if (!mysqli_query($conn, $query)) {
				$success = false;
				echo "An error occurred while inserting the user document";
			} else {
				session_start();
				$_SESSION["id"] = session_create_id();
				$_SESSION["firstname"] = $firstname;
				$_SESSION["lastname"] = $lastname;
				$_SESSION["email"] = $email;
				$_SESSION["resourceid"] = $resourceid;
				if ($isFreelancer && $isEmployer) {
				    $_SESSION["usertype"] = "both";
                } else if ($isFreelancer) {
                    $_SESSION["usertype"] = "freelancer";
                } else if ($isEmployer) {
                    $_SESSION["usertype"] = "employer";
                }
				$query = "SELECT userid FROM user WHERE user.resourceid = '$resourceid';";
				$result = mysqli_query($conn, $query);
				$rows = mysqli_num_rows($result);
				if ($rows > 0) {
					while ($row = mysqli_fetch_assoc($result)) {
						$userid = $row["userid"];
						$_SESSION["uid"] = $userid;
					}
				} else {
					$success = false;
					echo "An error occurred while retrieving the userid";
				};
				if ($ext != null) {
					$_SESSION["imagepath"] = "data/users/images/" . $resourceid . $ext;
				} else {
					unset($_SESSION["imagepath"]);
				};
			};

			// Insert userdata
			$query = "INSERT INTO userdata (userid, description, isemployer, isfreelancer, minwage, metadata) VALUES ('$userid', '$description', '$isEmployer', '$isFreelancer', '$minwage', '$metadata');";
			if (!mysqli_query($conn, $query)) {
				$success = false;
				echo "An error occurred while inserting the userdata document";
			};

			// Map user to selected languages
			foreach ($languages as $languageid) {
				$query = "INSERT INTO user_language (userid, languageid) VALUES ('$userid', '$languageid');";
				if (!mysqli_query($conn, $query)) {
					$success = false;
				};
			};
		};
	} else {
		$success = false;
	};

	// Redirects to homepage on success
	if ($success) {
		header("Location: ./");
	};

?>

<head>
	<title>Signup Failed - Techy.page</title>
	<link rel="stylesheet" type="text/css" href="styles/login_signup.css" />
</head>
<body>
	<div class="container">
		<div class="item">
			<?php
				echo ("Account creation for $firstname failed");
			?>
			<a href="signup.html"><button style="font-size:24px; position: absolute; left: 0; bottom: -50px; color: red;">Return</button></a>
		</div>
	</div>
</body>