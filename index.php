<?php

	session_start();
    require "mysql-connect.php";
    require "global.php";

    // Populate message banner
    $bannerHTML = populateMessageBanner();

	// Dynamic page material
	$recommendedActionsLeft = "";
	$recommendedActionsRight = "";

	// TODO: determine whether the user has a portfolio
    // TODO: determine whether the user has a resume
    // TODO: determine whether the user has met the recommended threshold for skills

    // Suggested action possibility templates
    $searchEmployersHTML = '<form action="./" method="get"><input type="hidden" name="q" value="" /><input type="hidden" name="restrict" value="employer" /><input class="btn btn-primary btn-block" type="submit" value="Search Employers" />';
    $searchFreelancersHTML = '<form action="./" method="get"><input type="hidden" name="q" value="" /><input type="hidden" name="restrict" value="freelancer" /><input class="btn btn-primary btn-block" type="submit" value="Search Freelancers" />';

    // Suggest connections based on user type
    if (isset($_SESSION["usertype"])) {
        if ($_SESSION["usertype"] == "both") {
            $recommendedActionsRight .= $searchEmployersHTML;
            $recommendedActionsRight .= $searchFreelancersHTML;
        } else if ($_SESSION["usertype"] == "freelancer") {
            $recommendedActionsRight .= $searchEmployersHTML;
        } else if ($_SESSION["usertype"] == "employer") {
            $recommendedActionsRight .= $searchFreelancersHTML;
        }
    }

?>
<!DOCTYPE html>
<html>
<head>
	<title>Techy.page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="styles/main.css" />
	<link rel="stylesheet" type="text/css" href="styles/homepage.css" id="stylesheet" />
	<link rel="shortcut icon" href="images/favicon.ico" id="favicon" />
