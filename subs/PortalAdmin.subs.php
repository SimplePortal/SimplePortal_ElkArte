<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Toggles the current state of a block / control
 *
 * - Calls sp_changeState to toggle the on/off status
 * - Directs back based on type passed
 *
 * @param string $type type of control
 * @param int $id id of the control
 */
function sportal_admin_state_change($type, $id)
{
	if (!in_array($type, array('block', 'category', 'article')))
		fatal_lang_error('error_sp_id_empty', false);

	// Toggle the current state
	sp_changeState($type, $id);

	// Based on the type, find our way back
	if ($type == 'block')
	{
		$sides = array(1 => 'left', 2 => 'top', 3 => 'bottom', 4 => 'right');
		$list = !empty($_GET['redirect']) && isset($sides[$_GET['redirect']]) ? $sides[$_GET['redirect']] : 'list';

		redirectexit('action=admin;area=portalblocks;sa=' . $list);
	}
	elseif ($type == 'category')
		redirectexit('action=admin;area=portalarticles;sa=categories');
	elseif ($type == 'article')
		redirectexit('action=admin;area=portalarticles;sa=articles');
	else
		redirectexit('action=admin;area=portalconfig');
}

/**
 * Fetches all the classes (blocks) in the system
 *
 * - If supplied a name gets just that functions id
 * - Returns the functions in the order found in the file system
 * - Will not return blocks according to block setting for security reasons
 * - Uses naming pattern of subs/spblocks/xyz.block.php
 *
 * @param string|null $function
 */
function getFunctionInfo($function = null)
{
	$return = array();

	// Looking for a specific block or all of them
	if ($function !== null)
	{
		// Replace dots with nothing to avoid security issues
		$function = strtr($function, array('.' => ''));
		$pattern = SUBSDIR . '/spblocks/' . $function . '.block.php';
	}
	else
		$pattern = SUBSDIR . '/spblocks/*.block.php';

	// Iterates through a file system in a similar fashion to glob().
	$fs = new GlobIterator($pattern);

	// Loop on the glob-ules !
	foreach ($fs as $item)
	{
		// Convert file names to class names, UserInfo.block.pbp => User_Info_Block
		$class = str_replace('.block.php', '_Block', trim(preg_replace('/((?<=)\p{Lu}(?=\p{Ll}))/', '_$1', $item->getFilename()), '_'));

		// Load the block, make sure we can access it
		require_once($item->getPathname());
		if (!class_exists($class))
			continue;

		// Ensure they have permissions to view this block
		$perms = $class::permissionsRequired();
		if (!allowedTo($perms))
			continue;

		// Add it to our allowed lists
		$return[] = array(
			'id' => $class,
			'function' => str_replace('_Block', '', $class),
		);
	}

	return $function === null ? $return : current($return);
}

/**
 * Assigns row id's to each block in a specified column
 *
 * - Ensures each block has a unique row id
 * - For each block in a column, it will sequentially number the row id
 * based on the order the blocks are returned via getBlockInfo
 *
 * @param int|null $column_id
 */
function fixColumnRows($column_id = null)
{
	$db = database();

	// Get the list of all blocks in this column
	$blockList = getBlockInfo($column_id);
	$blockIds = array();

	foreach ($blockList as $block)
		$blockIds[] = $block['id'];

	$counter = 0;

	// Now sequentially set the row number for each block in this column
	foreach ($blockIds as $block)
	{
		$counter = $counter + 1;

		$db->query('', '
			UPDATE {db_prefix}sp_blocks
			SET row = {int:counter}
			WHERE id_block = {int:block}',
			array(
				'counter' => $counter,
				'block' => $block,
			)
		);
	}
}

/**
 * Toggles the active state of a passed control ID of a given Type
 *
 * @param string|null $type type of control
 * @param int|null $id specific id of the control
 */
