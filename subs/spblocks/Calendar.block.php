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
 * Calendar Block, Displays a full calendar block
 *
 * @param mixed[] $parameters
 *        'events' => show events
 *        'birthdays' => show birthdays
 *        'holidays' => show holidays
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Calendar_Block extends SP_Abstract_Block
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
		global $modSettings, $options, $scripturl, $txt;

		require_once(SUBSDIR . '/Calendar.subs.php');

		// Fetch the calendar data for today
		$today = getTodayInfo();

		$this->data['curPage'] = array(
			'day' => $today['day'],
			'month' => $today['month'],
			'year' => $today['year']
		);

		$calendarOptions = array(
			'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
			'show_week_num' => false,
			'show_events' => !empty($parameters['events']),
			'show_birthdays' => !empty($parameters['birthdays']),
			'show_holidays' => !empty($parameters['holidays']),
		);

		// Check cache or fetch
		if (($this->data['calendar'] = cache_get_data('sp_calendar_data', 360)) === null)
		{
			$this->data['calendar'] = getCalendarGrid($this->data['curPage']['month'], $this->data['curPage']['year'], $calendarOptions);
			cache_put_data('sp_calendar_data', $this->data['calendar'], 360);
		}

		$title_text = $txt['months_titles'][$this->data['calendar']['current_month']] . ' ' . $this->data['calendar']['current_year'];

		if (!empty($modSettings['cal_enabled']))
		{
			$this->data['calendar_title'] = '<a href="' . $scripturl . '?action=calendar;year=' . $this->data['calendar']['current_year'] . ';month=' . $this->data['calendar']['current_month'] . '">' . $title_text . '</a>';
		}
		else
		{
			$this->data['calendar_title'] = $title_text;
		}
	}
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_calendar($data)
{
	global $scripturl, $txt;

	// Output the calendar block
	echo '
		<table class="sp_acalendar smalltext">
			<tr>
				<td class="centertext" colspan="7">
					', $data['calendar_title'], '
				</td>
			</tr>
			<tr>';

	foreach ($data['calendar']['week_days'] as $day)
	{
		echo '
				<td class="centertext">', $txt['days_short'][$day], '</td>';
	}

	echo '
			</tr>';

	foreach ($data['calendar']['weeks'] as $week_key => $week)
	{
		echo '
			<tr>';

		foreach ($week['days'] as $day_key => $day)
		{
			echo '
				<td class="sp_acalendar_day">';

			if (empty($day['day']))
			{
				unset($data['calendar']['weeks'][$week_key]['days'][$day_key]);
			}
			else
			{
				if (!empty($day['holidays']) || !empty($day['birthdays']) || !empty($day['events']))
				{
					echo '
					<a href="#day" onclick="return sp_collapseCalendar(\'', $day['day'], '\');"><strong>', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</strong></a>';
				}
				else
				{
					echo '
					<a href="#day" onclick="return sp_collapseCalendar(\'0\');">', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</a>';
				}
			}

			echo '
				</td>';
		}

		echo '
			</tr>';
	}

	echo '
		</table>
		<hr class="sp_acalendar_divider" />';

	foreach ($data['calendar']['weeks'] as $week)
	{
		foreach ($week['days'] as $day)
		{
			if (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']) && !$day['is_today'])
			{
				continue;
			}
			elseif (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']))
			{
				echo '
		<div class="centertext smalltext" id="sp_calendar_', $day['day'], '">', $txt['error_sp_no_items_day'], '</div>';

				continue;
			}

			echo '
		<ul class="sp_list smalltext" id="sp_calendar_', $day['day'], '" ', !$day['is_today'] ? ' style="display: none;"' : '', '>';

			if (!empty($day['holidays']))
			{
				echo '
			<li class="centertext">
				<strong>- ', $txt['sp_calendar_holidays'], ' -</strong>
 			</li>';

				foreach ($day['holidays'] as $holiday)
				{
					echo '
			<li ', sp_embed_class('holiday', '', 'sp_list_indent'), '>', $holiday, '</li>';
				}
			}

			if (!empty($day['birthdays']))
			{
				echo '
			<li class="centertext"><strong>- ', $txt['sp_calendar_birthdays'], ' -</strong></li>';

				foreach ($day['birthdays'] as $member)
				{
					echo '
			<li ', sp_embed_class('birthday', '', 'sp_list_indent'), '>
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>
			</li>';
				}
			}

			if (!empty($day['events']))
			{
				echo '
			<li class="centertext"><strong>- ', $txt['sp_calendar_events'], ' -</strong></li>';

				foreach ($day['events'] as $event)
				{
					echo '
			<li ', sp_embed_class('event', '', 'sp_list_indent'), '>', $event['link'], '</li>';
				}
			}

			echo '
		</ul>';
		}
	}

	echo '
		<div class="centertext smalltext" id="sp_calendar_0" style="display: none;">', $txt['error_sp_no_items_day'], '</div>';

	addInlineJavascript('var current_day = "sp_calendar_' . $data['curPage']['day'] . '";', true);
}