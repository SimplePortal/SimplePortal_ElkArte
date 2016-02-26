<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Calendar Info Block, Displays basic calendar ... birthdays, events and holidays.
 *
 * @param mixed[] $parameters
 *        'events' => show events
 *        'future' => how many months out to look for items
 *        'birthdays' => show birthdays
 *        'holidays' => show holidays
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Calendar_Information_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'events' => 'check',
			'future' => 'int',
			'birthdays' => 'check',
			'holidays' => 'check',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $txt;

		$show_event = !empty($parameters['events']);
		$event_future = !empty($parameters['future']) ? (int) $parameters['future'] : 0;
		$event_future = abs($event_future);
		$show_birthday = !empty($parameters['birthdays']);
		$show_holiday = !empty($parameters['holidays']);
		$this->data['show_titles'] = false;

		// If they are not showing anything, not much to do !
		if (!$show_event && !$show_birthday && !$show_holiday)
		{
			$this->data['error_msg'] = $txt['sp_calendar_noEventsFound'];
			$this->setTemplate('template_sp_calendarInformation_error');

			return;
		}

		$now = forum_time();
		$today_date = date("Y-m-d", $now);
		$this->data['calendar'] = array(
			'todayEvents' => array(),
			'futureEvents' => array(),
			'todayBirthdays' => array(),
			'todayHolidays' => array()
		);

		// Load calendar events
		if ($show_event)
		{
			// Just today's events or looking forward a few days?
			if (!empty($event_future))
			{
				$event_future_date = date("Y-m-d", ($now + $event_future * 86400));
			}
			else
			{
				$event_future_date = $today_date;
			}

			// Load them in
			$events = sp_loadCalendarData('getEvents', $today_date, $event_future_date);
			ksort($events);

			$displayed = array();
			foreach ($events as $day => $day_events)
			{
				foreach ($day_events as $event_key => $event)
				{
					if (in_array($event['id'], $displayed))
					{
						unset($events[$day][$event_key]);
					}
					else
					{
						$displayed[] = $event['id'];
					}
				}
			}

			if (!empty($events[$today_date]))
			{
				$this->data['calendar']['todayEvents'] = $events[$today_date];
				unset($events[$today_date]);
			}

			if (!empty($events))
			{
				ksort($events);
				$this->data['calendar']['futureEvents'] = $events;
			}
		}

		// Load in today's birthdays
		if ($show_birthday)
		{
			$this->data['calendar']['todayBirthdays'] = current(sp_loadCalendarData('getBirthdays', $today_date));
			$this->data['show_titles'] = !empty($show_event) || !empty($show_holiday);
		}

		// Load in any holidays
		if ($show_holiday)
		{
			$this->data['calendar']['todayHolidays'] = current(sp_loadCalendarData('getHolidays', $today_date));
			$this->data['show_titles'] = !empty($show_event) || !empty($show_birthday);
		}

		// Done collecting information, show what we found
		if (empty($this->data['calendar']['todayEvents']) && empty($this->data['calendar']['futureEvents']) && empty($this->data['calendar']['todayBirthdays']) && empty($this->data['calendar']['todayHolidays']))
		{
			$this->data['error_msg'] = $txt['sp_calendar_noEventsFound'];
			$this->setTemplate('template_sp_calendarInformation_error');

			return;
		}
		$this->setTemplate('template_sp_calendarInformation');
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_calendarInformation_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_calendarInformation($data)
{
	global $txt, $scripturl;

	echo '
		<ul class="sp_list">';

	if (!empty($data['calendar']['todayHolidays']))
	{
		if ($data['show_titles'])
		{
			echo '
			<li>
				<strong>', $txt['sp_calendar_holidays'], '</strong>
			</li>';
		}

		foreach ($data['calendar']['todayHolidays'] as $holiday)
		{
			echo '
			<li ', sp_embed_class('holiday'), '>', $holiday, '</li>';
		}
	}

	if (!empty($data['calendar']['todayBirthdays']))
	{
		if ($data['show_titles'])
		{
			echo '
			<li>
				<strong>', $txt['sp_calendar_birthdays'], '</strong>
			</li>';
		}

		foreach ($data['calendar']['todayBirthdays'] as $member)
		{
			echo '
			<li ', sp_embed_class('birthday'), '><a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a></li>';
		}
	}

	if (!empty($data['calendar']['todayEvents']))
	{
		if ($data['show_titles'])
		{
			echo '
			<li>
				<strong>', $txt['sp_calendar_events'], '</strong>
			</li>';
		}

		foreach ($data['calendar']['todayEvents'] as $event)
		{
			echo '
			<li ', sp_embed_class('event'), '>', $event['link'], !$data['show_titles'] ? ' - ' . standardTime(forum_time(), '%d %b') : '', '</li>';
		}
	}

	if (!empty($data['calendar']['futureEvents']))
	{
		if ($data['show_titles'])
		{
			echo '
			<li>
				<strong>', $txt['sp_calendar_upcomingEvents'], '</strong>
			</li>';
		}

		foreach ($data['calendar']['futureEvents'] as $startdate => $events)
		{
			foreach ($events as $event)
			{
				echo '
			<li ', sp_embed_class('event'), '>', $event['link'], ' - ', timeformat(strtotime($startdate), '%d %b'), '</li>';
			}
		}
	}

	echo '
		</ul>';
}