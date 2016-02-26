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
 * Poll Block, Shows a specific poll, the most recent or a random poll
 * If user can vote, provides for voting selection
 *
 * @param mixed[] $parameters
 *        'topic' => topic id of the poll
 *        'type' => 1 the most recently posted poll, 2 displays a random poll, null for specific topic
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Show_Poll_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'topic' => 'int',
			'type' => 'select',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes the block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $modSettings, $txt;

		// Basic block parameters
		$topic = !empty($parameters['topic']) ? $parameters['topic'] : null;
		$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;
		$boardsAllowed = boardsAllowedTo('poll_view');

		// Can't view polls on any board, let them know
		if (empty($boardsAllowed))
		{
			$this->setTemplate('template_sp_showPoll_error');

			loadLanguage('Errors');
			$this->data['error_msg'] = $txt['cannot_poll_view'];

			return;
		}

		if (!empty($type))
		{
			$request = $this->_db->query('', '
				SELECT
					t.id_topic
				FROM {db_prefix}polls AS p
					INNER JOIN {db_prefix}topics AS t ON (t.id_poll = p.id_poll' . ($modSettings['postmod_active'] ? ' AND t.approved = {int:is_approved}' : '') . ')
					INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE {query_wanna_see_board}
					AND p.voting_locked = {int:not_locked}' . (!in_array(0, $boardsAllowed) ? '
					AND b.id_board IN ({array_int:boards_allowed_list})' : '') . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
					AND b.id_board != {int:recycle_enable}' : '') . '
				ORDER BY {raw:type}
				LIMIT 1',
				array(
					'boards_allowed_list' => $boardsAllowed,
					'not_locked' => 0,
					'is_approved' => 1,
					'recycle_enable' => $modSettings['recycle_board'],
					'type' => $type == 1 ? 'p.id_poll DESC' : 'RAND()',
				)
			);
			list ($topic) = $this->_db->fetch_row($request);
			$this->_db->free_result($request);
		}

		if (empty($topic) || $topic < 0)
		{
			$this->setTemplate('template_sp_showPoll_error');

			loadLanguage('Errors');
			$this->data['error_msg'] = $txt['topic_doesnt_exist'];

			return;
		}

		$this->data['poll'] = ssi_showPoll($topic, 'array');
		$this->setTemplate('template_sp_showPoll');
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_showPoll_error($data)
{
	echo '
		', $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_showPoll($data)
{
	global $boardurl, $txt, $scripturl, $context;

	// Let them vote?
	if ($data['poll']['allow_vote'])
	{
		echo '
		<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="UTF-8">
			<ul class="sp_list">
				<li>
					<strong>', $data['poll']['question'], '</strong>
				</li>
				<li>', $data['poll']['allowed_warning'], '</li>';

		foreach ($data['poll']['options'] as $option)
		{
			echo '
				<li>
					<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label>
				</li>';
		}

		echo '
				<li class="centertext">
					<input type="submit" value="', $txt['poll_vote'], '" class="button_submit" />
				</li>
				<li class="centertext"
					<a href="', $scripturl, '?topic=', $data['poll']['topic'], '.0">', $txt['sp-pollViewTopic'], '</a>
				</li>
			</ul>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="poll" value="', $data['poll']['id'], '" />
		</form>';
	}
	// Or just view the results
	elseif ($data['poll']['allow_view_results'])
	{
		echo '
		<ul class="sp_list">
			<li>
				<strong>', $data['poll']['question'], '</strong>
			</li>';

		foreach ($data['poll']['options'] as $option)
		{
			echo '
			<li ', sp_embed_class('dot'), '> ', $option['option'], '</li>
			<li class="sp_list_indent"><strong>', $option['votes'], '</strong> (', $option['percent'], '%)</li>';
		}

		echo '
			<li>
				<strong>', $txt['poll_total_voters'], ': ', $data['poll']['total_votes'], '</strong>
			</li>
			<li class="centertext"><a href="', $scripturl, '?topic=', $data['poll']['topic'], '.0">', $txt['sp-pollViewTopic'], '</a></li>
		</ul>';
	}
	// Nothing is always an option
	else
	{
		echo '
		', $txt['poll_cannot_see'];
	}
}