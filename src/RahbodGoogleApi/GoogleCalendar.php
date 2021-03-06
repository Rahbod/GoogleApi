<?php
namespace RahbodGoogleApi;

class GoogleCalendar
{
    const CALENDAR_BASE_URI = 'https://www.googleapis.com/calendar/v3';

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
     * @param string $calendar_id
     * @param array $options
     * @subparam datetime $timeMin
     * @subparam datetime $timeMax
     * @subparam bool $singleEvents (true|flase)
     * @subparam string $orderBy    (startTime|updated) this option working when single item be true
     * @subparam int $maxResults    (50)
     * @subparam string $timeZone
     *
     *  Example $options
     *    array(
     *        'timeMin'=>date('c', strtotime("8 am")),
     *        'timeMax'=>date('c', strtotime("5 pm")),
     *        'maxResults'=>5,
     *        'singleEvents'=>true,
     *        'orderBy'=>'startTime',
     *        'timeZone'=>'Asia/Tehran',
     *    )
     *
     *
     * @return array $results
     **/
    public function getListEvents($calendar_id = "primary", $options = array())
    {
        if($this->isConnected()){
            // Parse the options to a usable format
            $data=[];
            if(isset($options['timeMin']))
                $data['timeMin'] = date('Y-m-d\TH:i:sP', strtotime($options['timeMin']));
            if(isset($options['timeMax']))
                $data['timeMax'] = date('Y-m-d\TH:i:sP', strtotime($options['timeMax']));
            if(isset($options['maxResults']))
                $data['maxResults'] = $options['maxResults'];
            $data['timeZone'] = (!isset($options['timeZone']))?'Asia/Tehran':$options['timeZone'];
            if(isset($options['singleEvents']) && $options['singleEvents']){
                $data['orderBy'] = (!isset($options['orderBy']))?'starttime':$options['orderBy'];
                $data['singleEvents'] = "true";
            }
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
            $i=0;
            foreach($response['items'] as $item){
                if(isset($item['recurrence']))
                {
                    $results['events'][$i] = $item;
                    $instances = $this->getInstances($calendar_id, $item['id'], array(), true);
                    if($instances){
                        foreach($instances['instances'] as $instance){
                            $i++;
                            $results['events'][$i] = $instance;
                        }
                    }
                }
                $results['events'][$i]  = $item;
                $i++;
            }
            var_dump($results);exit();
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

    /**
     *  Method to create new events
     * @param string $calendar_id
     * @param bool $recurring
     * @param CalendarEventModel $eventModel
     * @return array
     **/
    public function insert($calendar_id = "primary", CalendarEventModel $eventModel, $recurring = false)
    {
        if($this->isConnected()){
            // Verify the options are properly  set
            if(!empty($eventModel)){
                // End isset validation
                $queryParams = http_build_query(array('supportsAttachments' => "true"));

                $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events?" . $queryParams;

                // Load the CURL Library
                $curl = new Curl($url);

                // Set the initial headers
                $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);
                // Send Request
                $response = json_decode($curl->run('POST', json_encode($eventModel)), true);
                // Set the response code for debugging purposes
                $this->_response_code = $curl->getStatus();
                // We should receive a 200 response. If we don't, return a blank array
                if($this->_response_code != '200'){
                    if($this->debug)
                        die($response['error']['message']);
                    return [
                        'error' => true,
                        'code' => $this->_response_code,
                        'message' => $response['error']['message']
                    ];
                }
                return [
                    'eventId' => $response['id'],
                    'eventStatus' => $response['status'],
                ];
            }else{
                if($this->debug == TRUE){
                    echo 'Event Model are not properly set' . "\n";
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
     * @param string $event_id
     * @param CalendarEventModel $eventModel
     * @return array
     **/
    public function update($calendar_id = "primary", $event_id, $eventModel)
    {

        if($this->isConnected()){
            if(!empty($eventModel)){
                $queryParams = http_build_query(array('supportsAttachments' => "true"));
                $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$event_id}?" . $queryParams;
                // End isset validation
                $curl = new Curl($url);
                $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);

                // Send request to get event details
                $data = json_decode($curl->run('GET'));
                unset($curl);
                $lastModel = new CalendarEventModel();
                $lastModel->load($data);
                // Load new CURL instance
                $curl = new Curl($url);
                $data = CalendarEventModel::merge($lastModel,$eventModel);
                // Set the initial headers
                $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);
                // Send Request
                $response = json_decode($curl->run('PUT', json_encode($data)), true);
                // Set the response code for debugging purposes
                $this->_response_code = $curl->getStatus();
                // We should receive a 200 response. If we don't, return a blank array
                if($this->_response_code != '200'){
                    if($this->debug)
                        die($response['error']['message']);
                    return [
                        'error' => true,
                        'code' => $this->_response_code,
                        'message' => $response['error']['message']
                    ];
                }
                return [
                    'eventId' => $response['id'],
                    'eventStatus' => $response['status'],
                ];
            }else{
                if($this->debug == TRUE){
                    echo 'Event Model are not properly set' . "\n";
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
     *  Method to get events details
     * @param string $calendar_id
     * @param string $event_id
     *
     * @return array
     **/
    public function get($calendar_id = "primary", $event_id)
    {
        if($this->isConnected()){
            $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$event_id}";
            // End isset validation
            $curl = new Curl($url);
            $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);
            // send request
            $response = json_decode($curl->run('GET'));
            $model = new CalendarEventModel();
            $model->load($response);
            $this->_response_code = $curl->getStatus();
            // We should receive a 200 response. If we don't, return a blank array
            if($this->_response_code != '200'){
                if($this->debug)
                    die($response['error']['message']);
                return [
                    'error' => true,
                    'code' => $this->_response_code,
                    'message' => $response['error']['message']
                ];
            }
            return $model;
        }else{
            if($this->debug == TRUE){
                echo 'No connection has been started' . "\n";
                return array();
            }
        }
        return false;
    }


    /**
     *  Method to instances of recurring events
     *
     * @param string $calendar_id
     * @param string $event_id
     * @param array $options
     * @subparam datetime $timeMin
     * @subparam datetime $timeMax
     * @subparam bool showDeleted
     * @subparam int $maxResults    (50)
     * @subparam string $timeZone
     *
     *  Example $options
     *    array(
     *        'timeMin'=>date('c', strtotime("8 am")),
     *        'timeMax'=>date('c', strtotime("5 pm")),
     *        'maxResults'=>5,
     *        'showDeleted'=>'true',
     *        'timeZone'=>'Asia/Tehran',
     *    )
     * @param bool $fullDetails when true return all details of instance events
     *
     * @return array $results
     **/
    public function getInstances($calendar_id = "primary", $event_id, $options = array(), $fullDetails = false)
    {
        if($this->isConnected()){
            $data=[];
            if(isset($options['timeMin']))
                $data['timeMin'] = date('Y-m-d\TH:i:sP', strtotime($options['timeMin']));
            if(isset($options['timeMax']))
                $data['timeMax'] = date('Y-m-d\TH:i:sP', strtotime($options['timeMax']));
            if(isset($options['maxResults']))
                $data['maxResults'] = $options['maxResults'];
            if(isset($options['showDeleted']))
                $data['showDeleted'] = $options['showDeleted'];
            $data['timeZone'] = (!isset($options['timeZone']))?'Asia/Tehran':$options['timeZone'];
            $data['orderBy'] = (!isset($options['orderBy']))?'startTime':$options['orderBy'];
            $data = http_build_query($data);

            $url = self::CALENDAR_BASE_URI . "/calendars/{$calendar_id}/events/{$event_id}/instances?".$data;
            // End isset validation
            $curl = new Curl($url);
            $curl->setHeader($this->_headers, $url, TRUE, TRUE, 30);
            // send request
            $response = json_decode($curl->run('GET'),true);
            $this->_response_code = $curl->getStatus();
            // We should receive a 200 response. If we don't, return a blank array
            if($this->_response_code != '200'){
                if($this->debug)
                    die($response['error']['message']);
                return [
                    'error' => true,
                    'code' => $this->_response_code,
                    'message' => $response['error']['message']
                ];
            }
            $results = array(
                'totalResults' => count($response['items']),
                'instances' => array()
            );
            if(!$fullDetails)
                foreach($response['items'] as $key=>$item)
                    $results['instances'][$key] = $item['id'];
            else
                $results['instances'] = $response['items'];
            return $results;
        }else{
            if($this->debug == TRUE){
                echo 'No connection has been started' . "\n";
                return array();
            }
        }
        return false;
    }

    /**
     *  Method to delete events
     * @param string $calendar_id
     * @param array $event_id
     *
     *
     * @return bool response
     *    TRUE if the delete was successful, FALSE otherwise
     **/
    public function delete($calendar_id = "primary", $event_id)
    {
        if($this->isConnected()){
            if(!isset($calendar_id)){
                if($this->debug == TRUE){
                    echo 'Calendar ID was not set' . "\n";
                }
                return false;
            }

            // Retrieve and set the URL
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
                return [
                    'eventId' => $event_id,
                    'eventStatus' => 'cancelled',
                ];
            }else{
                if($this->debug)
                    die($response['error']['message']);
                return [
                    'error' => true,
                    'code' => $this->_response_code,
                    'message' => $response['error']['message']
                ];
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
     *  Method to get calendars
     *
     * @return bool response
     *    TRUE if the delete was successful, FALSE otherwise
     **/
    public function getCalendarList()
    {
        if($this->isConnected()){
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
        }else{
            if($this->debug == TRUE){
                echo 'No connection has been started' . "\n";
                return array();
            }
        }
        return false;
    }

    /**
     * @return CalendarEventModel
     */
    public function getNewEventModel()
    {
        return new CalendarEventModel();
    }
}