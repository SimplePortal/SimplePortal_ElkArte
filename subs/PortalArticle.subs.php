<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 1
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Returns the number of views and comments for a given article
 *
 * @param int $id the id of the article
 *
 * @return array
 */
function sportal_get_article_views_comments($id)
{
	$db = database();

	if (empty($id))
	{
		return array(0, 0);
	}

	// Make the request
	$request = $db->query('', '
		SELECT
			views, comments
		FROM {db_prefix}sp_articles
		WHERE id_article = {int:article_id}
		LIMIT {int:limit}',
		array(
			'article_id' => $id,
			'limit' => 1,
		)
	);
	$result = array(0, 0);
	while ($row = $db->fetch_assoc($request))
	{
		$result[0] = $row['views'];
		$result[1] = $row['comments'];
	}
	$db->free_result($request);

	return $result;
}

/**
 * Loads an article by id, or articles by namespace
 *
 * @param int|string|null $article_id id of an article or string for the articles in a namespace
 * @param boolean $active true to only return items that are active
 * @param boolean $allowed true to check permission access to the item
 * @param string $sort string passed to the order by parameter
 * @param int|null $category_id id of the category
 * @param int|null $limit limit the number of results
 * @param int|null $start start number for pages
 *
 * @return array
 */
function sportal_get_articles($article_id = null, $active = false, $allowed = false, $sort = 'spa.title', $category_id = null, $limit = null, $start = null)
{
	global $scripturl, $context, $color_profile;

	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	// Load up an article or namespace
	if (!empty($article_id) && is_int($article_id))
	{
		$query[] = 'spa.id_article = {int:article_id}';
		$parameters['article_id'] = (int) $article_id;
	}
	elseif (!empty($article_id))
	{
		$query[] = 'spa.namespace = {string:namespace}';
		$parameters['namespace'] = $article_id;
	}

	// Want articles only from a specific category
	if (!empty($category_id))
	{
		$query[] = 'spa.id_category = {int:category_id}';
		$parameters['category_id'] = (int) $category_id;
	}

	// Checking access?
	if (!empty($allowed))
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spa.permissions');
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spc.permissions');
	}

	// Its an active article and category?
	if (!empty($active))
	{
		$query[] = 'spa.status = {int:article_status}';
		$parameters['article_status'] = 1;
		$query[] = 'spc.status = {int:category_status}';
		$parameters['category_status'] = 1;
	}

	// Limits?
	if (!empty($limit))
	{
		$parameters['limit'] = $limit;
		$parameters['start'] = !empty($start) ? $start : 0;
	}

	// Make the request
	$request = $db->query('', '
		SELECT
			spa.id_article, spa.id_category, spa.namespace AS article_namespace, spa.title, spa.body,
			spa.type, spa.date, spa.status, spa.permissions AS article_permissions, spa.views, spa.comments, spa.styles,
			spc.permissions AS category_permissions, spc.name, spc.namespace AS category_namespace,
			m.avatar, IFNULL(m.id_member, 0) AS id_author, IFNULL(m.real_name, spa.member_name) AS author_name, m.email_address,
			a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spa.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}' . (!empty($limit) ? '
		LIMIT {int:start}, {int:limit}' : ''), $parameters
	);
	$return = array();
	$member_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_author']))
		{
			$member_ids[$row['id_author']] = $row['id_author'];
		}

		$return[$row['id_article']] = array(
			'id' => $row['id_article'],
			'category' => array(
				'id' => $row['id_category'],
				'category_id' => $row['category_namespace'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
				'permissions' => $row['category_permissions'],
			),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author']
					? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>')
					: $row['author_name'],
				'avatar' => determineAvatar(array(
					'avatar' => $row['avatar'],
					'filename' => $row['filename'],
					'id_attach' => $row['id_attach'],
					'email_address' => $row['email_address'],
					'attachment_type' => $row['attachment_type'],
				)),
			),
			'article_id' => $row['article_namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'body' => $row['body'],
			'type' => $row['type'],
			'date' => $row['date'],
			'permissions' => $row['article_permissions'],
			'styles' => $row['styles'],
			'view_count' => $row['views'],
			'comment_count' => $row['comments'],
			'status' => $row['status'],
		);
	}
	$db->free_result($request);

	// Use color profiles?
	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
			{
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
			}
		}
	}

	// Return one or all
	return !empty($article_id) ? current($return) : $return;
}

/**
 * Returns the count of articles in a given category
 *
 * @param int $catid category identifier
 *
 * @return int
 */
function sportal_get_articles_in_cat_count($catid)
{
	global $context;

	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles
		WHERE status = {int:article_status}
			AND {raw:article_permissions}
			AND id_category = {int:current_category}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'permissions'),
			'current_category' => $catid,
			'limit' => 1,
		)
	);
	list ($total_articles) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_articles;
}

/**
 * Returns the number of articles in the system that a user can see
 *
 * @return int
 */
function sportal_get_articles_count()
{
	global $context;

	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
		WHERE spa.status = {int:article_status}
			AND spc.status = {int:category_status}
			AND {raw:article_permissions}
			AND {raw:category_permissions}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'category_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spa.permissions'),
			'category_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spc.permissions'),
			'limit' => 1,
		)
	);
	list ($total_articles) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_articles;
}

/**
 * Fetches the number of comments a given article has received
 *
 * @param int $id article id
 *
 * @return int
 */