function sp_changeState($type = null, $id = null)
{
	$db = database();

	if ($type == 'block')
		$query = array(
			'column' => 'state',
			'table' => 'sp_blocks',
			'query_id' => 'id_block',
			'id' => $id
		);
	elseif ($type == 'category')
		$query = array(
			'column' => 'status',
			'table' => 'sp_categories',
			'query_id' => 'id_category',
			'id' => $id
		);
	elseif ($type == 'article')
		$query = array(
			'column' => 'status',
			'table' => 'sp_articles',
			'query_id' => 'id_article',
			'id' => $id
		);
	elseif ($type == 'page')
		$query = array(
			'column' => 'status',
			'table' => 'sp_pages',
			'query_id' => 'id_page',
			'id' => $id
		);
	elseif ($type == 'shout')
		$query = array(
			'column' => 'status',
			'table' => 'sp_shoutboxes',
			'query_id' => 'id_shoutbox',
			'id' => $id
		);
	else
		return false;

	// Clap on, Clap off
	$db->query('', '
		UPDATE {db_prefix}{raw:table}
		SET {raw:column} = CASE WHEN {raw:column} = {int:is_active} THEN 0 ELSE 1 END
		WHERE {raw:query_id} = {int:id}',
		array(
			'table' => $query['table'],
			'column' => $query['column'],
			'query_id' => $query['query_id'],
			'id' => $id,
			'is_active' => 1,
		)
	);

	return true;
}

/**
 * Load the default theme names
 *
 * @return array
 */
function sp_general_load_themes()
{
	global $txt;

	$db = database();

	$request = $db->query('', '
		SELECT id_theme, value AS name
		FROM {db_prefix}themes
		WHERE variable = {string:name}
			AND id_member = {int:member}
		ORDER BY id_theme',
		array(
			'member' => 0,
			'name' => 'name',
		)
	);
	$SPortal_themes = array('0' => &$txt['portalthemedefault']);
	while ($row = $db->fetch_assoc($request))
		$SPortal_themes[$row['id_theme']] = $row['name'];
	$db->free_result($request);

	return $SPortal_themes;
}

/**
 * This will file the $context['member_groups'] to the given options
 *
 * @param int[]|string $selectedGroups - all groups who should be shown as selected, if you like to check all than
 *     insert an 'all' You can also Give the function a string with '2,3,4'
 * @param string $show - 'normal' => will show all groups, and add a guest and regular member (Standard)
 *						 'post' => will load only post groups
 *						 'master' => will load only not postbased groups
 * @param string $contextName - where the data should stored in the $context
 * @param string $subContext
 */
function sp_loadMemberGroups($selectedGroups = array(), $show = 'normal', $contextName = 'member_groups', $subContext = 'SPortal')
{
	global $context, $txt;

	$db = database();

	// Some additional Language stings are needed
	loadLanguage('ManageBoards');

	// Make sure its empty
	if (!empty($subContext))
		$context[$subContext][$contextName] = array();
	else
		$context[$contextName] = array();

	// Presetting some things :)
	if (!is_array($selectedGroups))
		$checked = strtolower($selectedGroups) == 'all';
	else
		$checked = false;

	if (!$checked && isset($selectedGroups) && $selectedGroups === '0')
		$selectedGroups = array(0);
	elseif (!$checked && !empty($selectedGroups))
	{
		if (!is_array($selectedGroups))
			$selectedGroups = explode(',', $selectedGroups);

		// Remove all strings, i will only allow ids :P
		foreach ($selectedGroups as $k => $i)
			$selectedGroups[$k] = (int) $i;

		$selectedGroups = array_unique($selectedGroups);
	}
	else
		$selectedGroups = array();

	// Okay let's checkup the show function
	$show_option = array(
		'normal' => 'id_group != 3',
		'moderator' => 'id_group != 1 AND id_group != 3',
		'post' => 'min_posts != -1',
		'master' => 'min_posts = -1 AND id_group != 3',
	);

	$show = strtolower($show);

	if (!isset($show_option[$show]))
		$show = 'normal';

	// Guest and Members are added manually. Only on normal and master View =)
	if ($show == 'normal' || $show == 'master' || $show == 'moderator')
	{
		if ($show != 'moderator')
		{
			$context[$contextName][-1] = array(
				'id' => -1,
				'name' => $txt['membergroups_guests'],
				'checked' => $checked || in_array(-1, $selectedGroups),
				'is_post_group' => false,
			);
		}
		$context[$contextName][0] = array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'checked' => $checked || in_array(0, $selectedGroups),
			'is_post_group' => false,
		);
	}

	// Load membergroups.
	$request = $db->query('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE {raw:show}
		ORDER BY min_posts, id_group != {int:global_moderator}, group_name',
		array(
			'show' => $show_option[$show],
			'global_moderator' => 2,
		)
	);
	while ($row = $db->fetch_assoc($request))
	{
		$context[$contextName][(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => $checked || in_array($row['id_group'], $selectedGroups),
			'is_post_group' => $row['min_posts'] != -1,
		);
	}
	$db->free_result($request);
}

