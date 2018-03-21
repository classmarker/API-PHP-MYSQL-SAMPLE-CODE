<?php
/**
 * Script to use classMarker_api.class.php to retrieve results
 * This script can be used in PHP Strict mode
 *
 * MySql examples taken from php.net
 * https://www.php.net/manual/en/mysql.examples-basic.php
 *
 * @author ClasssMarker Pty Ltd
 * @see https://www.classmarker.com/online-testing/api/developers
 * @version 1
 * @copyright © 2012 ClasssMarker Pty Ltd
 * @license Copyright holder allows use of the code for any purpose
 *
 * Disclaimer:  ClassMarker Pty Ltd accepts no responsibility whatsoever from usage of these API scripts and Classes
 *  			and shall be held harmless from any and all litigation, liability, and responsibilities.
 */



/*******************************************************
 *
 *
 * 				How this script works
 *
 * In a nutshell:- Connect to ClassMaker API, retrieve results, insert results into your tables.
 *
 * This script is written as a guide to get you started integrating results into your system.
 * It is written to be run as a hourly cron job and does not take any $_REQUEST parameters. It can be modified of course
 * This guide presumes you intend to use the database tables supplied with this script.
 * The database tables supplied with this script are to store all test results from "groups" (if used) in one table
 * 	and all test results from "links" (if used) in a separate table,
 * 	as well as tests/groups/links information in separate tables
 *
 *
 * [First] We define if you want to retrieve results from tests taken under groups or links (see "$request_results_type" variable below)
 * Why: Results taken by Registered users (Groups) are kept separate from Results taken by non registered users (Links)
 *
 * Registered users (Group results) log into ClassMarker to take tests and you can track(group) their individual results by:
 * 	1. user_id: Supplied by ClassMarker
 *
 * Non registered users (Link results) add their details before starting a test. You can track their results by either:
 * 	1. Email address: (as long as they use the same email address)
 *  2. cm_user_id: (pass the test takers user_id from your system when the test is started) ClassMarker send this back with test results
 *  3. access_code: A ClassMarker administrator can create a list of unique identifiers to allow users access to tests (like a password list)
 *  The above 3 options are optional and just an informational guide, each returned test result will include users Name and Email as long as the test is set to require this by the ClassMarker administrator.
 *
 *
 *  [Second] We select to either:
 *
 *  1. Retrieve a list of groups and tests available to your API Key
 *     See: https://www.classmarker.com/online-testing/api/developers/#getgroups
 *
 *  2. Retrieve recent results from all tests taken within "Groups" or "Direct Links" as specified below in "$response_type" variable
 *     See: https://www.classmarker.com/online-testing/api/developers/ for more details
 *
 *  3. Retrieve recent results from single test taken within a single Groups or Links as specified above in "$response_type" variable
 *     See: https://www.classmarker.com/online-testing/api/developers/ for more details
 *
 *
 *
 *  [Third] We create arrays from results to insert data into database tables
 *
 *
 *  [Fourth] Insert results into database tables. See create_classmarker_tables.txt to create applicable tables
 *
 *
 *
 */





















include ('classmarker_api.class.php');

include ('credentials.php');//Get API KEY / DATABASE credentials etc






/***********************************************************************************
 * Set Request parameters
 ***********************************************************************************/

/*
 * $response_format.
 * Values:
 * 		1. json
 * 		2. xml
 */
$response_format =	'json';  // Values: 'json' or 'xml'. The format results are returned in. We have given examples for handline json and xml responses below


/*
 * $request_type.
 * Values:
 * 		1. available_groups 			- Display groups and tests that are available to this API key for retrieving results
 * 		2. get_all_recent_results 		- Recommended: Retrieve all recent results available (See: $finished_after_timestamp)
 * 		3. get_specific_recent_results 	- Retrieve recent results from single test taken within a single Groups or Links
 */
$request_type = 	'get_all_recent_results';


