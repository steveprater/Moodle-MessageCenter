<?php

require 'gmail/autoload.php';
include("../config.php");
include("config.php");


define('APPLICATION_NAME', 'Gmail API Quickstart');
define('CLIENT_SECRET_PATH', 'client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Gmail::GMAIL_READONLY)
));


if(isset($_GET['action'])) { $action = $_GET['action']; }
else { $action = "none"; }


if(isset($_COOKIE['MoodleSession'])) {
	$userid = getMoodleIdFromSession($_COOKIE['MoodleSession']);
	//echo "User ID = $userid ";
	if(isStudent($userid)){
		$userToken = getUserToken($userid);
		if(isset($userToken)){
			printUnreadMessages($userToken);
		}
		else
		{
			if($action == 'getToken'){
				getNewToken();
			}
			else{
				echo "0";
			}
		}
	}
	else {
		echo "null";
	}
}

function printUnreadMessages($userToken){

	global $GMAIL;

	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
        $client->setAccessType('offline');
        $client->setRedirectUri($GMAIL->callback);
        $client->setHostedDomain($GMAIL->domain);
	$client->setApprovalPrompt('force');
        $client->setAccessToken($userToken);
        $service = new Google_Service_Gmail($client);
        $results = $service->users_labels->get('me','INBOX');
	if($results['threadsUnread'] > 0){
		echo '	<div id="unreadmessages-badge" class="badge">'
			.$results['threadsUnread'].'</div>';
		echo '	<script>$(".avatar").addClass(\'unreadmessages\'); $(".unreadmessages").attr(\'data-content\','.$results['threadsUnread'].');                       $("#actionmenuaction-6").addClass(\'unreadmessages\'); $(".unreadmessages").attr(\'data-content\','.$results['threadsUnread'].');</script>';
	}
	else {
		echo "null";
	}
}

function getNewToken(){

	$client = new Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(CLIENT_SECRET_PATH);
	$client->setHostedDomain('email.wosc.edu');
        $client->setAccessType('offline');
	$client->setApprovalPrompt('force');
        $client->setRedirectUri($GMAIL->callback);

	$auth_url = $client->createAuthUrl();
	header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

}

/*
function getUnreadMessages() {
	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
	$client->setScopes(SCOPES);
	$client->setAuthConfigFile(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');
	$client->setRedirectUri('http://stevedev.wosc.edu/moodle/wosc/oauth2callback.php');
}

*/

function getUserToken($userid){
	global $GMAIL;

	mysql_connect($GMAIL->dbhost, $GMAIL->dbuser, $GMAIL->dbpassword);
	mysql_select_db($GMAIL->dbname);
	$query = 'select credentials.token from credentials where userid = "'.$userid.'";';
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	mysql_close();
	return $row[0];
}

function getMoodleIdFromSession($session) {
// Gets the current Moodle Id number from the session information
        global $CFG;

        $moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);
        $query = 'select mdl_user.id from mdl_user join mdl_sessions
                        on mdl_sessions.userid = mdl_user.id where
                        mdl_sessions.sid = "'.$session.'";';
        $res = mysql_query($query);
        $row = mysql_fetch_row($res);
        $moodleId = $row[0];
        mysql_close();
        return $moodleId;
}

function isStudent($moodleId) {
// Checks to see if the user is a student
// identified by email address
        global $CFG;

        $moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);

        $query = 'select email from mdl_user where id = '.$moodleId.';';
        $res = mysql_query($query);
        $row = mysql_fetch_row($res);
        $email = $row[0];
        mysql_close();
        if(preg_match("/email.wosc.edu/", $email)){
                return true;
        }
        else {
                return false;
        }
}

?>