/**
 * Loads the membergroups in the system
 *
 * - excludes moderator groups
 * - loads id and name for each group
 * - used for template group select lists / permissions
 */
function sp_load_membergroups()
{
	global $txt;

	$db = database();

	// Need to speak the right language
	loadLanguage('ManageBoards');

	// Start off with some known ones, guests and regular members
	$groups = array(
		-1 => $txt['parent_guests_only'],
		0 => $txt['parent_members_only'],
	);

	// Load up all groups in the system as long as they are not moderator groups
	$request = $db->query('', '
		SELECT
			group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group != {int:moderator_group}
		ORDER BY min_posts, group_name',
		array(
			'moderator_group' => 3,
		)
	);
	while ($row = $db->fetch_assoc($request))
		$groups[(int) $row['id_group']] = trim($row['group_name']);
	$db->free_result($request);

	return $groups;
}

/**
 * Returns the total count of categories in the system
 */
function sp_count_categories()
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_categories'
	);
	list ($total_categories) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_categories;
}

/**
 * Loads all of the category's in the system
 *
 * - Returns an indexed array of the categories
 *
 * @param int|null $start
 * @param int|null $items_per_page
 * @param string|null $sort
 */
function sp_load_categories($start = null, $items_per_page = null, $sort = null)
{
	global $scripturl, $txt, $context;

	$db = database();

	$request = $db->query('', '
		SELECT id_category, name, namespace, articles, status
		FROM {db_prefix}sp_categories' . (isset($sort) ? '
		ORDER BY {raw:sort}' : '') . (isset($start) ? '
		LIMIT {int:start}, {int:limit}' : ''),
		array(
			'sort' => $sort,
			'start' => $start,
			'limit' => $items_per_page,
		)
	);
	$categories = array();
	while ($row = $db->fetch_assoc($request))
	{
		$categories[$row['id_category']] = array(
			'id' => $row['id_category'],
			'category_id' => $row['namespace'],
			'name' => $row['name'],
			'href' => $scripturl . '?category=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?category=' . $row['namespace'] . '">' . $row['name'] . '</a>',
			'articles' => $row['articles'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalcategories;sa=status;category_id=' . $row['id_category'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status'])
				? 'deactive' : 'active', $txt['sp_admin_categories_' . (!empty($row['status']) ? 'de'
				: '') . 'activate']) . '</a>',
		);
	}
	$db->free_result($request);

	return $categories;
}

/**
 * Checks if a namespace is already in use by a category_id
 *
 * @param int $id
 * @param string $namespace
 */
function sp_check_duplicate_category($id, $namespace)
{
	$db = database();

	$result = $db->query('', '
		SELECT id_category
		FROM {db_prefix}sp_categories
		WHERE namespace = {string:namespace}
			AND id_category != {int:current}
		LIMIT {int:limit}',
		array(
			'limit' => 1,
			'namespace' => $namespace,
			'current' => $id,
		)
	);
	list ($has_duplicate) = $db->fetch_row($result);
	$db->free_result($result);

	return $has_duplicate;
}

/**
 * Update an existing category or add a new one to the database
 *
 * If adding a new one, will return the id of the new category
 *
 * @param mixed[] $data field name to value for use in query
 * @param boolean $is_new
 */
function sp_update_category($data, $is_new = false)
{
	$db = database();

	$id = isset($data['id']) ? $data['id'] : null;

	// Field definitions
	$fields = array(
		'namespace' => 'string',
		'name' => 'string',
		'description' => 'string',
		'permissions' => 'int',
		'status' => 'int',
	);

	// New category?
	if ($is_new)
	{
		unset($data['id']);
		$db->insert('', '
			{db_prefix}sp_categories',
			$fields,
			$data,
			array('id_category')
		);
		$id = $db->insert_id('{db_prefix}sp_categories', 'id_category');
	}
	// Update an existing one then
	else
	{
		$update_fields = array();

		foreach ($fields as $name => $type)
			$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

		$db->query('', '
			UPDATE {db_prefix}sp_categories
			SET ' . implode(', ', $update_fields) . '
			WHERE id_category = {int:id}', $data
		);
	}

	return $id;
}

/**
 * Removes a category or group of categories by id
 *
 * @param int[] $category_ids
 */