/*
 * $request_results_type.
 * Values:
 * 		1. groups 	- Retrieve results taken from registered members within ClassMarker
 * 		2. links 	- Retrieve results taken from non-registered users via Direct links
 *
 * See: https://www.classmarker.com/online-testing/api/developers/#explain
 */
$request_results_type = 	'links';


/*
 * $request_results_type.
 * Values:
 * 		1. A valid timestamp within the last 2 weeks
 * 		2. Do not set - ClassMarker API will default to retrieve results from last two weeks
 *
 * 		We don't want to download results we already have.
 * 		So lets get the highest timestamp from results we have saved in our results tables
 * 		and ClassMarker will return only results taken after this timestamp.
 */
if ($request_results_type == 'groups' || $request_results_type == 'links'){


	$finished_after_timestamp = getHighestTimeStamp($request_results_type);


}



/*
 * Debugging
 *  Values:
 * 		1. true 	- set to true to echo raw response then EXIT script (No Inserting of results into database will happen)
 * 		2. false 	- use for production use to insert results in your tables.
 */
$display_raw_reponse = false;


/*
 * Debugging
 * Do you want to insert results to database or view in HTML for debugging
 *  Values:
 *  	1. insert_to_database
 *  	2. view_as_html
 */
$action = 'insert_to_database';






/************************************************************************************
 * Create object
 ************************************************************************************/
$classmarkerApiObj = new classmarker_api($api_key, $api_secret, $response_format);



/**********************************************************************************
 *
 * 					MAKING A REQUEST
 *
 * Select from one of the three options below depending on the
 * results you wish to retrieve
 *
 *
 *********************************************************************************/


if ($request_type == 'available_groups'){

	/*
	 * Option 1: Request available groups/links/tests
	 *
	 * Retrieve a list of available groups/links with assigned tests that are available to this API Key
	 * Mainly for debugging to see your API key has access to group or link results
	 *
	 */


	$classmarkerApiObj->getAvailableGroups();




} else if ($request_type == 'get_all_recent_results'){

	/*
	 *
	 * Option 2: Request all recent	 |	RECOMMENDED FOR RETRIEVING RESULTS - See comments below
	 *
	 *
	 * Retrieve recent results from all tests taken within Groups or Via Direct Links as specified above in "$response_type" variable
	 *
	 * We recommend using this request rather then the request below as this will retrieve all test results available
	 * to your API key rather having to make separate requests per test.
	 *
	 * If inserting results into database (see below) you don't need to make any changes using supplied tables in
	 */


	$classmarkerApiObj->getRecentResults($request_results_type, $finished_after_timestamp);



} else if ($request_type == 'get_specific_recent_results'){

	/*
	 * Option 3: Request single tests results
	 *
	 * Retrieve recent results from single test taken within a single Groups or Links as specified above in "$response_type" variable
	 *
	 * Use this request if you only want to download results for a single test assigned to a group or link
	 */



	$group_or_link_id = 1; //your available group or link ids can be found from the $classmarkerApiObj->getAvailableGroups() request and setting results to ht;
	$test_id = 1; //your available test ids can be found from the $classmarkerApiObj->getAvailableGroups() request;

	$classmarkerApiObj->getSingleTestResults($request_results_type, $group_or_link_id, $test_id, $finished_after_timestamp);


} else {

	echo 'No data. Did you select a valid $request_type.';
	exit;

}






/*
 * Check for error made before making request to ClassMarker
 */
if ($classmarkerApiObj->doesPreRequestErrorExists()){

	echo $classmarkerApiObj->getError();
	exit;

} else {

	$response = $classmarkerApiObj->getResponse();

	if ($response == ''){
		echo 'No data. Did you select a valid $request_type.';
		exit;
	}
}


/*
 * Debugging
 * View raw results
 *
 */
if ($display_raw_reponse){

	echo $response;
	exit;

}







if ($classmarkerApiObj->request_type == 'get_available_tests'){
	/* Simply used to display what groups are avaiable to your API - A simple debugging exercise.
	 *
	 * Triggered when calling: Option 1: $classmarkerApiObj->getAvailableGroups(); above
	 * */
	echo $response;
	exit;
}










