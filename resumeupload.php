<?php

	session_start();
	$result = "";
	if (isset($_SESSION["resourceid"])) {
		$filepath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . "resumes" . DIRECTORY_SEPARATOR . $_SESSION["resourceid"] . ".pdf";
	} else {
		header("Location: ./");
	};

	if (isset($_FILES["resume"])) {
		if ($_FILES["resume"]["name"] != null) {
			// Checks that user doesn't already have a resume
			if (file_exists($filepath)) {
				$result = "This account already has a resume. Delete the old version before uploading another.";
			} else {
				// Moves temporary file to permanent location
				if (move_uploaded_file($_FILES["resume"]["tmp_name"], $filepath)) {
					$result = "Resume successfully uploaded!";
				} else {
					
					$result = "An error occurred while uploading resume.";
				};
			};
		} else {
			header("Location: ./");
		};
	};

?>

<!DOCTYPE html>
<html>
<head>
	<title>Upload a Resume - Techy.page</title>
	<link rel="stylesheet" type="text/css" href="styles/login_signup.css" />
	<link rel="shortcut icon" href="images/favicon.ico" />
</head>
<body>
	<div class="container">
		<div class="item">
			<form enctype="multipart/form-data" action="resumeupload" method="post">
				<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
				<input id="resume" name="resume" type="file" accept="application/pdf" required />
				<input type="submit" value="Submit Resume" />
			</form>
			<div style="color: darkred; padding-top: 10px;">
				<?php echo "$result"; ?>
			</div>
			<a href="./"><button style="font-size:24px; position: absolute; left: 0; bottom: -50px; color: red;">Return</button></a>
		</div>
	</div>
</body>
</html>