function sp_delete_categories($category_ids = array())
{
	$db = database();

	// Remove the categories
	$db->query('', '
		DELETE FROM {db_prefix}sp_categories
		WHERE id_category IN ({array_int:categories})',
		array(
			'categories' => $category_ids,
		)
	);

	// And remove the articles that were in those categories
	$db->query('', '
		DELETE FROM {db_prefix}sp_articles
		WHERE id_category IN ({array_int:categories})',
		array(
			'categories' => $category_ids,
		)
	);
}

/**
 * Updates the total articles in a category
 *
 * @param int $category_id
 */
function sp_category_update_total($category_id)
{
	$db = database();

	$db->query('', '
		UPDATE {db_prefix}sp_categories
		SET articles = articles - 1
		WHERE id_category = {int:id}',
		array(
			'id' => $category_id,
		)
	);
}

/**
 * Returns the total count of articles in the system
 */
function sp_count_articles()
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles'
	);
	list ($total_articles) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_articles;
}

/**
 * Loads all of the articles in the system
 * Returns an indexed array of the articles
 *
 * @param int $start
 * @param int $items_per_page
 * @param string $sort
 */
function sp_load_articles($start, $items_per_page, $sort)
{
	global $scripturl, $txt, $context;

	$db = database();

	$request = $db->query('', '
		SELECT
			spa.id_article, spa.id_category, spc.name, spc.namespace AS category_namespace,
			IFNULL(m.id_member, 0) AS id_author, IFNULL(m.real_name, spa.member_name) AS author_name,
			spa.namespace AS article_namespace, spa.title, spa.type, spa.date, spa.status
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spa.id_member)
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort,
			'start' => $start,
			'limit' => $items_per_page,
		)
	);
	$articles = array();
	while ($row = $db->fetch_assoc($request))
	{
		$articles[$row['id_article']] = array(
			'id' => $row['id_article'],
			'article_id' => $row['article_namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'category_name' => $row['name'],
			'author_name' => $row['author_name'],
			'category' => array(
				'id' => $row['id_category'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
			),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author']
					? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>')
					: $row['author_name'],
			),
			'type' => $row['type'],
			'type_text' => $txt['sp_articles_type_' . $row['type']],
			'date' => standardTime($row['date']),
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=status;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status'])
					? 'deactive' : 'active', $txt['sp_admin_articles_' . (!empty($row['status']) ? 'de'
					: '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=edit;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=delete;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_articles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$db->free_result($request);

	return $articles;
}

/**
 * Removes a article or group of articles by id's
 *
 * @param int[]|int $article_ids
 */
function sp_delete_articles($article_ids = array())
{
	$db = database();

	if (!is_array($article_ids))
		$article_ids = array($article_ids);

	$db->query('', '
		DELETE FROM {db_prefix}sp_articles
		WHERE id_article = {array_int:id}',
		array(
			'id' => $article_ids,
		)
	);
}

/**
 * Validates that an articles id is not duplicated in a given namespace
 *
 * return true if its a duplicate or false if its unique
 *
 * @param int $article_id
 * @param string $namespace
 */
function sp_duplicate_articles($article_id, $namespace)
{
	$db = database();

	$result = $db->query('', '
		SELECT
			id_article
		FROM {db_prefix}sp_articles
		WHERE namespace = {string:namespace}
			AND id_article != {int:current}
		LIMIT 1',
		array(
			'limit' => 1,
			'namespace' => $namespace,
			'current' => $article_id,
		)
	);
	list ($has_duplicate) = $db->fetch_row($result);
	$db->free_result($result);

	return $has_duplicate;
}

/**
 * Saves or updates an articles information
 *
 * - expects to have $context populated from sportal_get_articles()
 * - add items as a new article is is_new is true otherwise updates and existing one
 *
 * @param mixed[] $article_info array of fields details to save/update
 * @param boolean $is_new true for new insertion, false to update
 * @param boolean $update_counts true to update category counts
 */
function sp_save_article($article_info, $is_new = false, $update_counts = true)
{
	global $context, $user_info;

	$db = database();

	// Our base article database looks like this, so shall you comply
	$fields = array(
		'id_category' => 'int',
		'namespace' => 'string',
		'title' => 'string',
		'body' => 'string',
		'type' => 'string',
		'permissions' => 'int',
		'styles' => 'int',
		'status' => 'int',
	);

	// Brand new, insert it
	if ($is_new)
	{
		// New will set this
		unset($article_info['id']);

		// If new we set these one time fields
		$fields = array_merge($fields, array(
			'id_member' => 'int',
			'member_name' => 'string',
			'date' => 'int',
		));

		// And populate them with data
		$article_info = array_merge($article_info, array(
			'id_member' => $user_info['id'],
			'member_name' => $user_info['name'],
			'date' => time(),
		));

		// Add the new article to the system
		$db->insert('', '
			{db_prefix}sp_articles',
			$fields,
			$article_info,
			array('id_article')
		);
		$article_info['id'] = $db->insert_id('{db_prefix}sp_articles', 'id_article');
	}
	// The editing so we update what was there
	else
	{
		$update_fields = array();
		foreach ($fields as $name => $type)
			$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

		$db->query('', '
			UPDATE {db_prefix}sp_articles
			SET ' . implode(', ', $update_fields) . '
			WHERE id_article = {int:id}',
			array_merge(array(
				'id' => $article_info['id']),
				$article_info)
		);
	}

	// Now is a good time to update the counters if needed
	if ($update_counts && ($is_new || $article_info['id_category'] != $context['article']['category']['id']))
	{
		// Increase the number of items in this category
		$db->query('', '
			UPDATE {db_prefix}sp_categories
			SET articles = articles + 1
			WHERE id_category = {int:id}',
			array(
				'id' => $article_info['id_category'],
			)
		);

		// Not new then moved, so decrease the old category count
		if (!$is_new)
		{
			$db->query('', '
				UPDATE {db_prefix}sp_categories
				SET articles = articles - 1
				WHERE id_category = {int:id}',
				array(
					'id' => $context['article']['category']['id'],
				)
			);
		}
	}

	return $article_info['id'];
}

/**
 * Returns the total count of pages in the system
 */
function sp_count_pages()
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_pages'
	);
	list ($total_pages) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_pages;
}

/**
 * Loads all of the pages in the system
 * Returns an indexed array of the pages
 *
 * @param int $start
 * @param int $items_per_page
 * @param string $sort
 */
function sp_load_pages($start, $items_per_page, $sort)
{
	global $scripturl, $txt, $context;

	$db = database();

	$request = $db->query('', '
		SELECT id_page, namespace, title, type, views, status
		FROM {db_prefix}sp_pages
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort,
			'start' => $start,
			'limit' => $items_per_page,
		)
	);
	$pages = array();
	while ($row = $db->fetch_assoc($request))
	{
		$pages[$row['id_page']] = array(
			'id' => $row['id_page'],
			'page_id' => $row['namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?page=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?page=' . $row['namespace'] . '">' . $row['title'] . '</a>',
			'type' => $row['type'],
			'type_text' => $txt['sp_pages_type_' . $row['type']],
			'views' => $row['views'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=status;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status'])
				? 'deactive' : 'active', $txt['sp_admin_pages_' . (!empty($row['status']) ? 'de'
				: '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=edit;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=delete;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_pages_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$db->free_result($request);

	return $pages;
}

/**
 * Removes a page or group of pages by id
 *
 * @param int[] $page_ids
 */
function sp_delete_pages($page_ids = array())
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_pages
		WHERE id_page IN ({array_int:pages})',
		array(
			'pages' => $page_ids,
		)
	);
}

/**
 * Saves or updates an page
 *
 * - add items as a new page is is_new is true otherwise updates and existing one
 *
 * @param mixed[] $page_info array of fields details to save/update
 * @param boolean $is_new true for new insertion, false to update
 */
function sp_save_page($page_info, $is_new = false)
{
	$db = database();

	// Our base page database looks like this, so shall you
	$fields = array(
		'namespace' => 'string',
		'title' => 'string',
		'body' => 'string',
		'type' => 'string',
		'permissions' => 'int',
		'styles' => 'int',
		'status' => 'int',
	);

	// Brand new, insert it
	if ($is_new)
	{
		unset($page_info['id']);

		$db->insert('', '
			{db_prefix}sp_pages',
			$fields,
			$page_info,
			array('id_page')
		);
		$page_info['id'] = $db->insert_id('{db_prefix}sp_pages', 'id_page');
	}
	// The editing so we update what was there
	else
	{
		$update_fields = array();
		foreach ($fields as $name => $type)
			$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

		$db->query('', '
			UPDATE {db_prefix}sp_pages
			SET ' . implode(', ', $update_fields) . '
			WHERE id_page = {int:id}', $page_info
		);
	}

	return $page_info['id'];
}

/**
 * Returns the total count of shoutboxes in the system
 */
function sp_count_shoutbox()
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shoutboxes'
	);
	list ($total_shoutbox) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_shoutbox;
}

/**
 * Loads all of the shoutboxes in the system
 * Returns an indexed array of the shoutboxes
 *
 * @param int $start
 * @param int $items_per_page
 * @param string $sort
 */
function sp_load_shoutbox($start, $items_per_page, $sort)
{
	global $scripturl, $txt, $context;

	$db = database();

	$request = $db->query('', '
		SELECT id_shoutbox, name, caching, status, num_shouts
		FROM {db_prefix}sp_shoutboxes
		ORDER BY id_shoutbox, {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort,
			'start' => $start,
			'limit' => $items_per_page,
		)
	);
	$shoutboxes = array();
	while ($row = $db->fetch_assoc($request))
	{
		$shoutboxes[$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'shouts' => $row['num_shouts'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=status;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status'])
					? 'deactive' : 'active', $txt['sp_admin_shoutbox_' . (!empty($row['status']) ? 'de'
					: '') . 'activate']) . '</a>',
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=edit;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'prune' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=prune;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('bin') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=delete;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_shoutbox_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$db->free_result($request);

	return $shoutboxes;
}

/**
 * Removes a shoutbox or group of shoutboxes by id
 *
 * @param int[] $shoutbox_ids
 */
function sp_delete_shoutbox($shoutbox_ids = array())
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_shoutboxes
		WHERE id_shoutbox IN ({array_int:shoutbox})',
		array(
			'shoutbox' => $shoutbox_ids,
		)
	);

	$db->query('', '
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shoutbox IN ({array_int:shoutbox})',
		array(
			'shoutbox' => $shoutbox_ids,
		)
	);
}

/**
 * Returns the total count of profiles in the system
 *
 * @param int $type (1 = permissions, 2 = styles)
 */
function sp_count_profiles($type = 1)
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => $type,
		)
	);
	list ($total_profiles) =  $db->fetch_row($request);
	$db->free_result($request);

	return $total_profiles;
}

