<?php

	session_start();
	require "mysql-connect.php";
	require "global.php";

    // Populate message banner
    $bannerHTML = populateMessageBanner();

	$users = array();
    $url = $_SERVER["REQUEST_URI"];

    // Parse url for query string
	$parts = parse_url($url);
	parse_str($parts["query"], $query);
	$referrerAddress = encodeURI($_SERVER["REQUEST_URI"]);

	if (isset($query["q"])) {

		$param = $query["q"];
		if ($param != "") {
            $title = "$param - Techy.page";
        } else {
		    $title = "Search - Techy.page";
        }

		// Generic query for user matching search bar specifications
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
						userdata.metadata,
						language.name
					FROM user
					LEFT JOIN location ON user.locationid = location.locationid
					LEFT JOIN userdata ON user.userid = userdata.userid
					LEFT JOIN user_language ON user_language.userid = user.userid
					LEFT JOIN language ON language.languageid = user_language.languageid
					WHERE firstname LIKE '%{$param}%' OR lastname LIKE '%{$param}%' OR email LIKE '%{$param}%' OR city LIKE '%{$param}%' OR state LIKE '%{$param}%' OR description LIKE '%{$param}%' OR education LIKE '%{$param}%' OR minwage LIKE '%{$param}%' OR name LIKE '%{$param}%' OR metadata LIKE '%{$param}%'";

		// Edit query based on 'connection type' search param
		if (isset($query["restrict"])) {
			if ($query["restrict"] == "freelancer") {
				$sql .= " AND isfreelancer = true;";
			} else if ($query["restrict"] == "employer") {
			    $sql .= " AND isemployer = true;";
            } else {
			    $sql .= ";";
            };
		} else {
		    $sql .= ";";
        };

		// Perform db query for users matching search criteria
		$result = mysqli_query($conn, $sql);
		$rows = mysqli_num_rows($result);
		if ($rows > 0) {
			while($row = mysqli_fetch_assoc($result)) {

				// If user object already created, append language from row and continue
				foreach($users as $user) {
					if ($user->email == $row["email"]) {
						array_push($user->languages, $row["name"]);
						continue 2;
					}
				}

				// Basic user information
				$user = new User();
				$user->name = $row["firstname"] . " " . $row["lastname"];
				$user->resourceid = $row["resourceid"];
				$user->imageext = $row["imageext"];
				$user->email = $row["email"];
                $user->description = $row["description"];
                $user->education = $row["education"];
                $user->isEmployer = $row["isemployer"];
                $user->isFreelancer = $row["isfreelancer"];

				// Location information
				if ($row["city"] != null && $row["state"] != null) {
					$user->location = $row["city"] . ", " . $row["state"];
				} else if ($row["state"] != null) {
				    $user->location = $row["state"];
                } else if ($row["city"] != null) {
				    $user->location = $row["city"];
                };

				// Language information
				if ($row["name"] != null) {
					array_push($user->languages, $row["name"]);
				};

				// Wage information
				if ($row["minwage"] != 0) {
				    $user->wagerate = "\${$row['minwage']}+";
                } else {
				    $user->wagerate = "N/A";
                };

				array_push($users, $user);

			}
		}
	} else {
	    $title = "Search - Techy.page";
    };

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="styles/main.css" />
	<link rel="stylesheet" type="text/css" href="styles/search.css" />
	<link rel="shortcut icon" href="images/favicon.ico" id="favicon" />
