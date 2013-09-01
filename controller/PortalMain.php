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

function sportal_main()
{
	global $context;

	$context['page_title'] = $context['forum_name'];

	if (isset($context['page_title_html_safe']))
		$context['page_title_html_safe'] = Util::htmlspecialchars(un_htmlspecialchars($context['page_title']));

	if (!empty($context['standalone']))
		setupMenuContext();

	$actions = array(
		'articles' => array('PortalArticles.php', 'sportal_articles'),
		'categories' => array('PortalCategories.php', 'sportal_categories'),
		'credits' => array('', 'sportal_credits'),
		'index' => array('', 'sportal_index'),
		'pages' => array('PortalPages.php', 'sportal_pages'),
		'shoutbox' => array('PortalShoutbox.php', 'sportal_shoutbox'),
	);

	if (!isset($_REQUEST['sa']) || !isset($actions[$_REQUEST['sa']]))
		$_REQUEST['sa'] = 'index';

	if (!empty($actions[$_REQUEST['sa']][0]))
		require_once(SOURCEDIR . '/' . $actions[$_REQUEST['sa']][0]);

	$actions[$_REQUEST['sa']][1]();
}

function sportal_index()
{
	global $smcFunc, $context;

	$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC');

	foreach ($context['articles'] as $article)
	{
		if (($cutoff = $smcFunc['strpos']($article['body'], '[cutoff]')) !== false)
			$article['body'] = $smcFunc['substr']($article['body'], 0, $cutoff);

		$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
		$context['articles'][$article['id']]['date'] = forum_time($article['date']);
	}

	$context['sub_template'] = 'portal_index';
}

function sportal_credits()
{
	global $context, $txt;

	require_once(ADMINDIR . '/PortalAdminMain.php');
	loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

	sportal_information(false);

	$context['page_title'] = $txt['sp-info_title'];
	$context['sub_template'] = 'information';
}