/**
 * Loads all of the permission profiles in the system
 * Returns an indexed array of them
 *
 * @param int $start
 * @param int $items_per_page
 * @param string $sort
 * @param int $type (1 = permissions, 2 = styles)
 */
function sp_load_profiles($start, $items_per_page, $sort, $type = 1)
{
	global $scripturl, $txt, $context;

	$db = database();

	// First load up all of the permission profiles names in the system
	$request = $db->query('', '
		SELECT
			id_profile, name
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'type' => $type,
			'sort' => $sort,
			'start' => $start,
			'limit' => $items_per_page,
		)
	);
	$profiles = array();
	while ($row = $db->fetch_assoc($request))
	{
		$profiles[$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)])
				? $txt['sp_admin_profiles' . substr($row['name'], 1)]
				: $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editpermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletepermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$db->free_result($request);

	// Now for each profile, load up the specific in-use for each area of the portal
	switch ($type)
	{
		case 2:
			$select = 'styles, COUNT(*) AS used';
			$area = array('articles', 'blocks', 'pages');
			$group = 'styles';
			break;
		case 3:
			$select = 'display, COUNT(*) AS used';
			$area = array('articles', 'blocks', 'pages', 'shoutboxes');
			$group = 'display';
			break;
		default:
			$select = 'permissions, COUNT(*) AS used';
			$area = array('articles', 'blocks', 'categories', 'pages', 'shoutboxes');
			$group = 'permissions';
			break;
	}

	foreach ($area as $module)
	{
		$request = $db->query('', '
			SELECT ' . $select . '
			FROM {db_prefix}sp_' . $module . '
			GROUP BY ' . $group,
			array(
			)
		);
		while ($row = $db->fetch_assoc($request))
		{
			if (isset($profiles[$row[$group]]))
			{
				$profiles[$row[$group]][$module] = $row['used'];
			}
		}
		$db->free_result($request);
	}

	return $profiles;

}

