<?php
namespace RahbodGoogleApi;
class GoogleCalendar
{
    const CALENDAR_BASE_URI = 'https://www.googleapis.com/calendar/v3';

    // Reporting errors
    private $debug = TRUE;

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
     * @param string $calendar_id
     * @param array $options
     * @subparam datetime $timeMin
     * @subparam datetime $timeMax
     * @subparam string $orderBy    (startTime|updated)
     * @subparam int $maxResults    (50)
     * @subparam string $timeZone
     *
     *  Example $options
     *    array(
     *        'timeMin'=>date('c', strtotime("8 am")),
     *        'timeMax'=>date('c', strtotime("5 pm")),
     *        'maxResults'=>5,
     *        'orderBy'=>'startTime',
     *        'timeZone'=>'Asia/Tehran',
     *    )
     *
     * @return array $results
     **/
    public function getListEvents($calendar_id, $options = array())
    {
        if($this->isConnected()){
            if(!empty($options) && is_array($options)){
                // Parse the options to a usable format
                $data['timeMin'] = (!isset($options['timeMin']))?date('Y-m-d\T00:i:sP'):date('Y-m-d\TH:i:sP', strtotime($options['timeMin']));
                $data['timeMax'] = (!isset($options['timeMax']))?date('Y-m-d\T23:59:59P'):date('Y-m-d\TH:i:sP', strtotime($options['timeMax']));
                $data['maxResults'] = (!isset($options['maxResults']))?50:$options['maxResults'];
                $data['timeZone'] = (!isset($options['timeZone']))?'Asia/Tehran':$options['timeZone'];
                $data['orderBy'] = (!isset($options['orderBy']))?'startTime':$options['orderBy'];
                $data['singleEvents'] = "true";
                $data = http_build_query($data);
                // Build the Calendar URL
                $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events?" . $data;
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
                    'events' => array()
                );
                $results['events'] = $response['items'];
                // Return the results as an array
                return $results;

            }else{
                // Debug Output
                if($this->debug == TRUE){
                    if(empty($options)){
                        echo 'No options were specified' . "\n";
                    }
                }
                return array();
            }
        }else{
            // Debug Output
            if($this->debug == TRUE){
                echo 'Cannot complete query. No connection has been established.' . "\n";
            }
            return array();
        }
    }

    /**
     *  Method to create new events
     * @param int $calendar_id
     * @param bool $recurring
     * @param array $options | $type == 1
     * @subparam array $start
     * @subparam array $end
     * @subparam string $summary title of event
     * @subparam string $location
     * @subparam string $description
     * @subparam string $timeZone
     * @subparam string $status
     *
     *  Example Options for Single Events
     *        array(
     *        'start'=>date('c', strtotime("8 am")),
     *        'end'=>date('c', strtotime("5 pm")),
     *        'summary'=>'Meeting with Jane',
     *        'description'=>'Discuss business plan',
     *        'location'=>'My Office',
     *        )
     *
     * @return array
     **/
    public function insert($calendar_id, $options, $recurring = false)
    {
        if($this->isConnected()){
            // Verify the options are properly  set
            if(!empty($options) && is_array($options)){

                // Verify the required fields are set to something
                if(!isset($options['summary'])){
                    if($this->debug == TRUE){
                        echo 'No title was specified for event creation' . "\n";
                    }
                    return array();
                }

                if(!isset($options['start'])){
                    if($this->debug == TRUE){
                        echo 'No start time specified for event creation' . "\n";
                    }
                    return array();
                }

                if(!isset($options['end'])){
                    if($this->debug == TRUE){
                        echo 'No end time specified for event creation' . "\n";
                    }
                    return array();
                }

                // End isset validation
                $queryParams = http_build_query(array('supportsAttachments' => "true"));

                $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events?" . $queryParams;

                // Load the CURL Library
                $curl = new Curl($url);
                // Create a blank data set
                $data = array();
                // If we are creating a single event, or doing anything else not specified below
                $data = $options;
                $data['start'] = array(
                    'dateTime' => date('c', strtotime($options['start'])),
                    'timeZone' => isset($options['timeZone'])?$options['timeZone']:'Asia/Tehran',
                );
                $data['end'] = array(
                    'dateTime' => date('c', strtotime($options['end'])),
                    'timeZone' => isset($options['timeZone'])?$options['timeZone']:'Asia/Tehran',
                );

                if(!$recurring)
                    unset($data['recurrence']);

                // Set the initial headers
                $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);

                // Make an initial request to get the GSESSIONID
                $response = json_decode($curl->run('POST', json_encode($data)), true);
                // Set the response code for debugging purposes
                $this->_response_code = $curl->getStatus();
                // We should receive a 200 response. If we don't, return a blank array
                if($this->_response_code != '200')
                    return false;
                return $response;
            }else{
                if($this->debug == TRUE){
                    echo 'Options are not properly set' . "\n";
                    return array();
                }
            }
        }else{
            if($this->debug == TRUE){
                echo 'No connection has been started' . "\n";
                return array();
            }
        }
        return false;
    }

    /**
     *  Method to update events
     * @param string $calendar_id
     * @param array $options
     * @subparam string   $id
     * @subparam bool     $canEdit
     * @subparam string   $title
     * @subparam string   $details
     * @subparam string   $location
     * @subparam datetime $start
     * @subparam datetime $end
     *  Example Options for Update
     *      array(
     *        'id'=>'calendar_id'
     *        'start'=>date('c', strtotime("8 am")),
     *        'end'=>date('c', strtotime("5 pm")),
     *        'title'=>'Meeting with Jane',
     *        'details'=>'Discuss business plan',
     *        'location'=>'My Office',
     *      )
     * @return array
     **/
    public function update($calendar_id, $options = array())
    {
        if(!empty($options)){
            // Begin Validation
            if(!isset($options['id'])){
                if($this->debug == TRUE){
                    echo 'ID was not set' . "\n";
                }
                return array();
            }

            if(!isset($calendar_id)){
                if($this->debug == TRUE){
                    echo 'Calendar ID was not set' . "\n";
                }
                return array();
            }

            $queryParams = http_build_query(array('supportsAttachments' => "true"));
            $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$options['id']}?" . $queryParams;
            // End isset validation

            $curl = new Curl($url);
            $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);

            // Make an initial request to get the GSESSIONID
            $data = json_decode($curl->run('GET'), true);
            unset($curl);

            // Load new CURL instance
            $curl = new Curl($url);
            $data = array_merge($data, $options);
            if(isset($options['start'])){
                $data['start'] = array(
                    'dateTime' => date('c', strtotime($options['start'])),
                    'timeZone' => isset($options['timeZone'])?$options['timeZone']:'Asia/Tehran',
                );
            }

            if(isset($options['end'])){
                $data['end'] = array(
                    'dateTime' => date('c', strtotime($options['end'])),
                    'timeZone' => isset($options['timeZone'])?$options['timeZone']:'Asia/Tehran',
                );
            }

            // Set the initial headers
            $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);
            // Make an initial request to get the GSESSIONID
            $response = json_decode($curl->run('PUT', json_encode($data)), true);
            // Set the response code for debugging purposes
            $this->_response_code = $curl->getStatus();
            // We should receive a 200 response. If we don't, return a blank array
            if($this->_response_code != '200')
                return false;
            return $response;
        }else{
            if($this->debug == TRUE){
                echo 'Event ID was not set' . "\n";
                return array();
            }
        }
    }

    /**
     *  Method to get events details
     * @param string $calendar_id
     * @param array $options
     * @subparam string   $id
     *
     * @return array
     **/
    public function get($calendar_id, $options = array())
    {
        if(!empty($options)){
            // Begin Validation
            if(!isset($options['id'])){
                if($this->debug == TRUE){
                    echo 'ID was not set' . "\n";
                }
                return array();
            }

            if(!isset($calendar_id)){
                if($this->debug == TRUE){
                    echo 'Calendar ID was not set' . "\n";
                }
                return array();
            }

            $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$options['id']}";
            // End isset validation

            $curl = new Curl($url);
            $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);

            // Make an initial request to get the GSESSIONID
            $response = json_decode($curl->run('GET'), true);
            $this->_response_code = $curl->getStatus();
            // We should receive a 200 response. If we don't, return a blank array
            if($this->_response_code != '200')
                return false;
            return $response;
        }else{
            if($this->debug == TRUE){
                echo 'Event ID was not set' . "\n";
                return array();
            }
        }
    }

    /**
     *  Method to delete events
     * @param array $options
     *
     *
     * @return bool response
     *    TRUE if the delete was successful, FALSE otherwise
     **/
    public function delete($calendar_id, $options = array())
    {
        if(!empty($options)){

            // Begin Validation
            if(!isset($options['id'])){
                if($this->debug == TRUE){
                    echo 'ID was not set' . "\n";
                }
                return false;
            }

            if(!isset($calendar_id)){
                if($this->debug == TRUE){
                    echo 'Calendar ID was not set' . "\n";
                }
                return false;
            }

            // End isset validation

            // Retrieve and set the URL
            $event_id = $options['id'];

            $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$event_id}";

            // Load the CURL Library
            $curl = new Curl($url);

            // Set the headers for an If-Match Request
            $this->setHeaders(TRUE);

            // Set the header for the CURL request
            $curl->setHeader($this->_headers, $url, false);

            // Reset the Headers
            $this->setHeaders();

            // Make the request
            $response = json_decode($curl->run('DELETE'), true);
            // Set the response code for debugging purposes
            $this->_response_code = $curl->getStatus();

            if(($this->_response_code == 200 || $this->_response_code == 204) && $response == NULL){
                return true;
            }else{
                if($this->debug == TRUE){
                    echo 'Deletion failed with response code: ' . $this->_response_code . "\n";
                    echo 'Message: ' . $response['error']['message'];
                }
            }
            return false;
        }else{
            if($this->debug == TRUE){
                echo 'Event ID was not set' . "\n";
                return false;
            }
        }
    }

    /**
     *  Method to get calendars
     * @param array $options
     *
     *
     * @return bool response
     *    TRUE if the delete was successful, FALSE otherwise
     **/
    public function getCalendarList($options = array())
    {
        $data = http_build_query($options);
        $url = "https://content.googleapis.com/calendar/v3/users/me/calendarList";

        // Load the CURL Library
        $curl = new Curl($url);

        // Set the headers for an If-Match Request
        $this->setHeaders();
        // Set the header for the CURL request
        $curl->setHeader($this->_headers, $url, false);

        // Make the request
        $response = json_decode($curl->run('GET'), true);
        // Set the response code for debugging purposes
        $this->_response_code = $curl->getStatus();

        if($this->_response_code != 200)
            return false;
        // Build the results array
        $results = array();
        $results['totalResults'] = count($response['items']);
        $results = array_merge($response, $results);
        // Return the results as an array
        return $results;
    }
}