<?php

require_once 'gmail/autoload.php';

include("../config.php");
include("config.php");

global $GMAIL;


define('APPLICATION_NAME', 'Gmail API Quickstart');
define('CLIENT_SECRET_PATH', 'client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Gmail::GMAIL_READONLY)
));

$client = new Google_Client();
$client->setApplicationName(APPLICATION_NAME);
$client->setScopes(SCOPES);
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType('offline');
$client->setRedirectUri($GMAIL->callback);
$client->setApprovalPrompt('force');
$client->authenticate($_GET['code']);
$accessToken = $client->getAccessToken();
insertUserToken($accessToken);

function insertUserToken($token){

	global $GMAIL;

	$userid = getMoodleIdFromSession($_COOKIE['MoodleSession']);
	mysql_connect($GMAIL->dbhost, $GMAIL->dbuser, $GMAIL->dbpassword);
        mysql_select_db($GMAIL->dbname);
	$token = addSlashes($token);
        $query = 'insert into credentials values("","'.$userid.'","'.$token.'");';
	$result = mysql_query($query);
        $row = mysql_fetch_row($result);
        mysql_close();
	header('Location: '.$GMAIL->successRedirect);
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