/**
 * Removes a group of profiles by id
 *
 * @param int[] $remove_ids
 */
function sp_delete_profiles($remove_ids = array())
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile IN ({array_int:profiles})',
		array(
			'profiles' => $remove_ids,
		)
	);
}

/**
 * Loads boards and pages for template selection
 *
 * - Returns the results in $context for the template
 */
function sp_block_template_helpers()
{
	global $context;

	$db = database();

	// Get a list of board names for use in the template
	$request = $db->query('', '
		SELECT id_board, name
		FROM {db_prefix}boards
		ORDER BY name DESC'
	);
	$context['display_boards'] = array();
	while ($row = $db->fetch_assoc($request))
		$context['display_boards']['b' . $row['id_board']] = $row['name'];
	$db->free_result($request);

	// Get all the pages loaded in the system for template use
	$request = $db->query('', '
		SELECT id_page, title
		FROM {db_prefix}sp_pages
		ORDER BY title DESC'
	);
	$context['display_pages'] = array();
	while ($row = $db->fetch_assoc($request))
		$context['display_pages']['p' . $row['id_page']] = $row['title'];
	$db->free_result($request);
}

/**
 * Updates the position of the block in the portal (column and row in column)
 *
 * - Makes room above or below a position where a new block is inserted by renumbering
 * - returns nothing
 *
 * @param int $current_row
 * @param int $row
 * @param int $col
 * @param boolean $decrement
 */
