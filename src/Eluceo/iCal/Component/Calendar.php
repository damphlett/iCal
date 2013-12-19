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

class Calendar extends Component
{
    /**
     * The Product Identifier
     *
     * According to RFC 2445: 4.7.3 Product Identifier
     *
     * This property specifies the identifier for the product that created the Calendar object.
     *
     * @link http://www.ietf.org/rfc/rfc2445.txt
     *
     * @var string
     */
    protected $prodId = null;
    protected $method = null;
    protected $name = null;
	protected $method = 'PUBLISH';
	protected $errormsg = null;
	protected $success = null;

	protected $lastFreeBusySectionKey = null;
    protected $timezone = null;

    public function __construct($prodId)
    {
        if (empty($prodId)) {
            throw new \UnexpectedValueException('PRODID cannot be empty');
        }

        $this->prodId = $prodId;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'VCALENDAR';
    }

	public function setErrorMsg( $message )
	{
		$this->errormsg = $message;
	}

	public function setSuccess($success = true)
	{
		$this->success = $success;
	}

	public function setFailure()
	{
		$this->success = false;
	}

	public function setName( $name )
	{
		$this->name = $name;
	}

	public function setMethod( $method )
	{
		$this->method = $method;
	}

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPropertyBag()
    {
        $this->properties = new PropertyBag;
        $this->properties->set('VERSION', '2.0');
        $this->properties->set('PRODID', $this->prodId);

        if ($this->method) {
            $this->properties->set('METHOD', $this->method);
        }

    	if ($this->errormsg) {
    		$this->properties->set( 'X-ERRORMSG', $this->errormsg );
    	}

    	if (isset($this->success)) {
    		$this->properties->set( 'X-SUCCESS', $this->success );
    	}

        if ($this->name) {
            $this->properties->set('X-WR-CALNAME', $this->name);
        }

        if ($this->timezone) {
            $this->properties->set('X-WR-TIMEZONE', $this->timezone);
            $this->addComponent(new Timezone($this->timezone));
        }
    }

	/**
	 * Adds an Event to the Calendar
	 *
	 * Wrapper for addComponent()
	 *
	 * @see Eluceo\iCal::addComponent
	 *
	 * @param Event $event
	 */
	public function addEvent(Event $event)
	{
		$this->addComponent($event);
	}

	/**
	 * Adds a FreeBusy Section to the Calendar
	 *
	 * Wrapper for addComponent()
	 *
	 * @see Eluceo\iCal::addComponent
	 *
	 * @param FreeBusy $freeBusySection
	 * @param string $key
	 */
	public function addFreeBusySection(FreeBusy $freeBusySection, $key)
	{
		$this->addComponent($freeBusySection, $key);
		$this->lastFreeBusySectionKey = $key;
	}

	public function addFreeBusyTime($fbType, \DateTime $dtFrom, \DateTime $dtTo, $freeBusySectionKey = null)
	{
		if ($freeBusySectionKey === null) {
			$freeBusySectionKey = $this->lastFreeBusySectionKey;
		}
		$freeBusySection = $this->getComponentByKey($freeBusySectionKey);
		if ($freeBusySection !== null) {
			$freeBusySection->addFreeBusyTime($fbType, $dtFrom, $dtTo);
		}
	}
/**
     * Not needed here.
     *
     * @todo Remove this method
     *
     * @deprecated
     *
     * @return null|string
     */
    public function getProdId()
    {
        return $this->prodId;
    }
}
