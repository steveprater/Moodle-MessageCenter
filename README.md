# mdl_doSurvey
Script that allows moodle to check with limesurvey to see if a student has completed a begining of the semester survey and politly "nags" the student if it is not complete.

Uses Ajax requests and various javascript and html addons. 

Installation Instructions:

1. Add the following to Administration->Appearance->Additional HTML->Within HEAD:

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

2. Add the Following to Administration->Appearance->Additional HTML->When BODY is opened:

	<script>
	// update this to reflect your limesurvey setup
	//

 	var surveyurl = 'https://surveys.yoursite.com/index.php/survey/index/sid/';
	var sid = '';
	var questiongroup = '';
 	var question = '';

	$(document).ready(function(){
		var sessionid = getCookie("MoodleSession");
		$.get('/moodle/wosc/dosurvey.php', { session: sessionid} )
			.done(function(data) {
				if (data != "0"){
				// Might have to fix the next line a bit. Didnt test it.
					$("#surveydestination").attr("href",surveyurl+sid+'/newtest/Y/lang/en/'+sid+'X'+questiongroup+'X'+question+'/' + data + '/');
					$("#notice").css("display", "block");
				}
				})
	});

	function getCookie(cname) {
    		var name = cname + "=";
    		var ca = document.cookie.split(';');
    		for(var i=0; i<ca.length; i++) {
        		var c = ca[i];
        		while (c.charAt(0)==' ') c = c.substring(1);
        			if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    			}
    		return "";
	}

	</script>

	<div id="notice" class="alert alert-danger" role="alert" style="display:none;">
	<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  		<span class="sr-only">Notice!</span>
  		<strong>Help Western Improve! </strong><br> 
		<a href="#" onClick="goThere();" id="surveydestination">Please complete your beginning of the year survey.</a>
	</div>

3. Install the dosurvey.php and the config.php in the moodle directory in a "wosc" directory. ex. /var/www/html/moodle/wosc/

4. update the config.php with the proper information.