function sp_update_block_row($current_row, $row, $col, $decrement = true)
{
	$db = database();

	if ($decrement)
	{
		$db->query('', '
			UPDATE {db_prefix}sp_blocks
			SET row = row - 1
			WHERE col = {int:col}
				AND row > {int:start}
				AND row <= {int:end}',
			array(
				'col' => (int) $col,
				'start' => $current_row,
				'end' => $row,
			)
		);
	}
	else
	{
		$db->query('', '
			UPDATE {db_prefix}sp_blocks
			SET row = row + 1
			WHERE col = {int:col}
				AND row >= {int:start}' . (!empty($current_row) ? '
				AND row < {int:end}' : ''),
			array(
				'col' => (int) $col,
				'start' => $row,
				'end' => !empty($current_row) ? $current_row : 0,
			)
		);
	}
}

/**
 * Fetches the rows from a specified column and returns the values
 *
 * - If a current block ID is not specified the next available row number in
 * the specified column is returned
 * - If a block id is specified, its row number + 1 is returned
 *
 * @param int $block_column
 * @param int $block_id
 */
function sp_block_nextrow($block_column, $block_id = 0)
{
	$db = database();

	$request = $db->query('', '
		SELECT
			row
		FROM {db_prefix}sp_blocks
		WHERE col = {int:col}' . (!empty($block_id) ? '
			AND id_block != {int:current_id}' : '' ) . '
		ORDER BY row DESC
		LIMIT 1',
		array(
			'col' => $block_column,
			'current_id' => $block_id,
		)
	);
	list ($row) = $db->fetch_row($request);
	$db->free_result($request);

	return $row + 1;
}

/**
 * Inserts a new block to the portal
 *
 * @param mixed[] $blockInfo
 */
function sp_block_insert($blockInfo)
{
	$db = database();

	$db->insert('', '
		{db_prefix}sp_blocks',
		array(
			'label' => 'string', 'type' => 'string', 'col' => 'int', 'row' => 'int', 'permissions' => 'int', 'styles' => 'int',
			'state' => 'int', 'force_view' => 'int', 'mobile_view' => 'int', 'display' => 'string', 'display_custom' => 'string',
		),
		$blockInfo,
		array('id_block')
	);

	return $db->insert_id('{db_prefix}sp_blocks', 'id_block');
}

/**
 * Updates an existing portal block with new values
 *
 * - Removes all parameters stored with the box in anticipation of new
 * ones being supplied
 *
 * @param mixed[] $blockInfo
 */
function sp_block_update($blockInfo)
{
	$db = database();

	// The fields in the database
	$block_fields = array(
		"label = {string:label}",
		"permissions = {int:permissions}",
		"styles={int:styles}",
		"state = {int:state}",
		"force_view = {int:force_view}",
		"mobile_view = {int:mobile_view}",
		"display = {string:display}",
		"display_custom = {string:display_custom}",
	);

	if (!empty($blockInfo['row']))
		$block_fields[] = "row = {int:row}";
	else
		unset($blockInfo['row']);

	// Update all the blocks fields
	$db->query('', '
		UPDATE {db_prefix}sp_blocks
		SET ' . implode(', ', $block_fields) . '
		WHERE id_block = {int:id}', $blockInfo
	);

	// Remove any parameters it has
	$db->query('', '
		DELETE FROM {db_prefix}sp_parameters
		WHERE id_block = {int:id}',
		array(
			'id' => $blockInfo['id'],
		)
	);
}

/**
 * Inserts parameters for a specific block
 *
 * @param mixed[] $new_parameters
 * @param int $id_block
 */
