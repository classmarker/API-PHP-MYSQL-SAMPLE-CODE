<?php

/**********************************************************************************
 *
 * 					SET UP CREDENTIALS / FORMAT / REQUEST TYPE / DATABASE ACCESS (if using)
 *
 *  Keep this credentials file outside of www access for security.
 *
 *  Disclaimer:  ClassMarker Pty Ltd accepts no responsibility whatsoever from usage of these API scripts and Classes
 *  			 and shall be held harmless from any and all litigation, liability, and responsibilities.
 *
 *********************************************************************************/
$api_key =          'XXXX';  // your api key
$api_secret =       'XXXX';  // your api secret

$mysql_host =       'localhost';
$mysql_username =   'username';
$mysql_password =   '********';
$mysql_database =   'classmarker_results';


/* Add your administrators email here who can take support queries should they arise */
$administrator_email = 'example@example.com';



/* Connect to DB */
$mysqli = @mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database);
if (!$mysqli) {
    echo '<p>Error: Unable to connect to MySQL.</p>';
    echo '<p>Debugging errno: ' . mysqli_connect_errno() . '</p>';
    echo '<p>Debugging error: ' . mysqli_connect_error() . '</p>';
    exit;
}


