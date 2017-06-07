<?php
namespace RahbodGoogleApi;

class GoogleDrive
{
	const DRIVE_BASE_URI = 'https://www.googleapis.com/drive/v2';

	// Reporting errors
	public $debug = TRUE;

	// Source
	private $_source;

	// Authentication Data
	private $_auth;

	// Response Code
	private $_response_code;

	// CURL Headers
	public $_headers;

	public function __construct($_access_token, $_source = NULL)
	{
		if($_source == NULL){
			$this->_source = str_replace(' ', '_', '');
		}else{
			$this->_source = $_source;
		}

		// Set the access token
		$this->_auth = $_access_token;
		$this->setHeaders();
	}

	/**
	 *  Prepares the headers one time so we do not keep re-creating the headers
	 *
	 **/
	private function setHeaders($ifMatch = FALSE, $contentLength = NULL)
	{
		$this->_headers = array(
			"Authorization: Bearer " . $this->_auth,
			'Content-Type: application/json',
		);

		if($ifMatch){
			$this->_headers[] = 'If-Match: *';
		}

		if($contentLength != NULL){
			$this->_headers[] = 'Content-Length: ' . $contentLength;
		}
	}

	/**
	 *  Simple debug helper
	 *
	 * @param mixed $options
	 * @return print_r($option)
	 **/
	private function debug($options)
	{
		echo '<pre>';
		print_r($options);
		echo '</pre>';
	}

	/**
	 *  Public method to retrieve the last response code
	 *
	 * @return int/string $this->_response_code
	 **/
	public function getResponseCode()
	{
		return $this->_response_code;
	}

	public function isConnected()
	{
		return $this->_auth?true:false;
	}

	/**
	 *  Method to getListEvents events based upon a date range and calendar_id
	 *
	 * @param array $options
	 * @return array $results
	 **/
	public function getList($options = array())
	{
		if($this->isConnected()){
			// Parse the options to a usable format
			$data = http_build_query($options);
			// Build the Calendar URL
			$url = self::DRIVE_BASE_URI . "/files?" . $data;
			// Load the CURL Library
			$curl = new Curl($url);
			// Set the headers
			$curl->setHeader($this->_headers);
			// Make the request
			$response = json_decode($curl->run('GET'), true);
			// Set the response code for debugging purposes
			$this->_response_code = $curl->getStatus();
			// We should receive a 200 response. If we don't, return a blank array
			if($this->_response_code != '200')
				return array();

			// Build the results array
			$results = array(
				'totalResults' => count($response['items']),
				'files' => array()
			);
			foreach($response['items'] as $key => $file){
				$results['files'][$key]['id'] = $file['id'];
				$results['files'][$key]['title'] = $file['title'];
				$results['files'][$key]['mimeType'] = $file['mimeType'];
				$results['files'][$key]['fileUrl'] = $file['alternateLink'];
				$results['files'][$key]['iconLink'] = $file['iconLink'];
			}
			// Return the results as an array
			return $results;
		}else{
			// Debug Output
			if($this->debug == TRUE){
				echo 'Cannot complete query. No connection has been established.' . "\n";
			}
			return array();
		}
	}
}