<?php

	session_start();
	require "mysql-connect.php";
	require "global.php";

	$url = $_SERVER["REQUEST_URI"];
	$parts = parse_url($url);
	parse_str($parts["query"], $query);

	$userid = null;
	$connectedid = null;

	// Set connectedid and userid variables
	if (isset($query["id"])) {
		$sql = "SELECT userid FROM user WHERE user.resourceid = '" . $query['id'] . "';";
		$result = mysqli_query($conn, $sql);
		$rows = mysqli_num_rows($result);
		if ($rows > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$connectedid = $row["userid"];
			}
		} else {
			//echo "Failed to locate target user";
			header("location: ./?msg=" . ERR_INVALID_USER);
		}

		$sql = "SELECT userid FROM user WHERE user.resourceid = '" . $_SESSION['resourceid'] . "';";
		$result = mysqli_query($conn, $sql);
		$rows = mysqli_num_rows($result);
		if ($rows > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$userid = $row["userid"];
			}
		} else {
			// echo "Could not locate your user document";
            if (isset($query["referrer"])) {
                if (strpos($query["referrer"], "?") != -1) {
                    header("location: {$query["referrer"]}&msg=" . ERR_NOT_SIGNED_IN);
                    exit;
                } else {
                    header("location: {$query["referrer"]}?msg=" . ERR_NOT_SIGNED_IN);
                    exit;
                }
            } else {
                header("location: ./?msg=" . ERR_NOT_SIGNED_IN);
                exit;
            }
		}
	} else {
		header("location: ./?msg=" . ERR_INVALID_PARAMS);
		exit;
	}

	// Make insert/delete query depending on specified action
	if (isset($query["delete"])) {
		$sql = "DELETE FROM connection WHERE (connection.userid1 = " . $userid .  " AND connection.userid2 = " . $connectedid . ") OR (connection.userid1 = " . $connectedid . " AND connection.userid2 = " . $userid . ");";
		if (!mysqli_query($conn, $sql)) {
			// echo "An error occurred while removing connection";
            header("location: ./?msg=" . ERR_UNEXPECTED);
            exit;
		} else {
		    if (isset($query["referrer"])) {
		        if (strpos($query["referrer"], "?") != -1) {
		            header("location: {$query["referrer"]}&msg=" . SUC_DELETE_CONNECTION);
		            exit;
                } else {
                    header("location: {$query["referrer"]}?msg=" . SUC_DELETE_CONNECTION);
                    exit;
                }
            } else {
                header("location: ./?msg=" . SUC_DELETE_CONNECTION);
                exit;
            }
		}
	} else {
		if ($query["id"] == $_SESSION["resourceid"]) {
			//echo "Cannot add yourself as connection";
			header("location: ./?msg=" . ERR_CONNECTION_TO_SELF);
			exit;
		} else {
			$sql = "INSERT INTO connection (userid1, userid2) VALUES (" . $userid . ", " . $connectedid . ");";
			if (!mysqli_query($conn, $sql)) {
				//echo "An error occurred while making connection";
				header("location: ./?msg=" . ERR_NOT_SIGNED_IN);
				exit;
			} else {
				//echo "Connection successfully made";
                if (isset($query["referrer"])) {
                    if (strpos($query["referrer"], "?") != -1) {
                        header("location: {$query["referrer"]}&msg=" . SUC_CREATE_CONNECTION);
                        exit;
                    } else {
                        header("location: {$query["referrer"]}?msg=" . SUC_CREATE_CONNECTION);
                        exit;
                    }
                    exit;
                } else {
                    header("location: ./?msg=" . SUC_CREATE_CONNECTION);
                    exit;
                }
			}
		}
	}
