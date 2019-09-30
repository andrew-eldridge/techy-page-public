<?php

    // Success messages
    define("SUC_CREATE_CONNECTION", "connection_created");
    define("SUC_DELETE_CONNECTION", "connection_deleted");
    define("SUC_LINK_LINKEDIN", "linkedin_linked");
    define("SUC_LINK_GITHUB", "github_linked");

    // Error messages
    define("ERR_INCOMPLETE_FEATURE", "incomplete_feature");
    define("ERR_UNEXPECTED", "unexpected_error");
    define("ERR_CONNECTION_TO_SELF", "connection_to_self");
    define("ERR_INVALID_PARAMS", "invalid_params");
    define("ERR_INVALID_USER", "invalid_user");
    define("ERR_NOT_SIGNED_IN", "not_signed_in");
    define("ERR_LINK_LINKEDIN", "linkedin_link_failed");
    define("ERR_LINK_GITHUB", "github_link_failed");

    // User class definition
    class User {
        public $name = null;
        public $resourceid = null;
        public $imageext = null;
        public $email = null;
        public $location = null;
        public $description = null;
        public $education = null;
        public $isEmployer = null;
        public $isFreelancer = null;
        public $languages = array();
        public $wagerate = null;
    }

    // Parse query for message banner
    function populateMessageBanner() {
        $url = $_SERVER["REQUEST_URI"];
        $parts = parse_url($url);
        $bannerHTML = "";
        if (isset($parts["query"])) {
            parse_str($parts["query"], $query);
            if (isset($query["msg"])) {
                switch ($query["msg"]) {
                    // Successes
                    case SUC_CREATE_CONNECTION:
                        $bannerHTML = "<div class='message-banner success-banner'>Connection successfully made.</div>";
                        break;
                    case SUC_DELETE_CONNECTION:
                        $bannerHTML = "<div class='message-banner success-banner'>Connection successfully deleted.</div>";
                        break;
                    case SUC_LINK_LINKEDIN:
                        $bannerHTML = "<div class='message-banner success-banner'>LinkedIn account successfully linked.</div>";
                        break;
                    case SUC_LINK_GITHUB:
                        $bannerHTML = "<div class='message-banner success-banner'>GitHub account successfully linked.</div>";
                        break;
                    // Errors
                    case ERR_INCOMPLETE_FEATURE:
                        $bannerHTML = "<div class='message-banner error-banner'>The feature is not currently available.</div>";
                        break;
                    case ERR_UNEXPECTED:
                        $bannerHTML = "<div class='message-banner error-banner'>An unexpected error occurred.</div>";
                        break;
                    case ERR_CONNECTION_TO_SELF:
                        $bannerHTML = "<div class='message-banner error-banner'>You cannot connect with yourself.</div>";
                        break;
                    case ERR_INVALID_PARAMS:
                        $bannerHTML = "<div class='message-banner error-banner'>Invalid parameters in request.</div>";
                        break;
                    case ERR_INVALID_USER:
                        $bannerHTML = "<div class='message-banner error-banner'>Invalid user in request.</div>";
                        break;
                    case ERR_NOT_SIGNED_IN:
                        $bannerHTML = "<div class='message-banner error-banner'>You must be signed in to perform that action.</div>";
                        break;
                    case ERR_LINK_LINKEDIN:
                        $bannerHTML = "<div class='message-banner error-banner'>Failed to link LinkedIn account.</div>";
                        break;
                    case ERR_LINK_GITHUB:
                        $bannerHTML = "<div class='message-banner error-banner'>Failed to link GitHub account.</div>";
                        break;
                    // Default
                    default:
                        break;
                }
            }
        }
        return $bannerHTML;
    }

    function encodeURI($uri) {
        $revert = array("%26"=>"&", "%2f"=>"/", "%3f"=>"?");
        return strtr(rawurlencode($uri), $revert);
    }

    function redirect($location, $msg) {
        if ($msg != "") {
            if (strpos($location, "?") != -1) {
                header("location: {$location}&msg=" . $msg);
            } else {
                header("location: {$location}?msg=" . $msg);
            }
        } else {
            header("location: {$location}");
        }
    }
