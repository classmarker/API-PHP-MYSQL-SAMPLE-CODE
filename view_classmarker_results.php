<?php
/**
 * Script to view classMarker results from your systems ClassMarker tables
 * This script can be used in PHP Strict mode
 *
 * MySqli examples taken from php.net
 * For Help see
 * https://www.php.net/manual/en/mysqli-result.fetch-object.php
 *
 * @author ClasssMarker Pty Ltd
 * @see https://www.classmarker.com/online-testing/api/developers
 * @version 1
 * @copyright © 2012 ClasssMarker Pty Ltd
 * @license Copyright holder allows use of the code for any purpose
 *
 * Disclaimer:  ClassMarker Pty Ltd accepts no responsibility whatsoever from usage of these API scripts and Classes
 *  			and shall be held harmless from any and all litigation, liability, and responsibilities.
 *
 */


/**************************************************************
 *
 * This script retrieves all results from groups or links displaying from most recently taken to last
 *
 * You could modify this to show only particular test/group or test/link results by adding group/link/test ids
 *  to their respective sql calls
 *
 */

include ('credentials.php');

/* Pagination - how many results to display per page */
define('LIMIT', 50);




if (isset($_GET['view']) ){

	/* Set pagination requirements */

	if (isset($_GET['limit']) && is_numeric($_GET['limit'])){
		$limit = $_GET['limit'];
	} else {
		$limit = LIMIT;
	}

	if (isset($_GET['offset']) && is_numeric($_GET['offset'])){
		$offset = $_GET['offset'];
	} else {
		$offset = 0;
	}



}










?>
<!DOCTYPE div PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>ClassMarker Results</title>
<link rel="stylesheet" type="text/css" href="styles.css" media="screen">
</head>
<head>


</head>
<body>

	<h2>ClassMarker Results</h2>
	<div class="links">
		<a href="?view=recentGroupResults">Group results</a> | <a
			href="?view=recentLinkResults">Link results</a> <a class="floatright"
			href="mailto:<?php echo $administrator_email; ?>">Contact
			administrator: <?php echo $administrator_email; ?> </a>
	</div>


	<?php


	/* Are we showing results */
	if (isset($_GET['view']) ){

		if ($_GET['view'] == 'recentGroupResults'){

			displayGroupResults($mysqli, $offset, $limit);

		} else if ($_GET['view'] == 'recentLinkResults'){

			displayLinkResults($mysqli, $offset, $limit);
		}
	}



	?>