</head>
<body onresize = "updateUI()" onload="updateUI()">
	<div id="wrapper">
		<?php
            echo $bannerHTML;
			include "data/patterns/page-contents/banner.php";
			include "data/patterns/page-contents/navigation.php";
			include "data/patterns/page-contents/dropdowncontent.php";
		?>
		<main>
            <div id="searchbar">
                <form action="search" method="GET">
                    <input id="searchbaritem" type="text" name="q" size="20" placeholder="Search a skill" />
                    <input id="search" type="image" src="images/search.png" border="0" alt="Submit" />
                </form>
            </div>
            <div class="section">
                <?php

                // Indicate whether a connection type is specified in search criteria
                if (isset($query["restrict"])) {
                    $searchTypeIndicator = " from {$query['restrict']}s";
                } else {
                    $searchTypeIndicator = "";
                }

                // Indicate whether a search phrase was entered
                if ($query["q"] != null) {
                    $headerMessage = "Showing results for \"{$query['q']}\"" . $searchTypeIndicator;
                } else {
                    $headerMessage = "Showing results for global search" . $searchTypeIndicator;
                }

                // Print header message
                echo "<span style='font-size: 24px; margin-bottom: 20px; width:100%;'>{$headerMessage}</span>";

                // Create an template for each user entry
                $entryTemplate = "<div class='subsection'><div class='info-container'><div class='imagecontainer'><img src='[imagePath]' /></div><div class='credentials'>[nameHTML][userTypeHTML][wageHTML][locationHTML][skillHTML]</div></div><div class='actions'><div class='actions-container'><div class='action'><a class='btn btn-primary' href='viewprofile?id=[resourceId]&referrer={$referrerAddress}'>View profile</a></div><div class='action'><a class='btn btn-primary' href='connect?id=[resourceId]&referrer={$referrerAddress}'>Make Connection</a></div></div></div></div>";

                // Iterate through each user document
                foreach($users as $user) {
                    $languageList = "";
                    $wageHTML = "";
                    $locationHTML = "";
                    $skillHTML = "";

                    // Name HTML
                    $nameHTML = "<div class='name'>{$user->name}</div>";

                    // User Type HTML
                    if ($user->isEmployer && $user->isFreelancer) {
                        $userTypeHTML = "<div class='usertype'>Employer, Freelancer</div>";
                    } else if ($user->isEmployer) {
                        $userTypeHTML = "<div class='usertype'>Employer</div>";
                    } else if ($user->isFreelancer) {
                        $userTypeHTML = "<div class='usertype'>Freelancer</div>";
                    } else {
                        $userTypeHTML = "";
                    }
                    
                    // Wage HTML
                    if ($user->wagerate != null && $user->isFreelancer) {
                        $wageHTML = "<div class='wagerate'>Hourly Rate: {$user->wagerate}</div>";
                    }

                    // Location HTML
                    if ($user->location != null) {
                        $locationHTML = "<div class='location'>{$user->location}</div>";
                    };

                    // TODO: improve display method for languages
                    // Stringify the user's language list
                    foreach($user->languages as $language) {
                        if ($languageList != "") {
                            $languageList .= ", " . $language;
                        } else {
                            $languageList .= " " . $language;
                        };
                    };

                    // Skill HTML
                    if ($languageList != null) {
                        $skillHTML = "<div class='languages'><b>Skilled in:</b>{$languageList}</div>";
                    };

                    // Check for uploaded profile picture
                    if ($user->imageext != null) {
                        $imagePath = 'data/users/images/' . $user->resourceid . $user->imageext;
                    } else {
                        $imagePath = 'images/accounts.png';
                    };

                    // Use template for creating user entries
                    $userEntry = $entryTemplate;

                    // Replace tokens in template for user entry
                    $userEntry = str_replace("[nameHTML]", $nameHTML, $userEntry);
                    $userEntry = str_replace("[userTypeHTML]", $userTypeHTML, $userEntry);
                    $userEntry = str_replace("[wageHTML]", $wageHTML, $userEntry);
                    $userEntry = str_replace("[locationHTML]", $locationHTML, $userEntry);
                    $userEntry = str_replace("[skillHTML]", $skillHTML, $userEntry);
                    $userEntry = str_replace("[resourceId]", $user->resourceid, $userEntry);
                    $userEntry = str_replace("[imagePath]", $imagePath, $userEntry);

                    // Print result
                    echo $userEntry;
                }
                ?>
            </div>
        </main>
	</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="scripts/main.js"></script>
</body>
</html>