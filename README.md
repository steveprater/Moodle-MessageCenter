# Moodle-MessageCenter
Script that allows moodle to check with limesurvey to see if a student has completed a begining of the semester survey and politly "nags" the student if it is not complete.
Also has the abilty to check with a google apps for domains account to see how many unread messages are in a partuicular users inbox and displays it as thier profile badge.

Uses Ajax requests and various javascript and html addons. 

Installation Instructions:

1. Add the following to Administration->Appearance->Additional HTML->Within HEAD:

	<script src="http://your.moodle.com/installdir/include/jquery.min.js"></script>
	<script src="http://your.moodle.com/installdir/include/messagecenter.js"></script>
	<link rel="stylesheet" type="text/css" href="http://your.moodle.com/installdir/include/woscbell.css" />

3. Install the dosurvey.php and the config.php in the moodle directory in a "wosc" directory. ex. /var/www/html/moodle/wosc/

4. update the config.php with the proper information.

5. Create a database for the gmail tokens and import the database script with the following command:

	#mysql -u username -p < gmail.sql 

6. Install your "client_secret.json" file in the installdir
	a. Go to http://console.developers.google.com and create a project.
	b. In that project go to APIs and auth->Credentials
	c. Create a new Client ID for a web application
	d. Download JSON for that application
	e. Copy/Paste that file's contents into 'installdir/client_secret.json'