</head>
<body onresize="updateUI()" onload="updateUI()">
	<div id="wrapper">
		<?php
            echo $bannerHTML;
			include "data/patterns/page-contents/banner.php";
			include "data/patterns/page-contents/navigation.php";
			include "data/patterns/page-contents/dropdowncontent.php";
		?>
		<main>
            <div class="section">
                <div id="searchbar">
                    <form action="search" method="GET">
                        <input id="searchbaritem" type="text" name="q" size="20" placeholder="Search a skill" />
                        <input id="search" type="image" src="images/search.png" border="0" alt="Submit" />
                    </form>
                </div>
                <div class="sectioncontentleft">
                    <div class="sectionheader">
                        Improve your profile
                    </div>
                    <div class="subsection activitiessubsection">
                        <header>Suggested Actions</header>
                        <div class="formcontainer">
                            <div class="container-content-left">
                                <?php echo $recommendedActionsLeft; ?>
                                <form action="resumeupload">
                                    <input class="btn btn-primary btn-block" type="submit" value="Upload a Resume" />
                                </form>
                                <form method="get">
                                    <input type="hidden" name="msg" value="<?php echo ERR_INCOMPLETE_FEATURE; ?>" />
                                    <input class="btn btn-primary btn-block" type="submit" value="Add Skills" />
                                </form>
                            </div>
                            <div class="container-content-right">
                                <form method="get">
                                    <input type="hidden" name="msg" value="<?php echo ERR_INCOMPLETE_FEATURE; ?>" />
                                    <input class="btn btn-primary btn-block" type="submit" value="Build a Portfolio" />
                                </form>
                                <?php echo $recommendedActionsRight; ?>
                            </div>
                        </div>
                    </div>
                    <div class="subsection activitiessubsection">
                        <header>Link Accounts</header>
                        <div class="formcontainer">
                            <div class="container-content-left">
                                <form method="get">
                                    <input type="hidden" name="form" value="linkedinform" />
                                    <input class="btn btn-primary btn-block" type="submit" value="LinkedIn" />
                                </form>
                            </div>
                            <div class="container-content-right">
                                <form method="get">
                                    <input type="hidden" name="form" value="githubform" />
                                    <input class="btn btn-primary btn-block" type="submit" value="GitHub" />
                                </form>
                            </div>
                        </div>
                        <div class="container-content-bottom">
                            <form id="linkedinform" action="linksocial" method="post">
                                <input type="text" name="linkedinlink" placeholder="Enter LinkedIn link..." required />
                                <input class="btn btn-primary" type="submit" value="Submit" />
                            </form>
                        </div>
                        <div class="container-content-bottom">
                            <form id="githubform" action="linksocial" method="post">
                                <input type="text" name="githublink" placeholder="Enter GitHub link..." required />
                                <input class="btn btn-primary" type="submit" value="Submit" />
                            </form>
                        </div>
                    </div>
                    <div class="sectionheader">
                        Updates
                    </div>
                    <div class="subsection updatessubsection">
                        You are up to date!
                    </div>
                </div>
                <?php

                $connectionContents = "<div class='subsection'><a href='./search?q='>Make a connection</a></div>";
                $teamContents = "<div class='subsection'><a href='./?msg=" . ERR_INCOMPLETE_FEATURE . "'>Start a team</a></div>";
                $partnershipContents = "<div class='subsection'><a href='./?msg=" . ERR_INCOMPLETE_FEATURE . "'>Advertise a partnership</a></div>";
                $projectContents = "<div class='subsection'><a href='./?msg=" . ERR_INCOMPLETE_FEATURE . "'>Upload a project</a></div>";

                if (isset($_SESSION["uid"])) {

                    // Retrieve connections
                    $query = "SELECT * FROM connection WHERE connection.userid1 = " . $_SESSION['uid'] . " OR connection.userid2 = " . $_SESSION['uid'] . ";";
                    $result = mysqli_query($conn, $query);
                    $rows = mysqli_num_rows($result);
                    if ($rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            if ($row["userid1"] == $_SESSION["uid"]) {
                                $query = "SELECT firstname, lastname, resourceid FROM user WHERE user.userid = " . $row['userid2'] . ";";
                            } else {
                                $query = "SELECT firstname, lastname, resourceid FROM user WHERE user.userid = " . $row['userid1'] . ";";
                            }
                            $connectionResult = mysqli_query($conn, $query);
                            $rows = mysqli_num_rows($connectionResult);
                            if ($rows > 0) {
                                while ($connectionRow = mysqli_fetch_assoc($connectionResult)) {
                                    $connectionContents .= "<div class='subsection connection'><a href='viewprofile?id={$connectionRow["resourceid"]}'>{$connectionRow["firstname"]} {$connectionRow["lastname"]}</a><a href='connect?delete=1&id={$connectionRow["resourceid"]}'><img src='images/delete.png' /></a></div>";
                                }
                            }
                        }
                    }

                    // Retrieve teams
                    $query = "SELECT team.name, team.resourceid FROM team_connection LEFT JOIN team ON team_connection.teamid = team.teamid WHERE team_connection.userid = " . $_SESSION['uid'] . ";";
                    $result = mysqli_query($conn, $query);
                    $rows = mysqli_num_rows($result);
                    if ($rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $teamContents .= "<div class='subsection'><a href='./data/networks/teams/{$row["resourceid"]}'>{$row["name"]}</a></div>";
                        }
                    }

                    ////////////////////////////////////////////////
                    // TODO: Acquire user's business partnerships //
                    ////////////////////////////////////////////////

                    // Retrieve projects
                    $query = "SELECT project.name, project.resourceid FROM user_project LEFT JOIN project ON project.projectid = user_project.projectid WHERE user_project.userid = " . $_SESSION['uid'] . ";";
                    $result = mysqli_query($conn, $query);
                    $rows = mysqli_num_rows($result);
                    if ($rows > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $projectContents .= "<div class='subsection'><a href='./data/projects/{$row["resourceid"]}'>{$row["name"]}</a></div>";
                        }
                    }
                }

                ?>
                <div class="sectioncontentright">
                    <div class="sectionheader">
                        Connections
                        <img src="images/network.png" />
                    </div>
                    <?php echo "$connectionContents"; ?>
                    <div class="sectionheader">
                        Teams
                        <img src="images/team.png" />
                    </div>
                    <?php echo "$teamContents"; ?>
                    <div class="sectionheader">
                        Partnerships
                        <img src="images/business.png" />
                    </div>
                    <?php echo "$partnershipContents"; ?>
                    <div class="sectionheader">
                        Projects
                        <img src="images/share.png" />
                    </div>
                    <?php echo "$projectContents"; ?>
                </div>
            </div>
		</main>
	</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="scripts/main.js"></script>
    <?php
        // Display requested form
        $url = $_SERVER["REQUEST_URI"];
        $parts = parse_url($url);
        if (isset($parts["query"])) {
            parse_str($parts["query"], $query);
            if (isset($query["form"])) {
                echo "<script>displayForm('{$query["form"]}')</script>";
            }
        }
    ?>
</body>
</html>