function sportal_get_article_comment_count($id)
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_comments
		WHERE id_article = {int:current_article}
		LIMIT {int:limit}',
		array(
			'current_article' => $id,
			'limit' => 1,
		)
	);
	list ($total_comments) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_comments;
}

/**
 * Given an articles comment ID, fetches the comment and comment author
 *
 * @param int $id
 *
 * @return array
 */
function sportal_fetch_article_comment($id)
{
	$db = database();

	$request = $db->query('', '
		SELECT
			id_comment, id_member, body
		FROM {db_prefix}sp_comments
		WHERE id_comment = {int:comment_id}
		LIMIT {int:limit}',
		array(
			'comment_id' => $id,
			'limit' => 1,
		)
	);
	list ($comment_id, $author_id, $body) = $db->fetch_row($request);
	$db->free_result($request);

	return array($comment_id, $author_id, $body);
}

/**
 * Loads all the comments that an article has generated
 *
 * @param int|null $article_id
 * @param int|null $limit limit the number of results
 * @param int|null $start start number for pages
 *
 * @return array
 */
function sportal_get_comments($article_id = null, $limit = null, $start = null)
{
	global $scripturl, $user_info, $color_profile;

	$db = database();

	$request = $db->query('', '
		SELECT
			spc.id_comment, IFNULL(spc.id_member, 0) AS id_author,
			IFNULL(m.real_name, spc.member_name) AS author_name,
			spc.body, spc.log_time,
			m.avatar, m.email_address,
			a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}sp_comments AS spc
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spc.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
		WHERE spc.id_article = {int:article_id}
		ORDER BY spc.id_comment' . (!empty($limit) ? '
		LIMIT {int:start}, {int:limit}' : ''),
		array(
			'article_id' => (int) $article_id,
			'limit' => (int) $limit,
			'start' => (int) $start,
		)
	);
	$return = array();
	$member_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_author']))
		{
			$member_ids[$row['id_author']] = $row['id_author'];
		}

		$return[$row['id_comment']] = array(
			'id' => $row['id_comment'],
			'body' => parse_bbc($row['body']),
			'time' => htmlTime($row['log_time']),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author']
					? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>')
					: $row['author_name'],
				'avatar' => determineAvatar(array(
					'avatar' => $row['avatar'],
					'filename' => $row['filename'],
					'id_attach' => $row['id_attach'],
					'email_address' => $row['email_address'],
					'attachment_type' => $row['attachment_type'],
				)),
			),
			'can_moderate' => allowedTo('sp_admin') || allowedTo('sp_manage_articles') || (!$user_info['is_guest'] && $user_info['id'] == $row['id_author']),
		);
	}
	$db->free_result($request);

	// Colorization
	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
			{
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
			}
		}
	}

	return $return;
}

/**
 * Creates a new comment response to a published article
 *
 * @param int $article_id id of the article commented on
 * @param string $body text of the comment
 *
 * @return null
 */
function sportal_create_article_comment($article_id, $body)
{
	global $user_info;

	$db = database();

	$db->insert('',
		'{db_prefix}sp_comments',
		array(
			'id_article' => 'int',
			'id_member' => 'int',
			'member_name' => 'string',
			'log_time' => 'int',
			'body' => 'string',
		),
		array(
			$article_id,
			$user_info['id'],
			$user_info['name'],
			time(),
			$body,
		),
		array('id_comment')
	);

	// Increase the comment count
	sportal_recount_comments($article_id);
}

/**
 * Edits an article comment that has already been made
 *
 * @param int $comment_id comment id to edit
 * @param string $body replacement text
 */
function sportal_modify_article_comment($comment_id, $body)
{
	$db = database();

	$db->query('', '
		UPDATE {db_prefix}sp_comments
		SET body = {text:body}
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
			'body' => $body,
		)
	);
}

/**
 * Removes an comment from an article
 *
 * @param int $comment_id comment it
 *
 * @return null
 */
function sportal_delete_article_comment($comment_id)
{
	global $context, $user_info;

	$db = database();

	$request = $db->query('', '
		SELECT id_comment, id_article, id_member
		FROM {db_prefix}sp_comments
		WHERE id_comment = {int:comment_id}
		LIMIT {int:limit}',
		array(
			'comment_id' => $comment_id,
			'limit' => 1,
		)
	);
	list ($comment_id, $article_id, $author_id) = $db->fetch_row($request);
	$db->free_result($request);

	// You do have a right to remove the comment?
	if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
	{
		return false;
	}

	// Poof ... gone
	$db->query('', '
		DELETE FROM {db_prefix}sp_comments
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
		)
	);

	// One less comment
	sportal_recount_comments($article_id);

	return true;
}

/**
 * Recounts all comments made on an article and updates the DB with the new value
 *
 * @param int $article_id
 *
 * @return null
 */
function sportal_recount_comments($article_id)
{
	$db = database();

	// How many comments were made for this article
	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_comments
		WHERE id_article = {int:article_id}
		LIMIT {int:limit}',
		array(
			'article_id' => $article_id,
			'limit' => 1,
		)
	);
	list ($comments) = $db->fetch_row($request);
	$db->free_result($request);

	// Update the article comment count
	$db->query('', '
		UPDATE {db_prefix}sp_articles
		SET comments = {int:comments}
		WHERE id_article = {int:article_id}',
		array(
			'article_id' => $article_id,
			'comments' => $comments,
		)
	);
}