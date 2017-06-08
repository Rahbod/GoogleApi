<?php
namespace RahbodGoogleApi;

/**
 * Class CalendarEventModel
 * @package RahbodGoogleApi
 */
class CalendarEventModel
{
    /**
     * @var string id of the event.
     */
    public $id;
    /**
     * @var string Title of the event.
     */
    public $summary;
    /**
     * @var string Description of the event. Optional.
     */
    public $description;
    /**
     * @var string Geographic location of the event as free-form text. Optional.
     */
    public $location;
    /**
     * @var string Status of the event. Optional. Possible values are:
     *      "confirmed" - The event is confirmed. This is the default status.
     *      "tentative" - The event is tentatively confirmed.
     *      "cancelled" - The event is cancelled.
     */
    public $status = 'confirmed';
    /**
     *  The start time of the event.
     * @var array(
     *          "datetime", //The time, as a combined date-time value (formatted according to RFC3339). date("c", strtotime("YOURTIME"))
     *          "timeZone", //The time zone in which the time is specified. example: Asia/Tehran
     *      )
     */
    public $start = array();
    /**
     * The end time of the event.
     * @var array(
     *          "datetime", //The time, as a combined date-time value (formatted according to RFC3339).
     *          "timeZone", //The time zone in which the time is specified. example: Asia/Tehran
     *      )
     */
    public $end = array();
    /**
     * @var array
     */
    public $source = array();
    /**
     * List of RRULE, EXRULE, RDATE and EXDATE lines for a recurring event, as specified in RFC5545.
     * @var array
     */
    public $recurrence = array();
    /**
     * @var boolean Whether anyone can invite themselves to the event
     */
    public $anyoneCanAddSelf;
    /**
     * List of attachments. Get file details from google drive.
     * @var array(
     *          .
     *          .
     *          array(
     *              "fileUrl", // is Required.
     *              "title", // Optional
     *              "iconLink", // Optional
     *              "fileId", // Optional
     *              "mimeType", // Optional
     *          )
     *          .
     *          .
     * )
     */
    public $attachments = array();
    /**
     * List of attendees.
     * @var array(
     *          .
     *          .
     *          array(
     *              "fileUrl", // is Required.
     *              "title", // Optional
     *              "iconLink", // Optional
     *              "fileId", // Optional
     *              "mimeType", // Optional
     *          )
     *          .
     *          .
     * )
     */
    public $attendees = array();

    /**
     * Set Event Start time
     * @param $datetime string any date format
     * @param $timezone string The time zone in which the time is specified. example: Asia/Tehran
     */
    public function setStart($datetime, $timezone)
    {
        $this->start = [
            'dateTime' => date("c", strtotime($datetime)),
            'timeZone' => $timezone
        ];
    }

    /**
     * Set Event End time
     * @param $datetime string any date format
     * @param $timezone string The time zone in which the time is specified. example: Asia/Tehran
     */
    public function setEnd($datetime, $timezone)
    {
        $this->end = [
            'dateTime' => date("c", strtotime($datetime)),
            'timeZone' => $timezone
        ];
    }

    /**
     * Set Event Source
     * @param $title string Title of the source; for example a title of a web page or an email subject.
     * @param $url string URL of the source pointing to a resource. The URL scheme must be HTTP or HTTPS.
     */
    public function setSource($title, $url)
    {
        $this->source = [
            'datetime' => $title,
            'timeZone' => $url
        ];
    }

    /**
     * Add Attachment google drive file to event
     * @param $fileUrl string Google drive file fileUrl|alternativeLink property. is Required
     * @param $title string Title of file.
     * @param $iconLink string Google drive file iconLink property.
     * @param $fileId string The Google drive file id property.
     * @param $mimeType string The Google drive file mimeType property
     */
    public function addAttachment($fileUrl, $title = '', $iconLink = '', $fileId = '', $mimeType = '')
    {
        $this->attachments[] = [
            'fileUrl' => $fileUrl,
            'title' => $title,
            'iconLink' => $iconLink,
            'fileId' => $fileId,
            'mimeType' => $mimeType
        ];
    }

    /**
     * Add Attendees to event
     * @param $email string The attendee's email address, if available.
     * @param $displayName string The attendee's name, if available. Optional.
     * @param $comment string The time zone in which the time is specified. example: Asia/Tehran
     * @param $additionalGuests int Number of additional guests. Optional. The default is 0.
     * @param $responseStatus string The attendee's response status. Possible values are:
     *              "needsAction" - The attendee has not responded to the invitation.
     *              "declined" - The attendee has declined the invitation.
     *              "tentative" - The attendee has tentatively accepted the invitation.
     *              "accepted" - The attendee has accepted the invitation.
     * @param $optional boolean Whether this is an optional attendee. Optional.
     */
    public function addAttendees($email, $displayName = '', $comment = '', $additionalGuests = 0, $responseStatus = '', $optional = false)
    {
        $this->attendees[] = [
            'email' => $email,
            'displayName' => $displayName,
            'comment' => $comment,
            'additionalGuests' => $additionalGuests,
            'responseStatus' => $responseStatus,
            'optional' => $optional
        ];
    }

