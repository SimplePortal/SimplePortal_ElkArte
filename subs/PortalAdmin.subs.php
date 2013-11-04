<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('ELK'))
	die('No access...');

function sportal_admin_state_change($type, $id)
{
	if (!in_array($type, array('block', 'category', 'article')))
		fatal_lang_error('error_sp_id_empty', false);

	sp_changeState($type, $id);

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

function getFunctionInfo($function = null)
{
	$db = database();

	$request = $db->query('', '
		SELECT id_function, name
		FROM {db_prefix}sp_functions' . (!empty($function) ? '
		WHERE name = {string:function}' : '') . '
		ORDER BY function_order',
		array(
			'function' => $function,
		)
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		if ($row['name'] == 'sp_php' && !allowedTo('admin_forum'))
			continue;

		$return[] = array(
			'id' => $row['id_function'],
			'function' => $row['name'],
		);
	}
	$db->free_result($request);

	return $return;
}

function fixColumnRows($column_id = null)
{
	$db = database();

	$blockList = getBlockInfo($column_id);
	$blockIds = array();

	foreach ($blockList as $block)
		$blockIds[] = $block['id'];

	$counter = 0;

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
	else
		return false;

	$db->query('', '
		UPDATE {db_prefix}{raw:table}
		SET {raw:column} = CASE WHEN {raw:column} = {int:is_active} THEN 0 ELSE 1 END
		WHERE {raw:query_id} = {int:id}',
		array(
			'table' => $query['table'],
			'column' => $query['column'],
			'state' => $state,
			'query_id' => $query['query_id'],
			'id' => $id,
			'is_active' => 1,
		)
	);
}

/**
 * This will file the $context['member_groups'] to the given options
 *
 * @param type $selectedGroups - all groups who should be shown as selcted, if you like to check all than insert an 'all'
 *								 You can also Give the function a string with '2,3,4'
 * @param type $show -  'normal' => will show all groups, and add a guest and regular member (Standard)
 *						'post' => will load only post groups
 *						'master' => will load only not postbased groups
 * @param type $contextName - where the datas should stored in the $context
 * @param type $subContext
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

	// Preseting some things :)
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

		// Remove all strings, i will only allowe ids :P
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

	// Guest and Members are added manually. Only on normal ond master View =)
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

function sp_load_membergroups()
{
	global $txt;

	$db = database();

	loadLanguage('ManageBoards');

	$groups = array(
		-1 => $txt['parent_guests_only'],
		0 => $txt['parent_members_only'],
	);

	$request = $db->query('', '
		SELECT group_name, id_group, min_posts
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
	$total_categories = 0;

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_categories'
	);
	list ($total_categories) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_categories;
}

/**
 * Loads all of the categorys in the system
 * Returns an indexed array of the categories
 *
 * @param int $start
 * @param int $items_per_page
 * @param string $sort
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
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalcategories;sa=status;category_id=' . $row['id_category'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_categories_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
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
 * If adding a new one, will return the id of the new category
 *
 * @param array $fields
 * @param array $data
 * @param boolean $is_new
 */
function sp_update_category($fields, $data, $is_new = false)
{
	$db = database();

	$id = isset($data['id']) ? $data['id'] : null;

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
 * @param array $category_ids
 */
function sp_delete_categories($category_ids = array())
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_categories
		WHERE id_category IN ({array_int:categories})',
		array(
			'categories' => $category_ids,
		)
	);

	$db->query('', '
		DELETE FROM {db_prefix}sp_articles
		WHERE id_category IN ({array_int:categories})',
		array(
			'categories' => $category_id,
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
	$total_articles = 0;

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
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
			),
			'type' => $row['type'],
			'type_text' => $txt['sp_articles_type_' . $row['type']],
			'date' => standardTime($row['date']),
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalarticles;sa=status;article_id=' . $row['id_article'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_articles_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
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
 * @param array $article_ids
 */
function sp_delete_articles($article_ids = array())
{
	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}sp_articles
		WHERE id_article = {array_int:id}',
		array(
			'id' => $article_ids,
		)
	);
}

/**
 * Returns the total count of pages in the system
 */
function sp_count_pages()
{
	$db = database();
	$total_pages = 0;

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
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalpages;sa=status;page_id=' . $row['id_page'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_pages_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
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
 * @param array $article_ids
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
 * Returns the total count of pages in the system
 */
function sp_count_shoutbox()
{
	$db = database();
	$total_shoutbox = 0;

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shoutboxes'
	);
	list ($total_shoutbox) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_shoutbox;
}

/**
 * Loads all of the pages in the system
 * Returns an indexed array of the pages
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
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=status;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_shoutbox_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
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
 * @param array $article_ids
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
 */
function sp_count_profiles()
{
	$db = database();
	$total_profiles = 0;

	$request = $db->query('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => 1,
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
 */
function sp_load_profiles($start, $items_per_page, $sort)
{
	global $scripturl, $txt, $context;

	$db = database();

	// First load up all of the permission profiles names in the system
	$request = $db->query('','
		SELECT id_profile, name
		FROM {db_prefix}sp_profiles
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
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
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editpermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletepermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$db->free_result($request);

	// Now for each profile, load up the specific permisssions for each area of the portal
	foreach (array('articles', 'blocks', 'categories', 'pages', 'shoutboxes') as $module)
	{
		$request = $db->query('','
			SELECT permissions, COUNT(*) AS used
			FROM smf_sp_{raw:module}
			GROUP BY permissions',
			array(
				'module' => $module,
			)
		);
		while ($row = $db->fetch_assoc($request))
		{
			if (isset($profiles[$row['permissions']]))
				$profiles[$row['permissions']][$module] = $row['used'];
		}
		$db->free_result($request);
	}

	return $profiles;
}

/**
 * Removes a permission group by id
 *
 * @param array $article_ids
 */
function sp_delete_profiles($remove_ids = array())
{
	$db = database();

	$db->query('','
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile IN ({array_int:profiles})',
		array(
			'profiles' => $remove_ids,
		)
	);
}