/***********************************************************************************
 *
 * Lets deal with results response from ClassMarker
 *
 ***********************************************************************************/

if ($response_format=='xml'){


	//XML RESPONSE

	$results = new SimpleXMLElement($response);


	if ( $results->status == 'error' ){

		/* Printing error message and exit - handle as you will
		 *
		 * See error codes:
		 * https://www.classmarker.com/online-testing/api/developers#errorcodes
		 *
		 * */

		echo 'Status: '.			$results->status. '<br/>';
		echo 'Request path: '.		$results->request_path. '<br/>';
		echo 'Server timestamp: '.	$results->server_timestamp. '<br/>';
		echo 'Error code: '. 		$results->error->error_code. '<br/>';
		echo 'Error code: '.		$results->error->error_message;
		exit;

	} else if ( $results->status == 'no_results' ){

		echo 'Status: '.			$results->status. '<br/>';
		echo 'Request path: '.		$results->request_path. '<br/>';
		echo 'Server timestamp: '.	$results->server_timestamp. '<br/>';
		echo 'The finishedAfterTimestamp sent in request: ' . $results->finished_after_timestamp_used;
		exit;

	} else if ( $results->status == 'request_limit_reached' ){

		echo 'Status: '.			$results->status. '<br/>';
		echo 'Request path: '.		$results->request_path. '<br/>';
		echo 'Server timestamp: '.	$results->server_timestamp. '<br/>';
		exit;

	}

	if ($request_results_type == 'groups'){

		foreach ($results->groups->group as $group){


			$groups_array[ (int)$group->group_id ] = (string)$group->group_name;


			/* To echo each group id ID and NAME for example */
			//echo '<p>' . $group->group_id .': '.$group->group_name .'</p>';


		}

	} else if ($request_results_type == 'links'){

		foreach ($results->links->link as $link){


			$links_array[ (int)$link->link_id ] = (string)$link->link_name;


			/* To echo each link id ID and NAME for example */
			//echo '<p>' . $link->link_id .': '.$link->link_name .'</p>';


		}

	}

	foreach ($results->tests->test as $test){


		$tests_array[ (int)$test->test_id ] = (string)$test->test_name;


		/* To echo each test id ID and NAME for example */
		//echo '<p>' . $test->test_id .': '.$test->test_name .'</p>';


	}

	foreach ($results->results->result as $result){


		$results_array[] = $result;


		/* To echo each users result for example */
		//echo '<p>' . $result->first . ' ' . $result->last . ': ' . $result->percentage . '%</p>';


	}


} else {


	//JSON RESPONSE


	$results = json_decode($response, true);



	if ( $results['status'] == 'error' ){

		/* Printing error message and exit - handle as you will
		 *
		 * See error codes:
		 * https://www.classmarker.com/online-testing/api/developers#errorcodes
		 *
		 * */


		echo 'Status: '.			$results['status']. '<br/>';
		echo 'Request path: '.		$results['request_path']. '<br/>';
		echo 'Server timestamp: '.	$results['server_timestamp']. '<br/>';
		echo 'Error code: '. 		$results['error']['error_code']. '<br/>';
		echo 'Error code: '.		$results['error']['error_message'];
		exit;

	} else if ( $results['status'] == 'no_results' ){

		echo 'Status: '.			$results['status']. '<br/>';
		echo 'Request path: '.		$results['request_path']. '<br/>';
		echo 'Server timestamp: '.	$results['server_timestamp']. '<br/>';
		echo 'The finishedAfterTimestamp sent in request: ' . $results['finished_after_timestamp_used'];
		exit;

	} else if ( $results['status'] == 'request_limit_reached' ){

		echo 'Status: '.			$results['status']. '<br/>';
		echo 'Request path: '.		$results['request_path']. '<br/>';
		echo 'Server timestamp: '.	$results['server_timestamp']. '<br/>';
		exit;

	}





	/* Add groups or links names and ids to array */
	if ($request_results_type == 'groups'){

		foreach ($results['groups'] as $element){


			$groups_array[ $element['group']['group_id'] ] = $element['group']['group_name'];


			/* To echo each group id ID and NAME for example */
			//echo '<p>'. $element['group']['group_id'].': '.$element['group']['group_name'] . '</p>';

		}

	} else if ($request_results_type == 'links'){

		foreach ($results['links'] as $element){


			$links_array[ $element['link']['link_id'] ] = $element['link']['link_name'];


			/* To echo each link id ID and NAME for example */
			//echo '<p>'. $element['link']['link_id'].': '.$element['link']['link_name'] . '</p>';

		}

	}





	/* Add tests names and ids to array */
	foreach ($results['tests'] as $element){


		$tests_array[ $element['test']['test_id'] ] = $element['test']['test_name'];


		/* To echo each test id ID and NAME for example */
		//echo '<p>'. $element['test']['test_id'].': '.$element['test']['test_name'] . '</p>';

	}




	/* Add results to array */
	foreach ($results['results'] as $element){


		$results_array[] = $element['result'];


		/* To echo each users result for example */
		//echo '<p>' . $element['result']['first'] . ' ' . $element['result']['last'] . ': ' . $element['result']['percentage'] . '%</p>';

	}



}









