<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC2
 */

global $db_prefix, $db_package_log;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('ELK'))
{
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as ElkArte\'s index.php.');
}

$db = database();
$db_table = db_table();

// Define and create (if needed) all the portal tables
$sp_tables = defineTables();

// See if any blocks are already defined, like would be found during an upgrade
$has_blocks = checkForBlocks();
if ($has_blocks)
{
	// Rename the "old" (pre-classes) to the "new" format introduced in B2
	updateBlocks();
}
else
{
	// No blocks yet, since this is a new install, lets set some defaults
	addDefaultBlocks();
}

// Have any permission profiles been added yet, or is this an upgrade
$has_permission_profiles = checkForPermissionProfiles();
if (!$has_permission_profiles)
{
	addDefaultPermissions();
}

// Have any style profiles been added yet, or is this an upgrade
$has_style_profiles = checkForStyleProfiles();
if (!$has_style_profiles)
{
	addDefaultStyles();
}

// Have any visibility profiles been added yet, or is this an upgrade
$has_visibility_profiles = checkForVisibilityProfiles();
if (!$has_visibility_profiles)
{
	addDefaultVisibility();
}

// Make any upgrade changes as required
updateTableStructures();
updateStyleProfiles($has_style_profiles);
updateVisibilityProfiles($has_visibility_profiles);

$db_package_log = $db_table->package_log();

if (ELK === 'SSI')
{
	echo 'Database changes were carried out successfully.';
}

/**
 * Adds a few default visibility profiles for use
 */
function addDefaultVisibility()
{
	$db = database();

	$db->insert('replace',
		'{db_prefix}sp_profiles',
		array('id_profile' => 'int', 'type' => 'int', 'name' => 'text', 'value' => 'text'),
		array(
			array(14, 3, '$_show_on_portal', 'portal|'),
			array(15, 3, '$_show_on_board_index', 'forum|'),
			array(16, 3, '$_show_on_all_actions', '|allaction'),
			array(17, 3, '$_show_on_all_boards', '|allboard'),
			array(18, 3, '$_show_on_all_pages', '|allpage'),
			array(19, 3, '$_show_on_all_categories', '|allcategory'),
			array(20, 3, '$_show_on_all_articles', '|allarticle'),
			array(21, 3, '$_show_everywhere', '|all|1'),
		),
		array('id_profile')
	);
}

/**
 * Adds a few default style profiles for use
 */
