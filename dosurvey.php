<?php

/****************************************
// Takes the moodle session as a GET argument "session"
// and checks to see if the owner of the session has taken the 
// survey specified below. 
//	Returns: Student Number if the survey is untaken
//		 0 if the studnet has already taken the survey
//		 or is not a student in a current course.
//****************************************


/*****************************************/
//                                       //
// Settings for the lime survey database //
// are stored in the config.php in the   // 
// current directory                     //
//                                       //
/*****************************************/


include("config.php");
include("../config.php");

// Clean the data, make sure it only contains possible session information

if (preg_match('/^[a-z0-9]+$/', $_GET["session"]))
{	
	$session = $_GET["session"];
}
else {
	$session = 0;
}

// ************************************************************
// Main Program Logic
// ************************************************************

$moodleId = getMoodleIdFromSession($session);
if(isStudent($moodleId)) {
	if(inCurrentCourse($moodleId) == 1) {
		$studentNumber = getStudentNumber($moodleId);
		$results = checkSurveyStatus($studentNumber);
	}
	else{
		$results = "0";
	}
}
else {
	$results = "0";
}

echo $results;

// **************************************************************
// Supporting Functions
// **************************************************************

function getStudentNumber($moodleId) {
// Gets the WOSC Student ID number from the Moodle Database.

	global $CFG;

        $moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);

        $query = 'select idnumber from mdl_user where id = '.$moodleId.';';
        //echo $query;
        $res = mysql_query($query);
        $row = mysql_fetch_row($res);
        $sid = $row[0];
        mysql_close();
	return $sid;
}

function inCurrentCourse($moodleId) {
//  Checks to see if the currently logged in student is in courses that are in the current term.
	global $CFG;
	
	$moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);
	
	$filter = getCurrentCourseFilter();

        $query = 'select 
			*
			from mdl_user_enrolments ue
			join mdl_enrol e on ue.enrolid = e.id
			join mdl_course c on c.id = e.courseid
			where c.shortname like "'.$filter.'"
			and ue.userid = '.$moodleId.';';
	//echo $query;
	$res = mysql_query($query);
        $num = mysql_num_rows($res);
        mysql_close();

	if($num > 0){
		return true;
	}
	else {
		return false;
	}
}

function getCurrentCourseFilter(){
// Returns the pattern to for the current courses
// allows us to ultimatly identify if the student
// is enrolled in a current course

	$month = date("n");
	if(($month >= 1) && ($month <= 5)){
		$year = date("y") - 1;
		$semester = "3S";
		// Its Spring!
	}
	if(($month >=6) && ($month <= 7)){
		$year = date('y');
		$semester = "1S";
	}
	if(($month >= 8) && ($month <= 12)){
		$year = date('y');
		$semester = '2S';
	}
	
	return '%-'.$year.$semester;
	
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

function checkSurveyStatus($stdid) {
// Checks to see if the current user has alreay completed the survey for the semester.
	global $LIME;	
	
	$lime = mysql_connect($LIME->dbhost, $LIME->dbuser, $LIME->dbpass);
	if (!$lime) {
		die('Could not connect: ' .mysql_error());
	}	

	$query = 'select * from lime_survey_'.$LIME->surveyid.
		' where (('.$LIME->surveyid.'X'.$LIME->surveygroup.
		'X'.$LIME->surveyquestion.' = "'. $stdid .
		'") and (submitdate is not null));';
	mysql_select_db($LIME->db, $lime);
	$res = mysql_query($query);
	$num = mysql_num_rows($res);
	mysql_close();
	if ($num == 0) {
		if ($stdid != ""){
			$result = $stdid;
		}
		else {
			$result = "0";
		}
	}
	else {
		$result = "0";
	}
	return $result;
}

?>
