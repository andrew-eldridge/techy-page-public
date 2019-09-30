<?php

    require "mysql-connect.php";
    require "global.php";
	session_start();

	// Initialize variables in scope
    $userid = 0;
    $referrerAddress = encodeURI($_SERVER["REQUEST_URI"]);
    // Social links
    $linkedinHTML = "";
    $githubHTML = "";
	// Content for user connections section
	$connectionsHTML = "";
	$teamsHTML = "";
	$partnershipsHTML = "";
	$projectsHTML = "";

	// Parse URL for query
	$url = $_SERVER["REQUEST_URI"];
	$parts = parse_url($url);
	parse_str($parts["query"], $query);

	// Check for a referrer address
    if (isset($query["referrer"])) {
        $referrer = $query["referrer"];
    } else {
        $referrer = "./";
    }

	// Ensure that a valid user is being searched
	if (isset($query["id"])) {
		$sql = "SELECT
							user.firstname,
							user.lastname,
							user.resourceid,
							user.imageext,
							user.email,
							location.city,
							location.state,
							userdata.description,
							userdata.education,
							userdata.isemployer,
							userdata.isfreelancer,
							userdata.minwage,
							userdata.linkedin,
							userdata.facebook,
							userdata.twitter,
							language.name
						FROM user
						LEFT JOIN location ON user.locationid = location.locationid
						LEFT JOIN userdata ON user.userid = userdata.userid
						LEFT JOIN user_language ON user_language.userid = user.userid
						LEFT JOIN language ON language.languageid = user_language.languageid
						WHERE user.resourceid = '{$query['id']}'";
	} else {
	    redirect($referrer, ERR_INVALID_USER);
	    exit;
	}

	// Get user information
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) > 0) {
        $user = new User();
        while ($row = mysqli_fetch_assoc($result)) {

            // Multiple rows for same user, different languages
            $language = $row["name"];
            array_push($user->languages, $language);
            if ($user->email != null) {
                continue;
            }

            // Basic user info
            $user->name = $row["firstname"] . " " . $row["lastname"];
            $user->resourceid = $row["resourceid"];
            $user->imageext = $row["imageext"];
            $user->email = $row["email"];
            $user->location = $row["city"] . ", " . $row["state"];
            $user->description = $row["description"];
            $user->education = $row["education"];
            $user->isEmployer = $row["isemployer"];
            $user->isFreelancer = $row["isfreelancer"];
            $user->wagerate = "\${$row['minwage']}+";
            $user->linkedin = $row["linkedin"];
            $user->github = $row["github"];

        }
    } else {
	    // Unable to find user document
        redirect($referrer, ERR_INVALID_USER);
        exit;
    }

	// Check for custom profile picture
    if ($user->imageext != null) {
        $imagepath = 'data/users/images/' . $user->resourceid . $user->imageext;
    } else {
        $imagepath = 'images/accounts.png';
    }

    // Social media links
    if ($user->linkedin != null) {
        $linkedinLink = "<a href='{$user->linkedin}'>{$user->linkedin}</a>";
        $linkedinHTML = "<div><img src='images/linkedinicon.png' />{$linkedinLink}</div>";
    }
    if ($user->github != null) {
        $githubLink = "<a href='{$user->github}'>{$user->github}</a>";
        $githubHTML = "<div><img src='images/githubicon.svg' />{$githubLink}</div>";
    }

    // Get userid from resourceid
    $sql = "SELECT userid FROM user WHERE resourceid = '{$query['id']}';";
    $result = mysqli_query($conn, $sql);
    $rows = mysqli_num_rows($result);
    if ($rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userid = $row["userid"];
        }
    } else {
        // Unable to locate userid
        redirect($referrer, ERR_UNEXPECTED);
    }

    // Retrieve connections
    $sql = "SELECT * FROM connection WHERE connection.userid1 = {$userid} OR connection.userid2 = {$userid};";
    $result = mysqli_query($conn, $sql);
    $rows = mysqli_num_rows($result);
    if ($rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row["userid1"] == $userid) {
                $sql = "SELECT firstname, lastname, resourceid FROM user WHERE user.userid = {$row['userid2']};";
            } else {
                $sql = "SELECT firstname, lastname, resourceid FROM user WHERE user.userid = {$row['userid1']};";
            }
            $connectionResult = mysqli_query($conn, $sql);
            $rows = mysqli_num_rows($connectionResult);
            if ($rows > 0) {
                while ($connectionRow = mysqli_fetch_assoc($connectionResult)) {
                    if ($_SESSION["uid"] == $userid) {
                        $deleteConnectionHTML = "<a href='connect?delete=1&id={$connectionRow["resourceid"]}&referrer={$referrerAddress}'><img src='images/delete.png' /></a>";
                    } else {
                        $deleteConnectionHTML = "";
                    }
                    $connectionsHTML .= "<div class='subsection connection'><a href='viewprofile?id={$connectionRow["resourceid"]}'>{$connectionRow["firstname"]} {$connectionRow["lastname"]}</a>{$deleteConnectionHTML}</div>";
                }
            }
        }
    }

    // Custom HTML
    $imageHTML = "<img src='{$imagepath}' />";
    $resumePath = "data/users/resumes/{$user->resourceid}.pdf";
    if (file_exists($resumePath)) {
        $actionsHTML = "<div class='action'><a class='btn btn-primary' role='button' href='{$resumePath}' target='_blank'>View Resume</a></div>";
    }

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo("$user->name - Techy.page"); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
	<link rel='stylesheet' type='text/css' href='styles/main.css' />
	<link rel='stylesheet' type='text/css' href='styles/profilepage.css' />
	<link rel='shortcut icon' href='images/favicon.ico' />
</head>
<body onresize='updateUI()' onload='updateUI()'>
	<div id='wrapper'>
		<?php
			include 'data/patterns/page-contents/banner.php';
			include 'data/patterns/page-contents/navigation.php';
			include 'data/patterns/page-contents/dropdowncontent.php';
		?>
		<main>
			<div class='section'>
				<div class='headerinfo'>
					<div class='imagecontainer'>
                        <?php echo $imageHTML; ?>
					</div>
					<div class='usercontainer'>
						<div class='name'><?php echo $user->name; ?></div>
						<div class='description'><?php echo $user->description; ?></div>
					</div>
					<div class='connect'>
						<a href='connect?id=<?php echo $user->resourceid; ?>&referrer=viewprofile'>
							<img src='images/network.png' />
						</a>
					</div>
				</div>
				<div class='credentialinfo'>
					<div class='socialnetworkinglinks'>
						<?php echo $linkedinHTML; ?>
						<?php echo $githubHTML; ?>
					</div>
					<div class='actions'>
                        <?php echo $actionsHTML; ?>
					</div>
				</div>
				<div class='networks'>
					<div class='networksection'>
						<header class='networkheader'>Connections</header>
						<div class='networkcontent'><?php echo $connectionsHTML; ?></div>
					</div>
					<div class='networksection'>
						<header class='networkheader'>Teams</header>
						<div class='networkcontent'><?php echo $teamsHTML; ?></div>
					</div>
					<div class='networksection'>
						<header class='networkheader'>Partnerships</header>
						<div class='networkcontent'><?php echo $partnershipsHTML; ?></div>
					</div>
					<div class='networksection'>
						<header class='networkheader'>Projects</header>
						<div class='networkcontent'><?php echo $projectsHTML; ?></div>
					</div>
				</div>
			</div>
		</main>
	</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src='scripts/main.js'></script>
</body>
</html>