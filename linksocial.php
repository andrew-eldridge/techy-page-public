<?php

	require "mysql-connect.php";
	require "global.php";
	session_start();

	if (isset($_SESSION["uid"])) {
		if (isset($_POST["linkedinlink"]) && $_POST["linkedinlink"] != null) {
			$linkedinLink = mysqli_real_escape_string($conn, stripcslashes($_POST["linkedinlink"]));
			// Validate link
            $parts = parse_url($linkedinLink);
            if (strpos(strtolower($parts["host"]), "linkedin.com") != -1) {
                // Insert link into user data
                $sql = "UPDATE userdata SET linkedin = '" . strtolower($linkedinLink) . "' WHERE userid = " . $_SESSION["uid"];
                if (!mysqli_query($conn, $sql)) {
                    // LinkedIn link failed
                    header("location: ./?msg=" . ERR_LINK_LINKEDIN);
                    exit;
                } else {
                    // LinkedIn link successful
                    header("location: ./?msg=" . SUC_LINK_LINKEDIN);
                    exit;
                }
            }
		}
		if (isset($_POST["githublink"]) && $_POST["githublink"] != null) {
		    $githubLink = mysqli_real_escape_string($conn, stripcslashes($_POST["githublink"]));
		    // Validate link
            $parts = parse_url($githubLink);
            if (strpos(strtolower($parts["host"]), "github.com") != -1) {
                // Insert link into user data
                $sql = "UPDATE userdata SET github = '" . strtolower($githubLink) . "' WHERE userid = " . $_SESSION["uid"];
                if (!mysqli_query($conn, $sql)) {
                    // GitHub link failed
                    header("location: ./?msg=" . ERR_LINK_GITHUB);
                    exit;
                } else {
                    // GitHub link successful
                    header("location: ./?msg=" . SUC_LINK_GITHUB);
                    exit;
                }
            }
        }
		// No social links were provided
        header("location: ./?msg=" . ERR_INVALID_PARAMS);
		exit;
	} else {
	    // Must be logged in to link social accounts
        header("location: ./?msg=" . ERR_NOT_SIGNED_IN);
        exit;
    }
