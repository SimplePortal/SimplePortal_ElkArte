<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.2
 */


/**
 * Board Stats block, shows count of users online names
 *
 * @param array $parameters
 *		'averages' => Will calculate the daily average (posts, topics, registrations, etc)
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Board_Stats_Block extends SP_Abstract_Block
{
	protected $total_days_up = 0;

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'averages' => 'check',
			'refresh_value' => 'int'
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param array $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $user_info;

		$this->data['averages'] = !empty($parameters['averages']);

		loadLanguage('Stats');

		// Basic totals are easy
		$stats = ssi_boardStats('array');
		$this->data['totals'] = $stats === false ? [] : $stats;
		$this->data['totals'] += array('mostOnline' => empty($this->_modSettings['mostOnline']) ? 0 : $this->_modSettings['mostOnline']);

		// Get the averages from the activity log, its the most recent snapshot
		if ($this->data['averages'])
		{
			require_once(SUBSDIR . '/Stats.subs.php');

			// All the average data for the system
			$averages = getAverages();

			// The number of days the forum has been up...
			$this->total_days_up = ceil((time() - strtotime($averages['date'])) / (60 * 60 * 24));

			$this->data['totals'] += array(
				'average_members' => $this->formatAvg($averages['registers']),
				'average_posts' => $this->formatAvg($averages['posts']),
				'average_topics' => $this->formatAvg($averages['topics']),
				'average_online' => $this->formatAvg($averages['most_on']),
			);
		}

		// Set the template to use
		$this->setTemplate('template_sp_boardStats');

		// Enabling auto refresh?
		if (!empty($parameters['refresh_value']) && !$user_info['is_guest'])
		{
			$this->refresh = array('sa' => 'boardstats', 'class' => '.sp_board_stats', 'id' => $id, 'refresh_value' => $parameters['refresh_value']);
			$this->auto_refresh();
		}
	}

	/**
	 * Make the averages look normal 1,222.xx
	 *
	 * @param $value
	 *
	 * @return float|null|string
	 */
	protected function formatAvg($value)
	{
		if (empty($this->total_days_up))
			return null;

		return comma_format(round($value / $this->total_days_up, 2));
	}
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_boardStats($data)
{
	global $txt, $scripturl;

	echo '
		<ul class="sp_list">';

	if (!isset($data['totals']['members']))
	{
		echo '<li>' . $txt['error_sp_no_stats_found'] . '</li>
		</ul>';

		return;
	}

	echo '
			<li ', sp_embed_class('portalstats'), '>', $txt['total_members'], ': <a href="', $scripturl . '?action=memberlist">', comma_format($data['totals']['members']), '</a></li>
			<li ', sp_embed_class('portalstats'), '>', $txt['total_posts'], ': <a href="', $scripturl . '?action=stats">', comma_format($data['totals']['posts']), '</a></li>
			<li ', sp_embed_class('portalstats'), '>', $txt['total_topics'], ': ', comma_format($data['totals']['topics']), '</li>
			<li ', sp_embed_class('portalstats'), '>', $txt['total_cats'], ': ', comma_format($data['totals']['categories']), '</li>
			<li ', sp_embed_class('portalstats'), '>', $txt['total_boards'], ': ', comma_format($data['totals']['boards']), '</li>
			<li ', sp_embed_class('portalstats'), '>', $txt['most_online'], ': ', comma_format($data['totals']['mostOnline']), '</li>
		</ul>';

	// And the averages if required
	if ($data['averages'])
	{
		echo '
		<hr />
		<ul class="sp_list">
			<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_posts'], ': ', $data['totals']['average_posts'], '</li>
			<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_topics'], ': ', $data['totals']['average_topics'], '</li>
			<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_members'], ': ', $data['totals']['average_members'], '</li>
			<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_online'], ': ', $data['totals']['average_online'], '</li>
		</ul>';
	}
}