</body>
</html>

	<?php

	/* Retreive test names and ids */
	function getTests($mysqli){

		global $tests_array;

		$tests_array = array();
		$query = 'SELECT * from classmarker_tests';

		if ($result = $mysqli->query($query)) {

			while ($obj = $result->fetch_object()) {

				$tests_array[ $obj->test_id ] = $obj->test_name;

			}
		}

	}


	/* Retreive group names and ids */
	function getGroups($mysqli){

		global $groups_array;

		$groups_array = array();
		$query = 'SELECT * from classmarker_groups';

		if ($result = $mysqli->query($query)) {

			while ($obj = $result->fetch_object()) {

				$groups_array[ $obj->group_id ] = $obj->group_name;

			}
		}

	}


	/* Retreive link names and ids */
	function getLinks($mysqli){

		global $links_array;

		$links_array = array();
		$query = 'SELECT * from classmarker_links';

		if ($result = $mysqli->query($query)) {

			while ($obj = $result->fetch_object()) {

				$links_array[ $obj->link_id ] = $obj->link_name;

			}
		}

	}


	/* Format date */
	function formatDate($timestamp){
		return date("D jS M Y g:ia",$timestamp);
	}


	/* Alert if requires grading as score will not be final */
	function requiresGrading($value, $row=false){

		if ($row){
			if ($value == 'Yes'){
				return ' class="bgalert"';
			}
		} else {
		if ($value == 'Yes'){
			return '<span class="alert">'.$value.': Score may change</span>';
		} else {
			return $value;
		}
		}
	}


	/* Lets do page numbering. 1 2 3 4 etc */
	function showPaginationLinks($view, $offset, $limit=NULL, $total_results_count=NULL){


		$pagination_links='';

		$num_pages =  ceil($total_results_count / $limit);
		$page_we_are_on = ceil($offset / $limit);
		for ( $i=0;$i<$num_pages;$i++){
			if ($page_we_are_on == $i){
				$pagination_links .= ($i+1) . ' ';
			} else {
				$pagination_links .= '<a href="?view='.$view.'&amp;offset='.($i*$limit).'&amp;limit='.$limit.'">'.($i+1).'</a> ';
			}
		}


		return $pagination_links;
	}


	/* Display results taken from within groups */
	function displayGroupResults($mysqli, $offset, $limit){


		global $groups_array, $tests_array;


		/* Set required data */
		getTests($mysqli);
		getGroups($mysqli);

		/* Show most recent first */
		$query = 'SELECT SQL_CALC_FOUND_ROWS * from classmarker_group_results '
		. ' order by pk_id desc limit ' . mysqli_real_escape_string($mysqli, $offset) . ','.mysqli_real_escape_string($mysqli, $limit);

		if ($result = $mysqli->query($query)) {

			$query2 = "SELECT FOUND_ROWS() as found_rows";
			if ($result2 = $mysqli->query($query2)) {
				while ( $obj = $result2->fetch_object() ) {
					$total_rows_without_limit = $obj->found_rows;
				}
			}


			echo '<p>Number of results: '.$total_rows_without_limit.'</p>';

			$pagination_links = '<p>Page: ' .showPaginationLinks('recentGroupResults',$offset, $limit, $total_rows_without_limit).'</p>';

			echo $pagination_links;


			echo '<table class="resultsTable">
				<tr>
				<th>First</th>
				<th>Last</th>
				<th>% Points</th>
				<th>Points scores</th>
				<th>Points available</th>
				<th>Start time</th>
				<th>Finish time</th>
				<th>Duration</th>
				<th>Requires grading</th>
				<th>Test name</th>
				<th>Group name</th>
				<th>Email</th>
				</tr>';

			while ( $obj = $result->fetch_object() ) {
				//PK not in use but of course can be depending your integration requirements
				echo '<tr'.requiresGrading($obj->requires_grading, true).'>
					<td>'.$obj->first.'</td>
					<td>'.$obj->last.'</td>
					<td>'.$obj->percentage.'%</td>
					<td>'.$obj->points_scored.'</td>
					<td>'.$obj->points_available.'</td>
					<td>'.formatDate($obj->time_started).'</td>
					<td>'.formatDate($obj->time_finished).'</td>
					<td>'.$obj->duration.'</td>
					<td>'.requiresGrading($obj->requires_grading).'</td>
					<td>'.$tests_array[ $obj->test_id ].'</td>
					<td>'.$groups_array[ $obj->group_id ].'</td>
					<td>'.$obj->email.'</td>
					</tr>'."\n";
			}

			echo '</table>';


			echo $pagination_links;


		}

	}


	/* Display results taken from Direct links */
	function displayLinkResults($mysqli, $offset, $limit){


		global $links_array, $tests_array;


		/* Set required data */
		getTests($mysqli);
		getLinks($mysqli);

		/* Show most recent first */
		$query = 'SELECT SQL_CALC_FOUND_ROWS * from classmarker_link_results '
		. ' order by pk_id desc limit ' . mysqli_real_escape_string($mysqli, $offset) . ','.mysqli_real_escape_string($mysqli, $limit);

		if ($result = $mysqli->query($query)) {

			$query2 = "SELECT FOUND_ROWS() as found_rows";
			if ($result2 = $mysqli->query($query2)) {
				while ( $obj = $result2->fetch_object() ) {
					$total_rows_without_limit = $obj->found_rows;
				}
			}

			echo '<p>Number of results: '.$total_rows_without_limit.'</p>';

			$pagination_links = '<p>Page: ' .showPaginationLinks('recentLinkResults',$offset, $limit, $total_rows_without_limit).'</p>';

			echo $pagination_links;


			echo '<table class="resultsTable">
				<tr>
				<th>First</th>
				<th>Last</th>
				<th>% Points</th>
				<th>Points scores</th>
				<th>Points available</th>
				<th>Start time</th>
				<th>Finish time</th>
				<th>Duration</th>
				<th>Requires grading</th>
				<th>Test name</th>
				<th>Link name</th>
				<th>Email</th>
				<th>Ip Address</th>
				<th>CM User Id</th>
				<th>Access Code</th>
				<th>Extra information 1</th>
				<th>Extra information 2</th>
				<th>Extra information 3</th>
				<th>Extra information 4</th>
				<th>Extra information 5</th>
				</tr>';

			while ( $obj = $result->fetch_object() ) {
				//PK not in use but of course can be depending your integration requirements
				echo '<tr'.requiresGrading($obj->requires_grading, true).'>
					<td>'.$obj->first.'</td>
					<td>'.$obj->last.'</td>
					<td>'.$obj->percentage.'%</td>
					<td>'.$obj->points_scored.'</td>
					<td>'.$obj->points_available.'</td>
					<td>'.formatDate($obj->time_started).'</td>
					<td>'.formatDate($obj->time_finished).'</td>
					<td>'.$obj->duration.'</td>
					<td>'.requiresGrading($obj->requires_grading).'</td>
					<td>'.$tests_array[ $obj->test_id ].'</td>
					<td>'.$links_array[ $obj->link_id ].'</td>
					<td>'.$obj->email.'</td>
					<td>'.$obj->ip_address.'</td>
					<td>'.$obj->cm_user_id.'</td>
					<td>'.$obj->access_code.'</td>
					<td>'.nl2br($obj->extra_info).'</td>
					<td>'.nl2br($obj->extra_info2).'</td>
					<td>'.nl2br($obj->extra_info3).'</td>
					<td>'.nl2br($obj->extra_info4).'</td>
					<td>'.nl2br($obj->extra_info5).'</td>
					</tr>'."\n";
			}

			echo '</table>';


			echo $pagination_links;


		}

	}
	?>