function addDefaultStyles()
{
	$db = database();

	$db->insert('replace',
		'{db_prefix}sp_profiles',
		array('id_profile' => 'int', 'type' => 'int', 'name' => 'text', 'value' => 'text'),
		array(
			array(4, 2, '$_default_title_default_body', 'title_default_class~category_header|title_custom_class~|title_custom_style~|body_default_class~portalbg|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(5, 2, '$_default_title_alternate_body', 'title_default_class~category_header|title_custom_class~|title_custom_style~|body_default_class~portalbg2|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(6, 2, '$_alternate_title_default_body', 'title_default_class~secondary_header|title_custom_class~|title_custom_style~|body_default_class~portalbg|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(7, 2, '$_alternate_title_alternate_body', 'title_default_class~secondary_header|title_custom_class~|title_custom_style~|body_default_class~portalbg2|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(8, 2, '$_no_title_default_body', 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~portalbg|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
			array(9, 2, '$_no_title_alternate_body', 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~portalbg2|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
			array(10, 2, '$_default_title_roundframe', 'title_default_class~category_header|title_custom_class~|title_custom_style~|body_default_class~roundframe|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(11, 2, '$_alternate_title_roundframe', 'title_default_class~secondary_header|title_custom_class~|title_custom_style~|body_default_class~roundframe|body_custom_class~|body_custom_style~|no_title~|no_body~'),
			array(12, 2, '$_no_title_roundframe', 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~roundframe|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
			array(13, 2, '$_no_title_information', 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~information|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
		),
		array('id_profile')
	);
}

/**
 * Adds guest only, member ony and everyone permission profiles
 */
function addDefaultPermissions()
{
	$db = database();

	$request = $db->query('', '
		SELECT
			id_group
		FROM {db_prefix}membergroups
		WHERE min_posts != {int:min_posts}',
		array(
			'min_posts' => -1,
		)
	);
	$post_groups = array();
	while ($row = $db->fetch_assoc($request))
	{
		$post_groups[] = $row['id_group'];
	}
	$db->free_result($request);

	$db->insert('replace',
		'{db_prefix}sp_profiles',
		array('id_profile' => 'int', 'type' => 'int', 'name' => 'text', 'value' => 'text'),
		array(
			array(1, 1, '$_guests', '-1|'),
			array(2, 1, '$_members', implode(',', $post_groups) . ',0|'),
			array(3, 1, '$_everyone', implode(',', $post_groups) . ',0,-1|'),
		),
		array('id_profile')
	);
}

/**
 * Makes any table structure updates required from previous releases
 */
function updateTableStructures()
{
	$db_table = db_table();

	// Update the page table to accept more data
	$page_cols = $db_table->db_list_columns('{db_prefix}sp_pages', true);
	if (isset($page_cols['body']) && $page_cols['body']['type'] === 'text')
	{
		$db_table->db_change_column('{db_prefix}sp_pages', 'body', array('type' => 'mediumtext'));
	}
}

/**
 * Update sp_blocks to use visibility profiles in place of display and mobile view values
 *
 * @param bool $has_visibility_profiles;
 */
function updateVisibilityProfiles($has_visibility_profiles)
{
	$db_table = db_table();
	$db = database();

	$block_cols = $db_table->db_list_columns('{db_prefix}sp_blocks', true);
	if (!isset($block_cols['visibility']))
	{
		// Add visibility profile col to sp_blocks
		$db_table->db_add_column('{db_prefix}sp_blocks', array('name' => 'visibility', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true));

		// Read in the "old" data
		$result = $db->query('', '
			SELECT
				id_block, ' . (isset($block_cols['mobile_view']) ? 'mobile_view, ' : '') . ' display, display_custom
			FROM {db_prefix}sp_blocks',
			array()
		);
		$updates = array();
		$all_action = array('allaction' => 'allaction', 'allboard' => 'allboard', 'allpage' => 'allpages', 'all' => 'all');
		while ($row = $db->fetch_assoc($result))
		{
			$selections = array();
			$query = array();

			// Old Mobile and Custom to new is easy
			$mobile_view = !empty($row['mobile_view']) ? 1 : 0;
			if (!empty($row['display_custom']))
				$query[] = $row['display_custom'];

			// The rest depends on if its a selection or query
			$rest = explode(',', $row['display']);
			foreach ($rest as $sel_or_query)
			{
				if ($sel_or_query === 'sportal')
				{
					$selections[] = 'portal';
				}
				elseif ($sel_or_query === 'sforum')
				{
					$selections[] = 'forum';
				}
				elseif (in_array($sel_or_query, $all_action))
				{
					$query[] = $all_action[$sel_or_query];
				}
				else
				{
					$selections[] = $sel_or_query;
				}
			}

			// Build the visibility value that represents this row
			$updates[$row['id_block']] = (!empty($selections) ? implode(',', $selections) : '') . '|' . (!empty($query) ? implode(',', $query) : '') . (!empty($mobile_view) ? '|' . $mobile_view : '');
			if (empty($updates[$row['id_block']]) || $updates[$row['id_block']] === '|')
			{
				$updates[$row['id_block']] = 'portal|';
			}
		}
		$db->free_result($result);

		// Now check if each update already exists as a profile, if not, then add it and then update the block that used it !
		foreach ($updates as $id => $visibility)
		{
			// Find the old visibility value in the profile table
			$result = $db->query('', '
				SELECT
					id_profile
				FROM {db_prefix}sp_profiles
				WHERE value = {string:value}
					AND type = {int:type}
				LIMIT 1',
				array(
					'value' => $visibility,
					'type' => 3
				)
			);
			$visibility_profile = '';
			if ($db->num_rows($result) !== 0)
			{
				list ($visibility_profile) = $db->fetch_row($result);
				$db->free_result($result);
			}

			if (empty($visibility_profile))
			{
				// Not existing, so we add it and get the new id.
				$db->insert('ignore',
					'{db_prefix}sp_profiles',
					array('type' => 'int', 'name' => 'text', 'value' => 'text'),
					array(
						array(3, '$_converted_' . $id, $visibility),
					),
					array('id_profile')
				);
				$visibility_profile = $db->insert_id('{db_prefix}sp_profiles', 'id_profile');
			}

			// Update the block to use this visibility profile
			{
				$db->query('', '
					UPDATE {db_prefix}sp_blocks
					SET visibility = {string:visibility}
					WHERE id_block = {int:id}',
					array(
						'visibility' => $visibility_profile,
						'id' => $id
					)
				);
			}
		}

		// No need for the old columns now
		$db_table->db_remove_column('{db_prefix}sp_blocks', 'display');
		$db_table->db_remove_column('{db_prefix}sp_blocks', 'display_custom');
		$db_table->db_remove_column('{db_prefix}sp_blocks', 'mobile_view');
	}
}

/**
 * Update tables to use styles profiles id's in place of old style text
 *
 * @param bool $has_style_profiles
 */
function updateStyleProfiles($has_style_profiles)
{
	$db_table = db_table();
	$db = database();

	// Update tables to use styles (profiles) in place of style
	foreach (array('id_article' => 'sp_articles', 'id_block' => 'sp_blocks', 'id_page' => 'sp_pages') as $key => $sp_table)
	{
		$block_cols = $db_table->db_list_columns('{db_prefix}' . $sp_table, true);
		if (!isset($block_cols['styles']))
		{
			// Add the new styles column, it will replace the old style column
			$db_table->db_add_column('{db_prefix}' . $sp_table, array('name' => 'styles', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true));

			// Find the old style value in the profile table that matches
			$result = $db->query('', '
				SELECT
					sp.id_profile, old.' . $key . '
				FROM {db_prefix}sp_profiles AS sp
					INNER JOIN {db_prefix}' . $sp_table . ' AS old ON (sp.value = old.style)
				WHERE sp.type = {int:type}',
				array(
					'type' => 2,
					'db_error_skip' => true,
				)
			);
			$updates = array();
			if ($result)
			{
				while ($row = $db->fetch_assoc($result))
				{
					$updates[$row[$key]] = $row['id_profile'];
				}
				$db->free_result($result);
			}

			// Add the styles profile id for any old style that matched new ones
			foreach ($updates as $id => $style)
			{
				$db->query('', '
					UPDATE {db_prefix}' . $sp_table . '
					SET styles = {int:style}
					WHERE ' . $key . '={int:id}',
					array(
						'style' => $style,
						'id' => $id
					)
				);
			}

			// Set any blank ones to the default.
			$db->query('', '
				UPDATE {db_prefix}' . $sp_table . '
				SET styles = {int:style}
				WHERE styles = {int:id}',
				array(
					'style' => !empty($has_style_profiles) ? $has_style_profiles : 4,
					'id' => 0
				)
			);

			// No need for the old column now
			$db_table->db_remove_column('{db_prefix}' . $sp_table, 'style');
		}
	}
}

/**
 * Checks if any Visibility profiles already exist in the system
 *
 * @return bool
 */
function checkForVisibilityProfiles()
{
	$db = database();

	// Do visibility profiles exist?
	$result = $db->query('', '
		SELECT
			id_profile
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		LIMIT {int:limit}',
		array(
			'type' => 3,
			'limit' => 1,
		)
	);
	$has_visibility_profiles = '';
	if ($db->num_rows($result) !== 0)
	{
		list ($has_visibility_profiles) = $db->fetch_row($result);
		$db->free_result($result);
	}

	return !empty($has_visibility_profiles);
}

/**
 * Checks if any Style profiles already exist in the system
 *
 * @return bool
 */
function checkForStyleProfiles()
{
	$db = database();

	// Do style profiles exist already?
	$result = $db->query('', '
		SELECT
			id_profile
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		LIMIT {int:limit}',
		array(
			'type' => 2,
			'limit' => 1,
		)
	);
	$has_style_profiles = '';
	if ($db->num_rows($result) !== 0)
	{
		list ($has_style_profiles) = $db->fetch_row($result);
		$db->free_result($result);
	}

	return !empty($has_style_profiles);
}

/**
 * Checks if any Permission profiles already exist in the system
 *
 * @return bool
 */
function checkForPermissionProfiles()
{
	$db = database();

	$result = $db->query('', '
		SELECT 
			id_profile
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		LIMIT {int:limit}',
		array(
			'type' => 1,
			'limit' => 1,
		)
	);
	$has_permission_profiles = '';
	if ($db->num_rows($result) !== 0)
	{
		list ($has_permission_profiles) = $db->fetch_row($result);
		$db->free_result($result);
	}

	return !empty($has_permission_profiles);
}

/**
 * Checks if there are any blocks in the system
 *
 * @return bool
 */
function checkForBlocks()
{
	$db = database();

	$result = $db->query('', '
		SELECT 
			id_block
		FROM {db_prefix}sp_blocks
		LIMIT 1',
		array()
	);
	$has_blocks = '';
	if ($db->num_rows($result) !== 0)
	{
		list ($has_blocks) = $db->fetch_row($result);
		$db->free_result($result);
	}

	return !empty($has_blocks);
}

/**
 * Beta 2 introduced blocks as classes, so a little renaming is needed
 * From the "old" (pre-classes) to the "new" (blocks as classes) format
 */
function updateBlocks()
{
	$db = database();

	$replace_array = array(
		'sp_userInfo' => 'User_Info',
		'sp_latestMember' => 'Latest_Member',
		'sp_whosOnline' => 'Whos_Online',
		'sp_boardStats' => 'Board_Stats',
		'sp_topPoster' => 'Top_Poster',
		'sp_topStatsMember' => 'Top_Stats_Member',
		'sp_recent' => 'Recent',
		'sp_topTopics' => 'Top_Topics',
		'sp_topBoards' => 'Top_Boards',
		'sp_showPoll' => 'Show_Poll',
		'sp_boardNews' => 'Board_News',
		'sp_quickSearch' => 'Quick_Search',
		'sp_news' => 'News',
		'sp_attachmentImage' => 'Attachment_Image',
		'sp_attachmentRecent' => 'Attachment_Recent',
		'sp_calendar' => 'Calendar',
		'sp_calendarInformation' => 'Calendar_Information',
		'sp_rssFeed' => 'Rss_Feed',
		'sp_theme_select' => 'Theme_Select',
		'sp_staff' => 'Staff',
		'sp_articles' => 'Articles',
		'sp_shoutbox' => 'Shoutbox',
		'sp_gallery' => 'Gallery',
		'sp_menu' => 'Menu',
		'sp_bbc' => 'Bbc',
		'sp_html' => 'Html',
		'sp_php' => 'Php',
	);

	foreach ($replace_array as $from => $to)
	{
		$db->query('', '
			UPDATE {db_prefix}sp_blocks
			SET type = REPLACE(type, {string:from}, {string:to})
			WHERE type LIKE {string:from}',
			array(
				'from' => $from,
				'to' => $to,
			)
		);
	}
}

/**
 * Defines and creates all of the tables that make the portal tick
 */
function defineTables()
{
	$sp_tables = array(
		'sp_articles' => array(
			'columns' => array(
				array('name' => 'id_article', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'id_category', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'member_name', 'type' => 'varchar', 'size' => 80, 'default' => ''),
				array('name' => 'namespace', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'title', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'body', 'type' => 'text'),
				array('name' => 'type', 'type' => 'varchar', 'size' => 40, 'default' => ''),
				array('name' => 'date', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'permissions', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'styles', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'views', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'comments', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'status', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_article')),
			),
		),
		'sp_blocks' => array(
			'columns' => array(
				array('name' => 'id_block', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'label', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'type', 'type' => 'varchar', 'size' => 40, 'default' => ''),
				array('name' => 'col', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
				array('name' => 'row', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
				array('name' => 'permissions', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'styles', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'visibility', 'type' => 'mediumint', 'size' => 8, 'default' => 0),
				array('name' => 'state', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
				array('name' => 'force_view', 'type' => 'tinyint', 'size' => 2, 'default' => 0),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_block')),
				array('type' => 'index', 'columns' => array('state')),
			),
		),
		'sp_categories' => array(
			'columns' => array(
				array('name' => 'id_category', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'namespace', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'description', 'type' => 'text'),
				array('name' => 'permissions', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'articles', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'status', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_category')),
			),
		),
		'sp_comments' => array(
			'columns' => array(
				array('name' => 'id_comment', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'id_article', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'member_name', 'type' => 'varchar', 'size' => 80, 'default' => ''),
				array('name' => 'body', 'type' => 'text'),
				array('name' => 'log_time', 'type' => 'int', 'size' => 10, 'default' => 0),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_comment')),
			),
		),
		'sp_custom_menus' => array(
			'columns' => array(
				array('name' => 'id_menu', 'type' => 'mediumint', 'size' => 8, 'auto' => true),
				array('name' => 'name', 'type' => 'tinytext'),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_menu')),
			),
		),
		'sp_functions' => array(
			'columns' => array(
				array('name' => 'id_function', 'type' => 'tinyint', 'size' => 4, 'auto' => true, 'unsigned' => true),
				array('name' => 'function_order', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
				array('name' => 'name', 'type' => 'varchar', 'size' => 40, 'default' => ''),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_function')),
			),
		),
		'sp_menu_items' => array(
			'columns' => array(
				array('name' => 'id_item', 'type' => 'mediumint', 'size' => 8, 'auto' => true),
				array('name' => 'id_menu', 'type' => 'mediumint', 'size' => 8, 'default' => 0),
				array('name' => 'namespace', 'type' => 'tinytext'),
				array('name' => 'title', 'type' => 'tinytext'),
				array('name' => 'href', 'type' => 'tinytext'),
				array('name' => 'target', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_item')),
			),
		),
		'sp_pages' => array(
			'columns' => array(
				array('name' => 'id_page', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'namespace', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'title', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'body', 'type' => 'mediumtext'),
				array('name' => 'type', 'type' => 'varchar', 'size' => 40, 'default' => ''),
				array('name' => 'permissions', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'styles', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'views', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'status', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_page')),
			),
		),
		'sp_parameters' => array(
			'columns' => array(
				array('name' => 'id_block', 'type' => 'mediumint', 'size' => 9, 'default' => 0, 'unsigned' => true),
				array('name' => 'variable', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'value', 'type' => 'text'),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_block', 'variable')),
				array('type' => 'key', 'columns' => array('variable')),
			),
		),
		'sp_profiles' => array(
			'columns' => array(
				array('name' => 'id_profile', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'type', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
				array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'value', 'type' => 'text'),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_profile')),
			),
		),
		'sp_shoutboxes' => array(
			'columns' => array(
				array('name' => 'id_shoutbox', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'permissions', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'moderator_groups', 'type' => 'text'),
				array('name' => 'warning', 'type' => 'text'),
				array('name' => 'allowed_bbc', 'type' => 'text'),
				array('name' => 'height', 'type' => 'smallint', 'size' => 5, 'default' => 200, 'unsigned' => true),
				array('name' => 'num_show', 'type' => 'smallint', 'size' => 5, 'default' => 20, 'unsigned' => true),
				array('name' => 'num_max', 'type' => 'mediumint', 'size' => 8, 'default' => 1000, 'unsigned' => true),
				array('name' => 'refresh', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
				array('name' => 'reverse', 'type' => 'tinyint', 'size' => 4, 'default' => 0),
				array('name' => 'caching', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
				array('name' => 'status', 'type' => 'tinyint', 'size' => 4, 'default' => 1),
				array('name' => 'num_shouts', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'last_update', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_shoutbox')),
			),
		),
		'sp_shouts' => array(
			'columns' => array(
				array('name' => 'id_shout', 'type' => 'mediumint', 'size' => 8, 'auto' => true, 'unsigned' => true),
				array('name' => 'id_shoutbox', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'member_name', 'type' => 'varchar', 'size' => 80, 'default' => ''),
				array('name' => 'log_time', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'body', 'type' => 'text'),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_shout')),
			),
		),
		'sp_attachments' => array(
			'columns' => array(
				array('name' => 'id_attach', 'type' => 'int', 'size' => 10, 'auto' => true, 'unsigned' => true),
				array('name' => 'id_thumb', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'id_article', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'attachment_type', 'type' => 'tinyint', 'size' => 3, 'default' => 0, 'unsigned' => true),
				array('name' => 'filename', 'type' => 'varchar', 'size' => 255, 'default' => ''),
				array('name' => 'file_hash', 'type' => 'varchar', 'size' => 40, 'default' => ''),
				array('name' => 'fileext', 'type' => 'varchar', 'size' => 8, 'default' => ''),
				array('name' => 'size', 'type' => 'int', 'size' => 10, 'default' => 0, 'unsigned' => true),
				array('name' => 'width', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'height', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true),
				array('name' => 'mime_type', 'type' => 'varchar', 'size' => 20, 'default' => ''),
			),
			'indexes' => array(
				array('type' => 'primary', 'columns' => array('id_attach')),
				array('type' => 'index', 'columns' => array('id_member', 'id_attach')),
				array('type' => 'key', 'columns' => array('id_article')),
				array('type' => 'key', 'columns' => array('attachment_type')),
				array('type' => 'key', 'columns' => array('id_thumb')),
			),
		),
	);

	$db_table = db_table();

	// Create the tables, if they don't already exist
	foreach ($sp_tables as $sp_table => $data)
	{
		$db_table->db_create_table('{db_prefix}' . $sp_table, $data['columns'], $data['indexes'], array(), 'ignore');
	}

	return $sp_tables;
}

/**
 * Adds several default blocks to a new installation
 */
function addDefaultBlocks()
{
	$db = database();

	$welcome_text = '<h2 style="text-align: center;">Welcome to SimplePortal!</h2>
	<p>Although always developing, SimplePortal is produced with the user in mind first. User feedback is the number one method of 
	growth, and our users are always finding ways for SimplePortal to improve and grow. It has added numerous user-requested 
	features such as articles, block types and the ability to completely customize the portal page.</p>
	<p>All this and SimplePortal has remained Simple! SimplePortal is built for simplicity and ease of use; ensuring the average 
	forum administrator can install SimplePortal, configure a few settings, and show off the brand new portal to the users in 
	minutes. Confusing menus, undesired pre-loaded blocks and settings that cannot be found are all avoided as much as possible. 
	Because when it comes down to it, SimplePortal is YOUR portal, and should reflect your taste as much as possible.</p>';

	$default_blocks = array(
		'user_info' => array('label' => 'User Info', 'type' => 'User_Info', 'col' => 1, 'row' => 1, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'whos_online' => array('label' => 'Who&#039;s Online', 'type' => 'Whos_Online', 'col' => 1, 'row' => 2, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'board_stats' => array('label' => 'Board Stats', 'type' => 'Board_Stats', 'col' => 1, 'row' => 3, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'theme_select' => array('label' => 'Theme Select', 'type' => 'Theme_Select', 'col' => 1, 'row' => 4, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'search' => array('label' => 'Search', 'type' => 'Quick_Search', 'col' => 1, 'row' => 5, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'news' => array('label' => 'News', 'type' => 'News', 'col' => 2, 'row' => 1, 'permissions' => 3, 'styles' => 8, 'visibility' => 14),
		'welcome' => array('label' => 'Welcome', 'type' => 'Html', 'col' => 2, 'row' => 2, 'permissions' => 3, 'styles' => 8, 'visibility' => 14),
		'board_news' => array('label' => 'Board News', 'type' => 'Board_News', 'col' => 2, 'row' => 3, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'recent_topics' => array('label' => 'Recent Topics', 'type' => 'Recent', 'col' => 3, 'row' => 1, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'top_poster' => array('label' => 'Top Poster', 'type' => 'Top_Poster', 'col' => 4, 'row' => 1, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'recent_posts' => array('label' => 'Recent Posts', 'type' => 'Recent', 'col' => 4, 'row' => 2, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'staff' => array('label' => 'Forum Staff', 'type' => 'Staff', 'col' => 4, 'row' => 3, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'calendar' => array('label' => 'Calendar', 'type' => 'Calendar', 'col' => 4, 'row' => 4, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
		'top_boards' => array('label' => 'Top Boards', 'type' => 'Top_Boards', 'col' => 4, 'row' => 5, 'permissions' => 3, 'styles' => 4, 'visibility' => 14),
	);

	// Add the default blocks to the system
	$db->insert('ignore',
		'{db_prefix}sp_blocks',
		array('label' => 'text', 'type' => 'text', 'col' => 'int', 'row' => 'int', 'permissions' => 'int', 'styles' => 'int', 'visibility' => 'int'),
		$default_blocks,
		array('id_block', 'state')
	);

	// Set a few optional block parameters
	$request = $db->query('', '
		SELECT 
			MIN(id_block) AS id, type
		FROM {db_prefix}sp_blocks
		WHERE type IN ({array_string:types})
		GROUP BY type
		LIMIT 4',
		array(
			'types' => array('Html', 'Board_News', 'Calendar', 'Recent'),
		)
	);
	$block_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		$block_ids[$row['type']] = $row['id'];
	}
	$db->free_result($request);

	$default_parameters = array(
		array('id_block' => $block_ids['Html'], 'variable' => 'content', 'value' => htmlspecialchars($welcome_text)),
		array('id_block' => $block_ids['Board_News'], 'variable' => 'avatar', 'value' => 1),
		array('id_block' => $block_ids['Board_News'], 'variable' => 'per_page', 'value' => 3),
		array('id_block' => $block_ids['Calendar'], 'variable' => 'events', 'value' => 1),
		array('id_block' => $block_ids['Calendar'], 'variable' => 'birthdays', 'value' => 1),
		array('id_block' => $block_ids['Calendar'], 'variable' => 'holidays', 'value' => 1),
		array('id_block' => $block_ids['Recent'], 'variable' => 'type', 'value' => 1),
		array('id_block' => $block_ids['Recent'], 'variable' => 'display', 'value' => 1),
	);

	$db->insert('replace',
		'{db_prefix}sp_parameters',
		array('id_block' => 'int', 'variable' => 'text', 'value' => 'text'),
		$default_parameters,
		array('id_block', 'variable')
	);
}
