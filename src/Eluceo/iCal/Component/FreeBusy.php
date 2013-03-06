<?php

/*
 * This file is part of the eluceo/iCal package.
 *
 * (c) Markus Poerschke <markus@eluceo.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eluceo\iCal\Component;

use Eluceo\iCal\Component;
use Eluceo\iCal\PropertyBag;
use Eluceo\iCal\Property;

/**
 * Implementation of the EVENT component
 */
class FreeBusy extends Component
{
    /**
     * @var string
     */
    protected $uniqueId;

	protected $dtStamp;

    /**
     * @var \DateTime
     */
    protected $dtStart;

    /**
     * @var \DateTime
     */
    protected $dtEnd;

	/**
	 * @var array
	 */
	protected $freeBusyTimes = array();


    /**
     * @var string
     */
    protected $url;

	/**
	 * @var string
	 */
	protected $attendee;

	/**
     * @var string
     */
    protected $organizer;

    /**
     * If set to true the timezone will be added to the event
     *
     * @var bool
     */
    protected $useTimezone = false;

    function __construct($uniqueId = null)
    {
        if (null == $uniqueId) {
            $uniqueId = uniqid();
        }

        $this->uniqueId = $uniqueId;
    	$this->dtStamp = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'VFREEBUSY';
    }

    /**
     * {@inheritdoc}
     */
    public function buildPropertyBag()
    {
        $this->properties = new PropertyBag;

    	if (count($this->freeBusyTimes) === 0) {
    		return;
    	}

        // mandatory information
    	$this->properties->set('UID', $this->uniqueId);
    	$this->properties->set('ATTENDEE', $this->attendee);
    	$this->properties->add($this->buildDateTimeProperty('DTSTAMP', $this->dtStamp, false));
        $this->properties->add($this->buildDateTimeProperty('DTSTART', $this->dtStart, false));
        $this->properties->add($this->buildDateTimeProperty('DTEND', $this->dtEnd, false));

    	ksort($this->freeBusyTimes);
    	foreach ($this->freeBusyTimes as $fsbItem){
    		$fsbType = $fsbItem[0];
    		$fsbValue = $fsbItem[1];
    		$this->properties->set('FREEBUSY', $fsbValue, array('FSBTYPE' => $fsbType));
    	}

        $this->properties->set('ORGANIZER', $this->organizer);

        // optional information
        if (null != $this->url) {
            $this->properties->set('URL', $this->url);
        }

    }

    /**
     * Creates a Property based on a DateTime object
     *
     * @param string        $name       The name of the Property
     * @param \DateTime     $dateTime   The DateTime
     * @param bool          $noTime     Indicates if the time will be added
     * @return \Eluceo\iCal\Property
     */
    protected function buildDateTimeProperty($name, \DateTime $dateTime, $noTime = false)
    {
        $dateString = $this->getDateString($dateTime, $noTime);
        $params     = array();

        if ($this->useTimezone) {
            $timeZone       = $dateTime->getTimezone()->getName();
            $params['TZID'] = $timeZone;
        }

        if( $noTime )
            $params['VALUE'] = 'DATE';

        return new Property($name, $dateString, $params);
    }

    /**
     * Returns the date format that can be passed to DateTime::format()
     *
     * @param bool $noTime Indicates if the time will be added
     * @return string
     */
    protected function getDateFormat($noTime = false)
    {
        return $noTime ? 'Ymd' : 'Ymd\THis\Z';
    }

    /**
     * Returns a formatted date string
     *
     * @param \DateTime|null  $dateTime  The DateTime object
     * @param bool            $noTime    Indicates if the time will be added
     * @return mixed
     */
    protected function getDateString(\DateTime $dateTime = null, $noTime = false)
    {
        if (empty($dateTime)) {
            $dateTime = new \DateTime();
        }

        return $dateTime->format($this->getDateFormat($noTime));
    }

    public function setDtEnd($dtEnd)
    {
        $this->dtEnd = $dtEnd;
    }

	public function setDtStart($dtStart)
	{
		$this->dtStart = $dtStart;
	}

	public function setDtStamp($dtStamp)
	{
		$this->dtStamp = $dtStamp;
	}

	public function setAttendee($attendee)
	{
		$this->attendee = $attendee;
	}

	public function addFreeBusyTime($fbType, \DateTime $dtFrom, \DateTime $dtTo)
	{
		if ((!isset($this->dtStart)) || $dtFrom < $this->dtStart) {
			$this->dtStart = $dtFrom;
		}
		if ((!isset($this->dtEnd)) || $dtTo < $this->dtEnd) {
			$this->dtEnd = $dtTo;
		}

		$strFrom = $this->getDateString($dtFrom, false);
		$strTo = $this->getDateString($dtTo, false);

		$fbvalue = $strFrom . "/" . $strTo;

		// store by fbvalue as key so we can sort into correct order before publishing!
		$this->freeBusyTimes[$fbvalue] = array($fbType, $fbvalue);
	}

	public function setOrganizer($organizer)
	{
		$this->organizer = $organizer;
	}

    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setUseTimezone($useTimezone)
    {
        $this->useTimezone = $useTimezone;
    }

    public function getUseTimezone()
    {
        return $this->useTimezone;
    }

}