    /**
     * @param string $type (RRULE|EXRULE|RDATE|EXDATE)
     * @param string $freq (DAILY|WEEKLY|YEARLY)
     * @param string $count
     * @param string $interval
     * @param string $byday short weekday names, (Sa, Su, Mo, Tu, We, Th, Fr)
     * @param string $until short weekday names, (Sa, Su, Mo, Tu, We, Th, Fr)
     */
    public function addRecurrence($type = 'RRULE', $freq = 'DAILY', $interval = '', $count = '', $byday = '', $until = '')
    {
        $freq = $freq && !empty($freq)?"FREQ=$freq;":"";
        $count = $count && !empty($count)?"COUNT=$count;":"";
        $interval = $interval && !empty($interval)?"INTERVAL=$interval;":"";
        $byday = $byday && !empty($byday)?"BYDAY=$byday;":"";
        $until = $until && !empty($until)?"UNTIL=$until;":"";
        $this->recurrence[] = "$type:$count";
    }

    /**
     * @param \stdClass $values
     */
    public function load(\stdClass $values)
    {
        if(isset($values->id))
            $this->id = $values->id;
        $this->summary = isset($values->summary)?$values->summary:'';
        $this->description = isset($values->description)?$values->description:'';
        $this->location = isset($values->location)?$values->location:'';
        $this->status = isset($values->status)?$values->status:'';
        $this->start['dateTime'] = $values->start->dateTime;
        $this->end['dateTime'] = $values->end->dateTime;
        $this->start['timeZone'] = $values->start->timeZone;
        $this->end['timeZone'] = $values->end->timeZone;
        $this->recurrence = isset($values->recurrence)?$values->recurrence:'';
        if(isset($values->source) && isset($values->source->title))
            $this->source['title'] = $values->source->title;
        if(isset($values->source) && isset($values->source->url))
            $this->source['url'] = $values->source->url;
        if(isset($values->anyoneCanAddSelf))
            $this->anyoneCanAddSelf = $values->anyoneCanAddSelf;

        if(isset($values->recurrence)){
            foreach($values->recurrence as $recurrence)
                $this->addRecurrence($recurrence);
        }

        if(isset($values->attachments)){
            foreach($values->attachments as $attachment)
                $this->addAttachment($attachment->fileUrl,
                    isset($attachment->title)?$attachment->title:'',
                    isset($attachment->mimeType)?$attachment->mimeType:'',
                    isset($attachment->iconLink)?$attachment->iconLink:'',
                    isset($attachment->fileId)?$attachment->fileId:''
                );
        }

        if(isset($values->attendees)){
            foreach($values->attendees as $attendee)
                $this->addAttendees($attendee->email,
                    isset($attendee->displayName)?$attendee->displayName:'',
                    isset($attendee->comment)?$attendee->comment:'',
                    isset($attendee->additionalGuests)?$attendee->additionalGuests:'',
                    isset($attendee->responseStatus)?$attendee->responseStatus:'',
                    isset($attendee->optional)?$attendee->optional:''
                );
        }
    }

    /**
     * @param $obj1 CalendarEventModel
     * @param $obj2 CalendarEventModel
     * @return CalendarEventModel
     */
    public static function merge($obj1, $obj2)
    {
        if($obj2->summary)
            $obj1->summary = $obj2->summary;
        if($obj2->description)
            $obj1->description = $obj2->description;
        if($obj2->location)
            $obj1->location = $obj2->location;
        if($obj2->status)
            $obj1->status = $obj2->status;
        if($obj2->start){
            if(isset($obj2->start['dateTime']))
                $obj1->start['dateTime'] = $obj2->start['dateTime'];
            if(isset($obj2->start['timeZone']))
                $obj1->start['timeZone'] = $obj2->start['timeZone'];
        }
        if($obj2->end){
            if(isset($obj2->end['dateTime']))
                $obj1->end['dateTime'] = $obj2->end['dateTime'];
            if(isset($obj2->end['timeZone']))
                $obj1->end['timeZone'] = $obj2->end['timeZone'];
        }
        if($obj2->source){
            if(isset($obj2->source['title']))
                $obj1->source['title'] = $obj2->source['title'];
            if(isset($obj2->source['url']))
                $obj1->source['url'] = $obj2->source['url'];
        }
        if($obj2->recurrence)
            $obj1->recurrence = $obj2->recurrence;

        if($obj2->anyoneCanAddSelf)
            $obj1->anyoneCanAddSelf = $obj2->anyoneCanAddSelf;

        if($obj2->attachments === null || $obj2->attachments)
            $obj1->attachments = $obj2->attachments;

        if($obj2->attendees === null || $obj2->attendees)
            $obj1->attendees = $obj2->attendees;
        return $obj1;
    }
}