/**********************************************************************************
 *
 * 					IF VIEWING RESULTS IN HTML FOR DEBUGGING
 *
 * Results are now in arrays below
 *
 *********************************************************************************/

if ($action == 'view_as_html'){


	if ($request_results_type == 'groups'){

		echo '<strong>Groups</strong><br/>';
		echo '<pre>';print_r($groups_array);echo '</pre>';

	} else if ($request_results_type == 'links'){

		echo '<strong>Links</strong><br/>';
		echo '<pre>';print_r($links_array);echo '</pre>';

	}


	echo '<strong>Tests</strong><br/>';
	echo '<pre>';print_r($tests_array);echo '</pre>';

	echo '<strong>Results</strong><br/>';
	echo '<pre>';print_r($results_array);echo '</pre>';


	exit;
}






/**********************************************************************************
 *
 * 					IF INSERTING RESULTS INTO DATABASE
 *
 * You have 5 tables.
 * classmarker_tests:  			for tests information (test name and ID)
 * classmarker_groups: 			for group information (group name and ID)
 * classmarker_links:  			for link information (link name and ID)
 * classmarker_group_results: 	for group results (holds test results taken within Groups)
 * classmarker_link_results:  	for link results (holds test results taken from Direct links)
 *
 * 1. First see if we are inserting link results or group results
 *
 * 2. Each recent results script returns the test and (group or link) information involved with the results.
 *    For example: If group 'Math 101' Took Math test 'Algebra' we are retuned:
 *      i. the Math 1010 group name and ID (same if it was a link name and ID)
 *     ii. the Algebra test name and ID
 *    iii. the results taken since finishedAfterTimestamp parameter you sent in request.
 *
 *    So we do "insert for updates" on tests/links/groups this way if new tests/links/groups exist
 * 	  we can add them in automatically or update them if the test or group name was changed by a ClassMArker administrator for example.
 *    Note the Test/Link/Group ids will not change and are used for relational reference keys.
 *
 *  3. Loop results to add data to tables.
 *
 *
 *
 *********************************************************************************/





