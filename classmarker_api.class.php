<?php
/**
 * Class to validate and call api requests
 * This class can be used in PHP Strict mode
 *
 * @author ClasssMarker Pty Ltd
 * @see https://www.classmarker.com/online-testing/api/developers/
 * @version 1
 * @copyright © 2012 ClasssMarker Pty Ltd
 * @licence Copyright holder allows use of the code for any purpose
 *
 * Disclaimer:  ClassMarker Pty Ltd accepts no responsibility whatsoever from usage of these API scripts and Classes
 *  			and shall be held harmless from any and all litigation, liability, and responsibilities.
 */


Class classmarker_api {

	private $apiKey;			//Your API Key
	private $secret;			//Your API secret string
	private $signature;			//Your signature for verification - created by self::setApiCredentials()
	private $finished_after_timestamp;//return only results taken after this time. -2 weeks as far back as results can be retrieved - Save your results in your tables.
	private $limit;				//how many results to return. Max 200

	private $api_version;		//Api version you are requesting
	private $api_base_url;		//ClassMarker's api base url to call
	private $request_path;		//The request path to get your results. Example /v1/recent_results.json
	private $request_parameters;//The paramaters snd to ClassMarker. 	Example ?api_key, signature, timestamps etc

	public  $request_type;		// get_available_tests | get_results


	private $error_message; 	//used to see is an error has occurred "before" making a request to ClassMarker
	private $response;			//ClassMarker response





	/**
	 * Initiate Class
	 * @param string $apiKey
	 * @param string $secret
	 * @param int $api_version
	 */
	function __construct( $apiKey, $secret, $format='json', $api_version=1 ) {

		$this->apiKey = 		$apiKey;
		$this->secret = 		$secret;
		$this->api_version = 	$api_version;
		$this->api_base_url = 	'https://api.classmarker.com/';
		$this->error_message = 	false;
		$this->limit = 			200;

		$this->setResponseFormat($format);

	}



	/**
	 * Set format type for response
	 * @param string $format // json or xml
	 */
	function setResponseFormat($format='json'){

		$format = strtolower($format);
		if ($format == 'xml'){
			$this->format = '.xml';
		} else {
			/* default json */
			$this->format = '.json';
		}

	}



	/**
	 * Create a signature & set api_key,timestamp for sending to ClassMarker
	 * Note: Your server should be set to the ATOMIC clock as the time() value should be within a 5 minute range
	 */
	function setApiCredentials(){

		if (!$this->setApiVersion($this->api_version)){
			return false;
		}

		$signature = md5($this->apiKey . $this->secret . time());

		$this->request_parameters = 'api_key='.$this->apiKey.'&signature='.$signature.'&timestamp='.time();

		return true;
	}



	/**
	 * Set API version we are calling
	 * @param int $api_version
	 */
	function setApiVersion($api_version){

		/* Only version 1 available at present */
		$available_versions = array(1);

		if ( !in_array($api_version, $available_versions) ){
			$this->error_message = 'Version not available';
			return false;
		}

		$this->api_version = $api_version;

		return true;

	}



	/**
	 * Set error message
	 * @param string $str
	 */
	private function setErrorMsg($str){

		$this->error_message = $str;

	}



	/**
	 * Get error message
	 */
	function getError(){

		return $this->error_message;

	}



	/**
	 * Get error message
	 */
	function doesPreRequestErrorExists(){

		if ($this->error_message !== false){
			return true;
		} else {
			return false;
		}

	}



	/**
	 * Set path and call request to get available groups for API key
	 */
	function getAvailableGroups(){

		$this->request_type = 'get_available_tests';

		/* Set api crediantions */
		if (!$this->setApiCredentials()){
			return false;
		}

		/* Set request path */
		$this->request_path = '/v'.$this->api_version.$this->format.'?'.$this->request_parameters;


		if ( !$this->makeRequest() ){
			return false;
		}

		return true;

	}



	/**
	 * Set path and call request to get Recent results for all groups/links available to API key
	 * @param string $group_type
	 * @param int $limit
	 * @param int $finished_after_timestamp
	 */
	function getRecentResults($group_type='groups', $finished_after_timestamp=NULL, $limit=200){

		$this->request_type = 'get_results';

		/* Validate requirements */
		if ($group_type != 'groups' && $group_type != 'links'){

			$this->error_message ='"link" or "group" value not specified';

			return false;
		}




		/* Set API credientials */
		$this->setApiCredentials();



		/* Basic $limit check - else use 200 as set in constructor */
		if (is_numeric($limit) && $limit <= 200){
			$this->limit = $limit;
		}
		$this->request_parameters .= '&limit='.$this->limit;



		/* Basic $finished_after_timestamp check - else ClassMarker API will default use timestamp from 2 weeks ago */
		if (is_numeric($finished_after_timestamp) && $finished_after_timestamp >= strtotime("-2 weeks")){
			$this->finished_after_timestamp = $finished_after_timestamp;
		} else {
			$this->finished_after_timestamp = strtotime("-2 weeks");
		}
		$this->request_parameters .= '&finishedAfterTimestamp='.$this->finished_after_timestamp;



		/* Set request path */
		$this->request_path = 'v'.$this->api_version.'/'.$group_type.'/recent_results'.$this->format.'?'.$this->request_parameters;


		if ( !$this->makeRequest() ){
			return false;
		}

		return true;

	}



	/**
	 * Set path and call request to get results for given group_id/link_id and test
	 * @param string $group_type
	 * @param int $group_or_link_id
	 * @param int $test_id
	 * @param int $limit
	 * @param in $finished_after_timestamp
	 */
	function getSingleTestResults($group_type='groups', $group_or_link_id=NULL, $test_id=NULL, $finished_after_timestamp='', $limit=200 ){


		/* Validate requirements */
		if ($group_type != 'groups' && $group_type != 'links'){
			$this->error_message = 'group_type value "link" or "group" not specified';
			return false;
		}

		if (!is_numeric($group_or_link_id) || !is_numeric($test_id)){
			$this->error_message =  'group_id or test_id missing';
			return false;
		}


		/* Set api crediantions */
		$this->setApiCredentials();


		/* Basic $limit check - else use 200 as set in constructor */
		if (is_numeric($limit) && $limit <= 200){
			$this->limit = $limit;
		}
		$this->request_parameters .= '&limit='.$this->limit;



		/* Basic $finished_after_timestamp check - else use timestamp from 2 weeks ago set in constructor */
		if (is_numeric($finished_after_timestamp) && $finished_after_timestamp >= strtotime("-2 weeks")){
			$this->finished_after_timestamp = $finished_after_timestamp;
		}
		$this->request_parameters .= '&finishedAfterTimestamp='.$this->finished_after_timestamp;



		/* Set request path */
		$this->request_path = 'v'.$this->api_version.'/'.$group_type.'/'.$group_or_link_id.'/tests/'.$test_id.'/recent_results'.$this->format.'?'.$this->request_parameters;

		if ( !$this->makeRequest() ){
			return false;
		}

		return true;

	}




	/**
	 * Make request to ClassMarker
	 */
	private function makeRequest() {


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->api_base_url . $this->request_path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/* Get response */
		$this->response = curl_exec($ch);

		if ( $this->response === false){
			$this->error_message = 'cURL failed on curl_exec(). Check you have cURL installed on your server.';
			return false;
		}

		/* close curl */
		curl_close($ch);

	}



	/**
	 * Get response returned from ClassMarker
	 */
	function getResponse() {

		return $this->response;

	}

}


