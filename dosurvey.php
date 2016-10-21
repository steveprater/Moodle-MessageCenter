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

global $LIME;
$jsonString = new StdClass();
$moodleId = getMoodleIdFromSession($session);
$jsonString->studentSurvey = 0;
$jsonString->nursingSurvey = 0;

$jsonString->studentNumber = str_replace('-','',getStudentNumber($moodleId));

if(isStudent($moodleId)) {
//	$jsonString->debug = $jsonString->debug . " & " . "is student";
	if((inCurrentCourse($moodleId) == 1) && ($LIME->surveystatus == 1)) {
		if(checkSurveyStatus($jsonString->studentNumber)) {
			$tc = getCurrentCourseFilter();
                        $tc = substr($tc,2);
			$jsonString->studentSurvey = $tc;
		}
	}
}

if((inNursingCourse($moodleId)) && ($LIME->nurse_surveystatus == 1)) {
#                $jsonString->debug = $jsonString->debug . " & " . " is In NursinCourse";
		//echo hasTakenNursingSurvey($jsonString->studentNumber);
                if (hasTakenNursingSurvey($jsonString->studentNumber) == false) {
                        $jsonString->nursingSurvey = getNursingCourse($moodleId);
                }
}


# More info needed now. Have to change this to a json reply
$json = json_encode($jsonString);
echo $json;

// **************************************************************
// Supporting Functions
// **************************************************************

function inNursingCourse($moodleId) {
//  Checks to see if the currently logged in student has information in thier MSN field in mdl_user.
        global $CFG;
	global $LIME;

        $moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);

        $query = 'select
                        *
                        from mdl_user
                        where msn like "'.$LIME->nurse_surveyid.'%"
                        and id = '.$moodleId.';';
#        echo $query;
        $res = mysql_query($query);
        $num = mysql_num_rows($res);
#	echo $num;
        mysql_close();

        if($num > 0){
		return true;
        }
        else {
                return false;
        }

}


function getNursingCourse($moodleId) {
//  Checks to see if the currently logged in student has information in thier MSN field in mdl_user.
        global $CFG;
        global $LIME;

        $moodledb = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
        if (!$moodledb) {
                die('Could not connect to moodle: ' .mysql_error());
        }
        mysql_select_db($CFG->dbname, $moodledb);

        $query = 'select
                        *
                        from mdl_user
                        where msn like "'.$LIME->nurse_surveyid.'%"
                        and id = '.$moodleId.';';
        //echo $query;
        $res = mysql_query($query);
        $num = mysql_num_rows($res);
        mysql_close();

        if($num > 0){
                $row = mysql_fetch_row($res);
                $pieces = explode(':',$row[18]);
                return $pieces[1];
        }
        else {
                return false;
        }

}



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
	$day = date("j");
	if(($month >= 1) && ($month < 5)){
		$year = date("y") - 1;
		$semester = "3S";
		// Its Spring!
	}
	if(($month > 5) && ($month < 8)){
		$year = date('y');
		$semester = "1S";
	}
	if(($month == 5) && ($day >= 20)){
		$year = date('y');
                $semester = "1S";
	}
	if(($month >= 8) && ($month < 12)){
		$year = date('y');
		$semester = '2S';
	}
	//echo '%-'.$year.$semester;	
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
			$tc = getCurrentCourseFilter();
			$tc = substr($tc,2);
			$json = '{ "studentid":"' . $stdid . '","termcode":"'. $tc  .'"}';
			$result = $json;
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

function hasTakenNursingSurvey($stdid) {
// Checks to see if the current user has alreay completed the survey for the semester.
        global $LIME;

        $lime = mysql_connect($LIME->dbhost, $LIME->dbuser, $LIME->dbpass);
        if (!$lime) {
                die('Could not connect: ' .mysql_error());
        }

        $query = 'select * from lime_survey_'.$LIME->nurse_surveyid.
                ' where (('.$LIME->nurse_surveyid.'X'.$LIME->nurse_surveygroup.
                'X'.$LIME->nurse_surveyquestion.' = "'. $stdid .
                '") and (submitdate is not null));';
	//echo $query;
        mysql_select_db($LIME->db, $lime);
        $res = mysql_query($query);
        $num = mysql_num_rows($res);
        mysql_close();
        if ($num == 0) {
                if ($stdid != ""){
                        $result = false;
                }
                else {
                        $result = true;
                }
        }
        else {
                $result = true;
        }
        return $result;
}



