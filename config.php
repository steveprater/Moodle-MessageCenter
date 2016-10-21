<?php

unset($LIME);
unset($GMAIL);
$LIME = new stdClass();
$GMAIL = new stdClass();

# info fo lime survey database acess
$LIME->db = "";
$LIME->dbuser = "";
$LIME->dbpass = "";
$LIME->dbhost = "";

# Set to turn surevy on or off.
$LIME->surveystatus = 1;
$LIME->nurse_surveystatus = 1;

# info for the required survey information
$LIME->surveyid = "";
$LIME->surveygroup = "";
$LIME->surveyquestion = "";

# Info for the Nursing Demographic Survey
# Students are choosen based on the MSN field in mdl_user. If field contains nurse_surveyid:shortname
$LIME->nurse_surveyid = "";
$LIME->nurse_surveygroup = "";
$LIME->nurse_surveyquestion = "";

# info for the gmail credentials database
$GMAIL->dbhost = '';
$GMAIL->dbname = '';
$GMAIL->dbuser = '';
$GMAIL->dbpassword = '';

# info for the gmail api

$GMAIL->callback='';
$GMAIL->successRedirect='';
$GMAIL->domain='';
$GMAIL->appName = '';
$GMAIL->clientSecretPath = '';