function sp_block_insert_parameters($new_parameters, $id_block)
{
	$db = database();

	$parameters = array();
	foreach ($new_parameters as $variable => $value)
		$parameters[] = array(
			'id_block' => $id_block,
			'variable' => $variable,
			'value' => $value,
		);

	$db->insert('', '
		{db_prefix}sp_parameters',
		array('id_block' => 'int', 'variable' => 'string', 'value' => 'string'),
		$parameters,
		array()
	);
}

/**
 * Returns the current column and row for a given block id
 *
 * @param int $block_id
 */
function sp_block_get_position($block_id)
{
	$db = database();

	$request = $db->query('', '
		SELECT col, row
		FROM {db_prefix}sp_blocks
		WHERE id_block = {int:block_id}
		LIMIT 1',
		array(
			'block_id' => $block_id,
		)
	);
	list ($current_side, $current_row) = $db->fetch_row($request);
	$db->free_result($request);

	return array($current_side,$current_row);
}

/**
 * Move a block to the end of a new column
 *
 * @param int $block_id
 * @param int $target_side
 */
function sp_block_move_col($block_id, $target_side)
{
	$db = database();

	// Moving to a new column, lets place it at the end
	$current_row = 100;

	$db->query('', '
		UPDATE {db_prefix}sp_blocks
		SET col = {int:target_side}, row = {int:temp_row}
		WHERE id_block = {int:block_id}',
		array(
			'target_side' => $target_side,
			'temp_row' => $current_row,
			'block_id' => $block_id,
		)
	);
}

/**
 * Adds the portal block to the new row position
 *
 * - Opens up space in the column by shift all rows below the insertion point
 * down one
 * - Adds the block ID to the specified column and row
 *
 * @param int $block_id
 * @param int $target_side
 * @param int $target_row
 */
function sp_blocks_move_row($block_id, $target_side, $target_row)
{
	$db = database();

	// Shift all the rows below the insertion point down one
	$db->query('', '
		UPDATE {db_prefix}sp_blocks
		SET row = row + 1
		WHERE col = {int:target_side}
			AND row >= {int:target_row}',
		array(
			'target_side' => $target_side,
			'target_row' => $target_row,
		)
	);

	// Set the new block to the now available row position
	$db->query('', '
		UPDATE {db_prefix}sp_blocks
		SET row = {int:target_row}
		WHERE id_block = {int:block_id}',
		array(
			'target_row' => $target_row,
			'block_id' => $block_id,
		)
	);
}

/**
 * Remove a block from the portal
 *
 * - removes the block from the portal
 * - removes the blocks parameters
 *
 * @param int $block_id
 */
function sp_block_delete($block_id)
{
	$db = database();

	$block_id = (int) $block_id;

	// No block
	$db->query('', '
		DELETE FROM {db_prefix}sp_blocks
		WHERE id_block = {int:id}',
		array(
			'id' => $block_id,
		)
	);

	// No parameters
	$db->query('', '
		DELETE FROM {db_prefix}sp_parameters
		WHERE id_block = {int:id}',
		array(
			'id' => $block_id,
		)
	);
}

/**
 * Function to add or update a profile, style, permissions or display
 *
 * @param mixed[] $profile_info The data to insert/update
 * @param bool $is_new if to update or insert
 *
 * @return int
 */
function sp_add_permission_profile($profile_info, $is_new = false)
{
	$db = database();

	// Our database fields
	$fields = array(
		'type' => 'int',
		'name' => 'string',
		'value' => 'string',
	);

	// A new profile?
	if ($is_new)
	{
		// Don't need this, we will create it instead
		unset($profile_info['id']);

		$db->insert('',
			'{db_prefix}sp_profiles',
			$fields,
			$profile_info,
			array('id_profile')
		);
		$profile_info['id'] = $db->insert_id('{db_prefix}sp_profiles', 'id_profile');
	}
	// Or an edit, we do a little update
	else
	{
		$update_fields = array();
		foreach ($fields as $name => $type)
			$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

		$res = $db->query('', '
			UPDATE {db_prefix}sp_profiles
			SET ' . implode(', ', $update_fields) . '
			WHERE id_profile = {int:id}',
			$profile_info
		);
	}

	return (int) $profile_info['id'];
}

/**
 * Removes a single permission profile from the system
 *
 * @param int $profile_id
 */
function sp_delete_permission_profile($profile_id)
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile = {int:id}',
		array(
			'id' => $profile_id,
		)
	);
}