if ($action == 'insert_to_database'){


	/* Insert tests data */
	foreach ($tests_array as $id=>$test_name){

		/*
		 * Query will add new rows or update if rows exists already
		 * This allows a ClassMarker admin to rename a group and you will see changes on your system
		 *
		 * Note: A test can be used and contain results under both links and groups - but will use it's same ID across both
		 *
		 */
		$query = 'INSERT INTO classmarker_tests set '
		. ' test_id = ' . mysqli_real_escape_string($mysqli, $id)
		. ', test_name = "' . mysqli_real_escape_string($mysqli, $test_name) . '"'
		. ' ON DUPLICATE KEY UPDATE test_name = "' . mysqli_real_escape_string($mysqli, $test_name) . '"';


		if (!$result = $mysqli->query($query)) {
			echo 'Query failed: ' . mysqli_error(mysqli);
			exit;
		}


	}


	if ($request_results_type == 'groups'){


		/* Insert groups data */

		foreach ($groups_array as $id=>$group_name){

			/*
			 * Query will add new rows or update if row exists already
			 * This allows a ClassMarker admin to rename a group and you will see changes on your system
			 */
			$query = 'INSERT INTO classmarker_groups set '
			. ' group_id = ' . mysqli_real_escape_string($mysqli, $id)
			. ', group_name = "' . mysqli_real_escape_string($mysqli, $group_name) . '"'
			. ' ON DUPLICATE KEY UPDATE group_name = "' . mysqli_real_escape_string($mysqli, $group_name) . '"';

			if (!$result = $mysqli->query($query)) {
				echo 'Query failed: ' . mysqli_error(mysqli);
				exit;
			}

		}


		/* Insert groups results */

		foreach ($results_array as $result){


			/*
			 * Query will add new rows or update if rows exists already
			 * This allows a ClassMarker admin to edit points in a test and
			 * the results can be downloaded again by settings your finishedAfterTimestamp variable
			 * in your request to ClassMarker back to a time before the test was taken.
			 *
			 */
			$query = 'INSERT INTO classmarker_group_results set '
			. ' user_id = ' . 				mysqli_real_escape_string($mysqli, $result['user_id'])
			. ', test_id = ' . 				mysqli_real_escape_string($mysqli, $result['test_id'])
			. ', group_id = ' . 			mysqli_real_escape_string($mysqli, $result['group_id'])
			. ', first = "' . 				mysqli_real_escape_string($mysqli, $result['first']) . '"'
			. ', last = "' . 				mysqli_real_escape_string($mysqli, $result['last']) . '"'
			. ', email = "' . 				mysqli_real_escape_string($mysqli, $result['email']) . '"'
			. ', percentage = ' . 			mysqli_real_escape_string($mysqli, $result['percentage'])
			. ', points_scored = ' . 		mysqli_real_escape_string($mysqli, $result['points_scored'])
			. ', points_available = ' . 	mysqli_real_escape_string($mysqli, $result['points_available'])
			. ', time_started = ' . 		mysqli_real_escape_string($mysqli, $result['time_started'])
			. ', time_finished = ' . 		mysqli_real_escape_string($mysqli, $result['time_finished'])
			. ', duration = "' . 			mysqli_real_escape_string($mysqli, $result['duration']) . '"'
			. ', status = "' . 				mysqli_real_escape_string($mysqli, $result['status']) . '"'
			. ', requires_grading = "' . 	mysqli_real_escape_string($mysqli, $result['requires_grading']) . '"'

			. '  ON DUPLICATE KEY UPDATE percentage = ' . mysqli_real_escape_string($mysqli, $result['percentage'])
			. ', points_scored = ' . 		mysqli_real_escape_string($mysqli, $result['points_scored'])
			. ', points_available = ' . 	mysqli_real_escape_string($mysqli, $result['points_available']);


			if (!$result = $mysqli->query($query)) {
				echo 'Query failed: ' . mysqli_error(mysqli);
				exit;
			}




		}

		echo 'Successful request';








	} else if ($request_results_type == 'links'){

		/* Insert links data */

		foreach ($links_array as $id=>$link_name){

			/*
			 * Query will add new rows or update if rows exists already
			 * This allows a ClassMarker admin to rename a link and you will see changes on your system
			 */
			$query = 'INSERT INTO classmarker_links set '
			. ' link_id = ' . mysqli_real_escape_string($mysqli, $id)
			. ', link_name = "' . mysqli_real_escape_string($mysqli, $link_name) . '"'
			. ' ON DUPLICATE KEY UPDATE link_name = "' . mysqli_real_escape_string($mysqli, $link_name) . '"';

			if (!$result = $mysqli->query($query)) {
				echo 'Query failed: ' . mysqli_error(mysqli);
				exit;
			}

		}



		/* Insert groups results */

		foreach ($results_array as $result){


			/*
			 * Query will add new rows or update if rows exists already
			 * This allows a ClassMarker admin to edit points in a test and
			 * the results can be downloaded again by settings your finishedAfterTimestamp variable
			 * in your request to ClassMarker back to a time before the test was taken.
			 *
			 */
			$query = 'INSERT INTO classmarker_link_results set '
			. ' link_result_id = ' . 		mysqli_real_escape_string($mysqli, $result['link_result_id'])
			. ', test_id = ' . 				mysqli_real_escape_string($mysqli, $result['test_id'])
			. ', link_id = ' . 				mysqli_real_escape_string($mysqli, $result['link_id'])
			. ', first = "' . 				mysqli_real_escape_string($mysqli, $result['first']) . '"'
			. ', last = "' . 				mysqli_real_escape_string($mysqli, $result['last']) . '"'
			. ', email = "' . 				mysqli_real_escape_string($mysqli, $result['email']) . '"'
			. ', percentage = ' . 			mysqli_real_escape_string($mysqli, $result['percentage'])
			. ', points_scored = ' . 		mysqli_real_escape_string($mysqli, $result['points_scored'])
			. ', points_available = ' . 	mysqli_real_escape_string($mysqli, $result['points_available'])
			. ', time_started = ' . 		mysqli_real_escape_string($mysqli, $result['time_started'])
			. ', time_finished = ' . 		mysqli_real_escape_string($mysqli, $result['time_finished'])
			. ', duration = "' . 			mysqli_real_escape_string($mysqli, $result['duration']) . '"'
			. ', status = "' . 				mysqli_real_escape_string($mysqli, $result['status']) . '"'
			. ', requires_grading = "' . 	mysqli_real_escape_string($mysqli, $result['requires_grading']) . '"'
			. ', cm_user_id = "' . 			mysqli_real_escape_string($mysqli, $result['cm_user_id']) . '"'
			. ', access_code = "' . 		mysqli_real_escape_string($mysqli, $result['access_code']) . '"'
			. ', extra_info = "' . 			mysqli_real_escape_string($mysqli, $result['extra_info']) . '"'
			. ', extra_info2 = "' . 		mysqli_real_escape_string($mysqli, $result['extra_info2']) . '"'
			. ', extra_info3 = "' . 		mysqli_real_escape_string($mysqli, $result['extra_info3']) . '"'
			. ', extra_info4 = "' . 		mysqli_real_escape_string($mysqli, $result['extra_info4']) . '"'
			. ', extra_info5 = "' . 		mysqli_real_escape_string($mysqli, $result['extra_info5']) . '"'
			. ', ip_address = "' . 			mysqli_real_escape_string($mysqli, $result['ip_address']) . '"'

			. '  ON DUPLICATE KEY UPDATE percentage = ' . mysqli_real_escape_string($mysqli, $result['percentage'])
			. ', points_scored = ' . 		mysqli_real_escape_string($mysqli, $result['points_scored'])
			. ', points_available = ' . 	mysqli_real_escape_string($mysqli, $result['points_available']);


			if (!$result = $mysqli->query($query)) {
				echo 'Query failed: ' . mysqli_error(mysqli);
				exit;
			}

		}

		echo 'Successful request';


	}


}



/* Get the highest timestamp from results we have  */
function getHighestTimeStamp($request_results_type){

	global $mysqli;

	if ( $request_results_type == 'groups' ){

		$table_name = 'classmarker_group_results';


	} else if ( $request_results_type == 'links'){

		$table_name = 'classmarker_link_results';
	}

	$query = 'SELECT max(time_finished) as highestTimestamp FROM '. $table_name;


	if ($result = $mysqli->query($query)) {
		while ( $obj = $result->fetch_object() ) {
			return $obj->highestTimestamp;
		}

	}

	/* Default will be 2 weeks ago if no timestamp is send to ClassMarker */
	return NULL;

}




