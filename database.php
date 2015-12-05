<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('ELK'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as ELK\'s index.php.');

global $db_prefix, $db_package_log;

$db = database();
$db_table = db_table();

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
);

foreach ($sp_tables as $sp_table => $data)
	$db_table->db_create_table('{db_prefix}' . $sp_table, $data['columns'], $data['indexes'], array(), 'ignore');

// From the "old" (pre-classes) to the "new" (blocks as classes) format
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

$result = $db->query('', '
	SELECT id_block
	FROM {db_prefix}sp_blocks
	LIMIT 1',
	array(
	)
);
list ($has_block) = $db->fetch_row($result);
$db->free_result($result);

if (empty($has_block))
{
	$welcome_text = '<h2 style="text-align: center;">Welcome to SimplePortal!</h2>
<p>SimplePortal is one of several portal mods for Elkarte. Although always developing, SimplePortal is produced with the user in mind first. User feedback is the number one method of growth for SimplePortal, and our users are always finding ways for SimplePortal to grow. SimplePortal stays competative with other portal software by adding numerous user-requested features such as articles, block types and the ability to completely customize the portal page.</p>
<p>All this and SimplePortal has remained Simple! SimplePortal is built for simplicity and ease of use; ensuring the average forum administrator can install SimplePortal, configure a few settings, and show off the brand new portal to the users in minutes. Confusing menus, undesired pre-loaded blocks and settings that cannot be found are all avoided as much as possible. Because when it comes down to it, SimplePortal is YOUR portal, and should reflect your taste as much as possible.</p>';

	$default_blocks = array(
		'user_info' => array('label' => 'User Info', 'type' => 'UserInfo', 'col' => 1, 'row' => 1, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'whos_online' => array('label' => 'Who&#039;s Online', 'type' => 'WhosOnline', 'col' => 1, 'row' => 2, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'board_stats' => array('label' => 'Board Stats', 'type' => 'BoardStats', 'col' => 1, 'row' => 3, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'theme_select' => array('label' => 'Theme Select', 'type' => 'ThemeSelect', 'col' => 1, 'row' => 4, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'search' => array('label' => 'Search', 'type' => 'QuickSearch', 'col' => 1, 'row' => 5, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'news' => array('label' => 'News', 'type' => 'News', 'col' => 2, 'row' => 1, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~windowbg|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
		'welcome' => array('label' => 'Welcome', 'type' => 'Html', 'col' => 2, 'row' => 2, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~windowbg|body_custom_class~|body_custom_style~|no_title~1|no_body~'),
		'board_news' => array('label' => 'Board News', 'type' => 'BoardNews', 'col' => 2, 'row' => 3, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'recent_topics' => array('label' => 'Recent Topics', 'type' => 'Recent', 'col' => 3, 'row' => 1, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'top_poster' => array('label' => 'Top Poster', 'type' => 'TopPoster', 'col' => 4, 'row' => 1, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'recent_posts' => array('label' => 'Recent Posts', 'type' => 'Recent', 'col' => 4, 'row' => 2, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'staff' => array('label' => 'Forum Staff', 'type' => 'Staff', 'col' => 4, 'row' => 3, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'calendar' => array('label' => 'Calendar', 'type' => 'Calendar', 'col' => 4, 'row' => 4, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
		'top_boards' => array('label' => 'Top Boards', 'type' => 'TopBoards', 'col' => 4, 'row' => 5, 'permissions' => 3, 'display' => '', 'display_custom' => '', 'style' => ''),
	);

	$db->insert('ignore',
		'{db_prefix}sp_blocks',
		array('label' => 'text', 'type' => 'text', 'col' => 'int', 'row' => 'int', 'permissions' => 'int', 'styles' => 'int', 'visibility' => 'int'),
		$default_blocks,
		array('id_block', 'state')
		);

	$request = $db->query('', '
		SELECT MIN(id_block) AS id, type
		FROM {db_prefix}sp_blocks
		WHERE type IN ({array_string:types})
		GROUP BY type
		LIMIT 4',
		array(
			'types' => array('sp_html', 'sp_boardNews', 'sp_calendar', 'sp_recent'),
		)
	);
	while ($row = $db->fetch_assoc($request))
		$block_ids[$row['type']] = $row['id'];
	$db->free_result($request);

	$default_parameters = array(
		array('id_block' => $block_ids['sp_html'], 'variable' => 'content', 'value' => htmlspecialchars($welcome_text)),
		array('id_block' => $block_ids['sp_boardNews'], 'variable' => 'avatar', 'value' => 1),
		array('id_block' => $block_ids['sp_boardNews'], 'variable' => 'per_page', 'value' => 3),
		array('id_block' => $block_ids['sp_calendar'], 'variable' => 'events', 'value' => 1),
		array('id_block' => $block_ids['sp_calendar'], 'variable' => 'birthdays', 'value' => 1),
		array('id_block' => $block_ids['sp_calendar'], 'variable' => 'holidays', 'value' => 1),
		array('id_block' => $block_ids['sp_recent'], 'variable' => 'type', 'value' => 1),
		array('id_block' => $block_ids['sp_recent'], 'variable' => 'display', 'value' => 1),
	);

	$db->insert('replace',
		'{db_prefix}sp_parameters',
		array('id_block' => 'int', 'variable' => 'text', 'value' => 'text'),
		$default_parameters,
		array('id_block', 'variable')
	);
}

// Update to use permission profiles if they have not been added
$result = $db->query('', '
	SELECT id_profile
	FROM {db_prefix}sp_profiles
	WHERE type = {int:type}
	LIMIT {int:limit}',
	array(
		'type' => 1,
		'limit' => 1,
	)
);
list ($has_permission_profiles) = $db->fetch_row($result);
$db->free_result($result);
// No profiles, so add some defaults to get started
if (empty($has_permission_profiles))
{
	$request = $db->query('', '
		SELECT id_group
		FROM {db_prefix}membergroups
		WHERE min_posts != {int:min_posts}',
		array(
			'min_posts' => -1,
		)
	);
	$post_groups = array();
	while ($row = $db->fetch_assoc($request))
		$post_groups[] = $row['id_group'];
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

// Update the page table to accept more data
$dbtbl = db_table();
if (empty($modSettings['sp_version']) || $modSettings['sp_version'] < '2.4.1')
{
	$page_cols = $dbtbl->db_list_columns('{db_prefix}sp_pages', true);

	if (isset($page_cols['body']) && $page_cols['body']['type'] == 'text')
		$dbtbl->db_change_column('{db_prefix}sp_pages', 'body', array('type' => 'mediumtext'));
}

// Update the block table to include the mobile column if needed
$dbtbl->db_add_column('{db_prefix}sp_blocks', array('name' => 'mobile_view', 'type' => 'tinyint',  'size' => 4, 'default' => 0, 'unsigned' => true), 'ignore');

// Update tables to use style profiles in place of style
foreach (array('sp_articles', 'sp_blocks', 'sp_pages') as $sp_style)
{
	$block_cols = $dbtbl->db_list_columns('{db_prefix}' . $sp_style, true);
	if (!isset($block_cols['styles']))
	{
		$dbtbl->db_add_column('{db_prefix}' . $sp_style, array('name' => 'styles', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true));
		$dbtbl->db_remove_column('{db_prefix}' . $sp_style, 'style');
	}
}

// If there are no style profiles, then add some defaults to use
$result = $db->query('','
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
list ($has_style_profiles) = $db->fetch_row($result);
$db->free_result($result);
if (empty($has_style_profiles))
{
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

// Add / convert visibility profiles if none exist
$result = $db->query('','
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
list ($has_visibility_profiles) = $db->fetch_row($result);
$db->free_result($result);
if (empty($has_visibility_profiles))
{
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

// Update tables to use visibility profiles in place of display
foreach (array('sp_blocks') as $sp_visibility)
{
	$block_cols = $dbtbl->db_list_columns('{db_prefix}' . $sp_visibility, true);
	if (!isset($block_cols['visibility']))
	{
		$dbtbl->db_add_column('{db_prefix}' . $sp_visibility, array('name' => 'visibility', 'type' => 'mediumint', 'size' => 8, 'default' => 0, 'unsigned' => true));
		//$dbtbl->db_remove_column('{db_prefix}' . $sp_style, 'display');
		//$dbtbl->db_remove_column('{db_prefix}' . $sp_style, 'display_custom');
	}
}

$db_package_log = array();
foreach ($sp_tables as $sp_table_name => $null)
	$db_package_log[] = array('remove_table', $db_prefix . $sp_table_name);

if (ELK === 'SSI')
	echo 'Database changes were carried out successfully.';