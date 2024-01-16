<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.3
 */

use BBC\Codes;

/**
 * Portal "management" controller.
 *
 * This class holds all of SimplePortals integration hooks.
 *
 * @package SimplePortal
 */
class ManageSPortalModule_Controller extends Action_Controller
{
	/**
	 * Default method.
	 * Requires admin_forum permissions
	 * Required by Action_Controller
	 *
	 * @uses SPortal language file
	 */
	public function action_index()
	{
		isAllowedTo('admin_forum');
		loadLanguage('SPortal');
	}

	/**
	 * Used to add the Portal entry to the Core Features list.
	 *
	 * @param array $core_features The core features array
	 */
	public static function addCoreFeature(&$core_features)
	{
		isAllowedTo('admin_forum');
		loadLanguage('SPortalAdmin');

		$core_features['pt'] = array(
			'url' => 'action=admin;area=portalconfig',
			'setting_callback' => function ($value) {
				// Enabling
				if ($value)
				{
					Hooks::instance()->enableIntegration('Portal_Integrate');
					return array('disable_sp' => '');
				}

				// Disabling
				Hooks::instance()->disableIntegration('Portal_Integrate');
				return array('disable_sp' => 1);
			},
		);
	}

	/******************************INTEGRATION HOOKS*******************************************/

	/**
	 * Adds [spattach] BBC code tags for use with article images.  Mostly the same as ILA [attach]
	 *
	 * @param array $additional_bbc
	 */
	public static function sp_integrate_additional_bbc(&$additional_bbc)
	{
		global $scripturl, $modSettings, $txt;

		// Generally we don't want to render inside of these tags ...
		$disallow = array(
			'quote' => 1,
			'code' => 1,
			'nobbc' => 1,
			'html' => 1,
			'php' => 1,
		);

		// Disabled tags?
		$disabledBBC = empty($modSettings['disabledBBC']) ? array() : explode(',', $modSettings['disabledBBC']);
		$disabled = in_array('spattach', $disabledBBC, true);

		// Add simplePortal ILA codes
		$additional_bbc = array_merge($additional_bbc, array(
			// Just a simple attach
			array(
				Codes::ATTR_TAG => 'spattach',
				Codes::ATTR_TYPE => Codes::TYPE_UNPARSED_CONTENT,
				Codes::ATTR_DISABLED => $disabled,
				Codes::ATTR_CONTENT => '$1',
				Codes::ATTR_VALIDATE => $disabled ? null : self::validate_plain(),
				Codes::ATTR_DISALLOW_PARENTS => $disallow,
				Codes::ATTR_DISABLED_CONTENT => '<a href="' . $scripturl . '?action=portal;sa=spattach;attach=$1">(' . $txt['link'] . '-$1)</a> ',
				Codes::ATTR_BLOCK_LEVEL => false,
				Codes::ATTR_AUTOLINK => false,
				Codes::ATTR_LENGTH => 8,
			),
			array(
				Codes::ATTR_TAG => 'spattach',
				Codes::ATTR_TYPE => Codes::TYPE_UNPARSED_CONTENT,
				Codes::ATTR_PARAM => array(
					'type' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_MATCH => '(thumb)',
					),
				),
				Codes::ATTR_DISABLED => $disabled,
				Codes::ATTR_CONTENT => '$1',
				Codes::ATTR_VALIDATE => $disabled ? null : self::validate_plain(),
				Codes::ATTR_DISALLOW_PARENTS => $disallow,
				Codes::ATTR_DISABLED_CONTENT => '<a href="' . $scripturl . '?action=portal;sa=spattach;attach=$1">(' . $txt['link'] . '-$1)</a> ',
				Codes::ATTR_BLOCK_LEVEL => false,
				Codes::ATTR_AUTOLINK => false,
				Codes::ATTR_LENGTH => 8,
			),
			// Require a width with optional height/align to allow use of full image and/or ;thumb
			array(
				Codes::ATTR_TAG => 'spattach',
				Codes::ATTR_TYPE => Codes::TYPE_UNPARSED_CONTENT,
				Codes::ATTR_PARAM => array(
					'width' => array(
						Codes::PARAM_ATTR_VALIDATE => self::validate_width(),
						Codes::PARAM_ATTR_MATCH => '(\d+)',
					),
					'height' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_VALUE => 'max-height:$1px;',
						Codes::PARAM_ATTR_MATCH => '(\d+)',
					),
					'align' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_VALUE => 'float$1',
						Codes::PARAM_ATTR_MATCH => '(right|left|center)',
					),
				),
				Codes::ATTR_CONTENT => '<a id="link_$1" class="sp_attach" data-lightboximage="$1" data-lightboxmessage="0" href="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1;image"><img src="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1{width}{height}" alt="" class="bbc_img {align}" /></a>',
				Codes::ATTR_VALIDATE => self::validate_options(),
				Codes::ATTR_DISALLOW_PARENTS => $disallow,
				Codes::ATTR_BLOCK_LEVEL => false,
				Codes::ATTR_AUTOLINK => false,
				Codes::ATTR_LENGTH => 8,
			),
			// Require a height with optional width/align to allow removal of ;thumb
			array(
				Codes::ATTR_TAG => 'spattach',
				Codes::ATTR_TYPE => Codes::TYPE_UNPARSED_CONTENT,
				Codes::ATTR_PARAM => array(
					'height' => array(
						Codes::PARAM_ATTR_VALIDATE => self::validate_height(),
						Codes::PARAM_ATTR_MATCH => '(\d+)',
					),
					'width' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_VALUE => 'width:100%;max-width:$1px;',
						Codes::PARAM_ATTR_MATCH => '(\d+)',
					),
					'align' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_VALUE => 'float$1',
						Codes::PARAM_ATTR_MATCH => '(right|left|center)',
					),
				),
				Codes::ATTR_CONTENT => '<a id="link_$1" class="sp_attach" data-lightboximage="$1" data-lightboxmessage="{article}" href="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1;image"><img src="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1{height}{width}" alt="" class="bbc_img {align}" /></a>',
				Codes::ATTR_VALIDATE => self::validate_options(),
				Codes::ATTR_DISALLOW_PARENTS => $disallow,
				Codes::ATTR_BLOCK_LEVEL => false,
				Codes::ATTR_AUTOLINK => false,
				Codes::ATTR_LENGTH => 8,
			),
			// Just an align ?
			array(
				Codes::ATTR_TAG => 'spattach',
				Codes::ATTR_TYPE => Codes::TYPE_UNPARSED_CONTENT,
				Codes::ATTR_PARAM => array(
					'align' => array(
						Codes::PARAM_ATTR_VALUE => 'float$1',
						Codes::PARAM_ATTR_MATCH => '(right|left|center)',
					),
					'type' => array(
						Codes::PARAM_ATTR_OPTIONAL => true,
						Codes::PARAM_ATTR_VALUE => ';$1',
						Codes::PARAM_ATTR_MATCH => '(thumb|image)',
					),
				),
				Codes::ATTR_CONTENT => '<a id="link_$1" class="sp_attach" data-lightboximage="$1" data-lightboxmessage="{article}" href="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1;image"><img src="' . $scripturl . '?action=portal;sa=spattach;article={article};attach=$1{type}" alt="" class="bbc_img {align}" /></a>',
				Codes::ATTR_VALIDATE => self::validate_options(),
				Codes::ATTR_DISALLOW_PARENTS => $disallow,
				Codes::ATTR_BLOCK_LEVEL => false,
				Codes::ATTR_AUTOLINK => false,
				Codes::ATTR_LENGTH => 8,
			),
		));
	}

	/**
	 * Used when the optional width parameter is set
	 *
	 * - Determines the best image, full or thumbnail, based on ILA width desired
	 * - Used as PARAM_ATTR_VALIDATE function
	 *
	 * @return Closure
	 */
	public static function validate_width()
	{
		global $modSettings;

		return static function ($data) use ($modSettings) {
			if (!empty($modSettings['attachmentThumbWidth']) && $data <= $modSettings['attachmentThumbWidth'])
			{
				return ';thumb" style="width:100%;max-width:' . $data . 'px;';
			}

			return '" style="width:100%;max-width:' . $data . 'px;';
		};
	}

	/**
	 * Used when the optional height parameter is set and no width is set
	 *
	 * - Determines the best image, full or thumbnail, based on desired ILA height
	 * - Used as PARAM_ATTR_VALIDATE function
	 *
	 * @return Closure
	 */
	public static function validate_height()
	{
		global $modSettings;

		return static function ($data) use ($modSettings) {
			if (!empty($modSettings['attachmentThumbHeight']) && $data <= $modSettings['attachmentThumbHeight'])
			{
				return ';thumb" style="max-height:' . $data . 'px;';
			}

			return '" style="max-height:' . $data . 'px;';
		};
	}

	/**
	 * This provides for some control for "plain" tags
	 *
	 * - Determines if the ILA is an image or not
	 * - Sets the lightbox attributes if an image is identified
	 * - Keeps track of attachment usage to prevent displaying below the post
	 *
	 * @return Closure
	 */
	public static function validate_plain()
	{
		global $user_info, $scripturl, $context, $modSettings;

		return static function (&$tag, &$data) use ($user_info, $scripturl, &$context, $modSettings) {
			$num = $data;
			$is_image = array();
			$preview = strpos($data, 'post_tmp_' . $user_info['id'] . '_');
			$article = $context['article']['id'] ?? 0;

			// Not a preview, then sanitize the attach id and determine the actual type
			if ($preview === false)
			{
				$num = (int) $data;
				$is_image = isArticleAttachmentImage($num);
			}

			// An image will get the light box treatment
			if (!empty($is_image['is_image']) || $preview !== false)
			{
				$type = !empty($modSettings['attachmentThumbnails']) ? ';thumb' : '';
				$data = '<a id="link_' . $num . '" data-lightboximage="' . $num . '" data-lightboxmessage="{article}" href="' . $scripturl . '?action=portal;sa=spattach;article=' . $article . ';attach=' . $num . ';image' . '"><img src="' . $scripturl . '?action=portal;sa=spattach;article=' . $article . ';attach=' . $num . $type . '" alt="" class="bbc_img" /></a>';
			}
			else
			{
				// Not an image, determine a mime or use a default thumbnail
				require_once(SUBSDIR . '/Attachments.subs.php');
				$check = returnMimeThumb(($is_image['fileext'] ?? ''), true);

				if ($is_image === false)
				{
					$data = '<img src="' . $check . '" alt="" class="bbc_img" />';
				}
				else
				{
					$data = '<a class="sp_attach" href="' . $scripturl . '?action=portal;sa=spattach;article=' . $article . ';attach=' . $num . '"><img src="' . $check . '" alt="' . $is_image['filename'] . '" class="bbc_img" /></a>';
				}
			}

			$context['ila_dont_show_attach_below'][] = $num;
			$context['ila_dont_show_attach_below'] = array_unique($context['ila_dont_show_attach_below']);
		};
	}

	/**
	 * For tags with options (width / height / align)
	 *
	 * - Keeps track of attachment usage to prevent displaying below the post
	 *
	 * @return Closure
	 */
	public static function validate_options()
	{
		global $context;

		return static function (&$tag, &$data) use (&$context) {
			$article = $context['article']['id'] ?? 0;

			// Not a preview, then sanitize the attach id
			if (strpos($data, 'post_tmp_') === false)
			{
				$data = (int) $data;
			}

			$tag[Codes::ATTR_CONTENT] = str_replace('{article}', $article, $tag[Codes::ATTR_CONTENT]);

			$context['ila_dont_show_attach_below'][] = $data;
			$context['ila_dont_show_attach_below'] = array_unique($context['ila_dont_show_attach_below']);
		};
	}

	/**
	 * Integration hook integrate_setup_allow
	 *
	 * Called from Theme.php setupMenuContext(), used to determine if the admin button is visible for a given
	 * member as its needed to access certain sub menus
	 */
	public static function sp_integrate_setup_allow()
	{
		global $context;

		$context['allow_admin'] = $context['allow_admin'] || allowedTo(array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox', 'sp_manage_profiles', 'sp_manage_categories'));
	}

	/**
	 * integration hook integrate_actions
	 * Called from dispatcher.class, used to add in custom actions
	 *
	 * @param array $actions
	 */
	public static function sp_integrate_actions(&$actions)
	{
		global $context;

		if (!empty($context['disable_sp']))
		{
			return;
		}

		$actions['forum'] = array('BoardIndex.controller.php', 'BoardIndex_Controller', 'action_boardindex');
		$actions['portal'] = array('PortalMain.controller.php', 'PortalMain_Controller', 'action_index');
		$actions['shoutbox'] = array('PortalShoutbox.controller.php', 'Shoutbox_Controller', 'action_sportal_shoutbox');
	}

	/**
	 * Admin hook, integrate_admin_areas, called from Menu.subs
	 * adds the admin menu
	 *
	 * @param array $admin_areas
	 */
	public static function sp_integrate_admin_areas(&$admin_areas)
	{
		global $txt, $context;

		require_once(SUBSDIR . '/Portal.subs.php');
		loadLanguage('SPortalAdmin');
		loadLanguage('SPortal');

		$temp = $admin_areas;
		$admin_areas = array();

		foreach ($temp as $area => $data)
		{
			$admin_areas[$area] = $data;

			// Add in our admin menu option after layout aka forum
			if ($area === 'layout')
			{
				$admin_areas['portal'] = array(
					'enabled' => in_array('pt', $context['admin_features']),
					'title' => $txt['sp-adminCatTitle'],
					'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox', 'sp_manage_profiles', 'sp_manage_categories'),
					'areas' => array(
						'portalconfig' => array(
							'label' => $txt['sp-adminConfiguration'],
							'file' => 'PortalAdminMain.controller.php',
							'controller' => 'ManagePortalConfig_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_corefeatures',
							'permission' => array('sp_admin', 'sp_manage_settings'),
							'subsections' => array(
								'information' => array($txt['sp-info_title']),
								'generalsettings' => array($txt['sp-adminGeneralSettingsName']),
								'blocksettings' => array($txt['sp-adminBlockSettingsName']),
								'articlesettings' => array($txt['sp-adminArticleSettingsName']),
							),
						),
						'portalblocks' => array(
							'label' => $txt['sp-blocksBlocks'],
							'file' => 'PortalAdminBlocks.controller.php',
							'controller' => 'ManagePortalBlocks_Controller',
							'function' => 'action_index',
							'icon' => 'blocks.png',
							'permission' => array('sp_admin', 'sp_manage_blocks'),
							'subsections' => array(
								'list' => array($txt['sp-adminBlockListName']),
								'add' => array($txt['sp-adminBlockAddName']),
								'header' => array($txt['sp-positionHeader']),
								'left' => array($txt['sp-positionLeft']),
								'top' => array($txt['sp-positionTop']),
								'bottom' => array($txt['sp-positionBottom']),
								'right' => array($txt['sp-positionRight']),
								'footer' => array($txt['sp-positionFooter']),
							),
						),
						'portalarticles' => array(
							'label' => $txt['sp_admin_articles_title'],
							'file' => 'PortalAdminArticles.controller.php',
							'controller' => 'ManagePortalArticles_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_news',
							'permission' => array('sp_admin', 'sp_manage_articles'),
							'subsections' => array(
								'list' => array($txt['sp_admin_articles_list']),
								'add' => array($txt['sp_admin_articles_add']),
							),
						),
						'portalcategories' => array(
							'label' => $txt['sp_admin_categories_title'],
							'file' => 'PortalAdminCategories.controller.php',
							'controller' => 'ManagePortalCategories_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_server',
							'permission' => array('sp_admin', 'sp_manage_categories'),
							'subsections' => array(
								'list' => array($txt['sp_admin_categories_list']),
								'add' => array($txt['sp_admin_categories_add']),
							),
						),
						'portalpages' => array(
							'label' => $txt['sp_admin_pages_title'],
							'file' => 'PortalAdminPages.controller.php',
							'controller' => 'ManagePortalPages_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_posts',
							'permission' => array('sp_admin', 'sp_manage_pages'),
							'subsections' => array(
								'list' => array($txt['sp_admin_pages_list']),
								'add' => array($txt['sp_admin_pages_add']),
							),
						),
						'portalshoutbox' => array(
							'label' => $txt['sp_admin_shoutbox_title'],
							'file' => 'PortalAdminShoutbox.controller.php',
							'controller' => 'ManagePortalShoutbox_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_smiley',
							'permission' => array('sp_admin', 'sp_manage_shoutbox'),
							'subsections' => array(
								'list' => array($txt['sp_admin_shoutbox_list']),
								'add' => array($txt['sp_admin_shoutbox_add']),
							),
						),
						/* Shhh its a secret for now, not done yet ;)
						'portalmenus' => array(
							'label' => $txt['sp_admin_menus_title'],
							'file' => 'PortalAdminMenus.controller.php',
							'controller' => 'ManagePortalMenus_Controller',
							'function' => 'action_index',
							'icon' => 'menus.png',
							'permission' => array('sp_admin', 'sp_manage_menus'),
							'subsections' => array(
								'listmainitem' => array($txt['sp_admin_menus_main_item_list']),
								'addmainitem' => array($txt['sp_admin_menus_main_item_add']),
								'listcustommenu' => array($txt['sp_admin_menus_custom_menu_list']),
								'addcustommenu' => array($txt['sp_admin_menus_custom_menu_add']),
								'addcustomitem' => array($txt['sp_admin_menus_custom_item_add'], 'enabled' => !empty($_REQUEST['sa']) && $_REQUEST['sa'] === 'listcustomitem'),
							),
						),
						*/
						'portalprofiles' => array(
							'label' => $txt['sp_admin_profiles_title'],
							'file' => 'PortalAdminProfiles.controller.php',
							'controller' => 'ManagePortalProfile_Controller',
							'function' => 'action_index',
							'icon' => 'transparent.png',
							'class' => 'admin_img_permissions',
							'permission' => array('sp_admin', 'sp_manage_profiles'),
							'subsections' => array(
								'listpermission' => array($txt['sp_admin_permission_profiles_list']),
								'liststyle' => array($txt['sp_admin_style_profiles_list']),
								'listvisibility' => array($txt['sp_admin_visibility_profiles_list']),
							),
						),
					),
				);
			}
		}
	}

	/**
	 * Permissions hook, integrate_load_permissions, called from ManagePermissions.php
	 * used to add new permissions
	 *
	 * @param array $permissionGroups
	 * @param array $permissionList
	 * @param array $leftPermissionGroups
	 * @param array $hiddenPermissions
	 * @param array $relabelPermissions
	 */
	public static function sp_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
	{
		$permissionList['membergroup'] = array_merge($permissionList['membergroup'], array(
			'sp_admin' => array(false, 'sp', 'sp'),
			'sp_manage_settings' => array(false, 'sp', 'sp'),
			'sp_manage_blocks' => array(false, 'sp', 'sp'),
			'sp_manage_articles' => array(false, 'sp', 'sp'),
			'sp_manage_pages' => array(false, 'sp', 'sp'),
			'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
			'sp_manage_menus' => array(false, 'sp', 'sp'),
			'sp_manage_profiles' => array(false, 'sp', 'sp'),
		));

		$permissionGroups['membergroup'][] = 'sp';

		$leftPermissionGroups[] = 'sp';
	}

	/**
	 * Whos online hook, integrate_whos_online, called from who.subs
	 * translates custom actions to allow us to show what area a user is in
	 *
	 * @param array $actions
	 *
	 * @return string|array
	 */
	public static function sp_integrate_whos_online($actions)
	{
		global $scripturl, $modSettings, $txt;

		$data = null;

		require_once(SUBSDIR . '/Portal.subs.php');
		loadLanguage('SPortal');

		// This may miss if needed for the first action ... need to improve the hook or
		// find another location
		if ($modSettings['sp_portal_mode'] == 1)
		{
			$txt['who_index'] = sprintf($txt['sp_who_index'], $scripturl);
			$txt['whoall_forum'] = sprintf($txt['sp_who_forum'], $scripturl);
		}
		elseif ($modSettings['sp_portal_mode'] == 3)
		{
			$txt['whoall_portal'] = sprintf($txt['sp_who_index'], $scripturl);
		}

		// If its a portal action, lets check it out.
		if (isset($actions['page']))
		{
			$data = self::sp_whos_online_page($actions['page']);
		}
		elseif (isset($actions['article']))
		{
			$data = self::sp_whos_online_article($actions['article']);
		}

		return $data;
	}

	/**
	 * Page online hook, helper function to determine the page a user is viewing
	 *
	 * @param string $page_id
	 *
	 * @return string
	 */
	public static function sp_whos_online_page($page_id)
	{
		global $scripturl, $txt, $context;

		$db = database();

		$data = $txt['who_hidden'];
		$numeric_ids = '';
		$string_ids = '';
		$page_where = '';

		if (is_numeric($page_id))
		{
			$numeric_ids = (int) $page_id;
		}
		else
		{
			$string_ids = $page_id;
		}

		if (!empty($numeric_ids))
		{
			$page_where = 'id_page IN ({int:numeric_ids})';
		}

		if (!empty($string_ids))
		{
			$page_where = 'namespace IN ({string:string_ids})';
		}

		$query = sprintf($context['SPortal']['permissions']['query'], 'permissions');

		$result = $db->query('', '
		SELECT
			id_page, namespace, title, permissions
		FROM {db_prefix}sp_pages
		WHERE ' . $page_where . ' AND ' . $query . '
		LIMIT {int:limit}',
			array(
				'numeric_ids' => $numeric_ids,
				'string_ids' => $string_ids,
				'limit' => 1,
			)
		);
		$page_data = '';
		while ($row = $db->fetch_assoc($result))
		{
			$page_data = array(
				'id' => $row['id_page'],
				'namespace' => $row['namespace'],
				'title' => $row['title'],
			);
		}
		$db->free_result($result);

		if (!empty($page_data))
		{
			if (isset($page_data['id']))
			{
				$data = sprintf($txt['sp_who_page'], $page_data['id'], censor($page_data['title']), $scripturl);
			}

			if (isset($page_data['namespace']))
			{
				$data = sprintf($txt['sp_who_page'], $page_data['namespace'], censor($page_data['title']), $scripturl);
			}
		}

		return $data;
	}

	/**
	 * Article online hook, helper function to determine the page a user is viewing
	 *
	 * @param string $article_id
	 *
	 * @return string
	 */
	public static function sp_whos_online_article($article_id)
	{
		global $scripturl, $txt, $context;

		$db = database();

		$data = $txt['who_hidden'];
		$numeric_ids = '';
		$string_ids = '';
		$article_where = '';

		if (is_numeric($article_id))
		{
			$numeric_ids = (int) $article_id;
		}
		else
		{
			$string_ids = $article_id;
		}

		if (!empty($numeric_ids))
		{
			$article_where = 'id_article IN ({int:numeric_ids})';
		}

		if (!empty($string_ids))
		{
			$article_where = 'namespace IN ({string:string_ids})';
		}

		$query = sprintf($context['SPortal']['permissions']['query'], 'permissions');

		$result = $db->query('', '
		SELECT
			id_article, namespace, title, permissions
		FROM {db_prefix}sp_articles
		WHERE ' . $article_where . ' AND ' . $query . '
		LIMIT {int:limit}',
			array(
				'numeric_ids' => $numeric_ids,
				'string_ids' => $string_ids,
				'limit' => 1,
			)
		);
		$article_data = '';
		while ($row = $db->fetch_assoc($result))
		{
			$article_data = array(
				'id' => $row['id_article'],
				'namespace' => $row['namespace'],
				'title' => $row['title'],
			);
		}
		$db->free_result($result);

		if (!empty($article_data))
		{
			if (isset($article_data['id']))
			{
				$data = sprintf($txt['sp_who_article'], $article_data['id'], censor($article_data['title']), $scripturl);
			}

			if (isset($article_data['namespace']))
			{
				$data = sprintf($txt['sp_who_article'], $article_data['namespace'], censor($article_data['title']), $scripturl);
			}
		}

		return $data;
	}

	/**
	 * Theme hook, integrate_init_theme, called from load.php
	 * Used to initialize main portal functions as soon as the theme is started
	 */
	public static function sp_integrate_init_theme()
	{
		// Need to run init to determine if we are even active
		require_once(SUBSDIR . '/Portal.subs.php');
		sportal_init();
	}

	/**
	 * Help hook, integrate_quickhelp, called from help.controller.php
	 * Used to add in additional help languages for use in the admin quickhelp
	 */
	public static function sp_integrate_quickhelp()
	{
		require_once(SUBSDIR . '/Portal.subs.php');

		// Load the Simple Portal Help file.
		loadLanguage('SPortalHelp');
		loadLanguage('SPortalAdmin');
	}

	/**
	 * Integration hook integrate_buffer, called from ob_exit via call_integration_buffer
	 * Used to modify the output buffer before its sent, here we add in our copyright
	 *
	 * @param string $tourniquet
	 *
	 * @return string
	 */
	public static function sp_integrate_buffer($tourniquet)
	{
		global $context, $modSettings, $forum_copyright;

		if ((ELK === 'SSI' && empty($context['standalone']))
			|| empty($modSettings['sp_portal_mode'])
			|| !Template_Layers::instance()->hasLayers())
		{
			return $tourniquet;
		}

		// Don't display copyright for things like SSI.
		if (!defined('FORUM_VERSION'))
		{
			return $tourniquet;
		}

		if (method_exists('Util', 'strftime'))
		{
			$fix = str_replace('{version}', SPORTAL_VERSION, '<a href="https://github.com/SimplePortal" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-' . Util::strftime('%Y', time()) . '</a>');
		}
		else
		{
			$fix = str_replace('{version}', SPORTAL_VERSION, '<a href="https://github.com/SimplePortal" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-' . strftime('%Y', time()) . '</a>');
		}

		if (strpos($tourniquet, $fix) !== false)
			return $tourniquet;

		// Append our cp notice at the end of the line
		$finds = array(
			$forum_copyright,
		);
		$replaces = array(
			sprintf($forum_copyright, FORUM_VERSION) . ' | ' . $fix,
		);

		$tourniquet = str_replace($finds, $replaces, $tourniquet);

		// Can't find it for some reason so we add it at the end
		if (strpos($tourniquet, $fix) === false)
		{
			$fix = '<div style="text-align: center; width: 100%; font-size: x-small; margin-bottom: 5px;">' . $fix . '</div></body></html>';
			$tourniquet = preg_replace('~</body>\s*</html>~', $fix, $tourniquet);
		}

		return $tourniquet;
	}

	/**
	 * Menu Button hook, integrate_menu_buttons, called from subs.php
	 * used to add top menu buttons
	 *
	 * @param array $buttons
	 */
	public static function sp_integrate_menu_buttons(&$buttons)
	{
		global $txt, $scripturl, $modSettings, $context;

		require_once(SUBSDIR . '/Portal.subs.php');
		loadLanguage('SPortal');

		// Set the right portalurl based on what integration mode the portal is using
		$modSettings['sp_portal_mode'] = (int) $modSettings['sp_portal_mode'];
		if ($modSettings['sp_portal_mode'] === 1 && empty($context['disable_sp']))
		{
			$sportal_url = $scripturl . '?action=forum';
		}
		elseif ($modSettings['sp_portal_mode'] === 3 && empty($context['disable_sp']))
		{
			$buttons['home']['href'] = $modSettings['sp_standalone_url'];
			$sportal_url = $modSettings['sp_standalone_url'];
		}
		else
		{
			return;
		}

		theme()->addCSSRules("
	.i-spgroup::before {
		content: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23555555' viewBox='0 0 36 32'%3E%3Cpath d='M24 24v-1.6a9 9 0 0 0 4-7.4c0-5 0-9-6-9s-6 4-6 9a9 9 0 0 0 4 7.4v1.7C13.2 24.6 8 28 8 32h28c0-4-5.2-7.4-12-8z'/%3E%3Cpath d='M10.2 24.9a19 19 0 0 1 6.3-2.6 11.3 11.3 0 0 1-2.8-7.3c0-2.7 0-5.2 1-7.3 1-2 2.6-3.3 5-3.7-.5-2.4-2-4-5.7-4-6 0-6 4-6 9a9 9 0 0 0 4 7.4v1.7C5.2 18.6 0 22 0 26h8.7l1.5-1.1z'/%3E%3C/svg%3E\");
	}");

		// Define the new menu item(s), show it for modes 1 and 3 only
		$buttons = elk_array_insert($buttons, 'home', array(
			'forum' => array(
				'title' => empty($txt['sp-forum']) ? 'Forum' : $txt['sp-forum'],
				'data-icon' => 'i-spgroup',
				'href' => $sportal_url,
				'show' => empty($context['disable_sp']),
				'sub_buttons' => array(),
				'action_hook' => true,
			),
		), 'after');
	}

	/**
	 * Redirection hook, integrate_redirect, called from subs.php redirectexit()
	 *
	 * @param string $setLocation
	 *
	 * @uses redirectexit_callback in subs.php
	 */
	public static function sp_integrate_redirect(&$setLocation)
	{
		global $modSettings, $context, $scripturl;

		// Set the default redirect location as the forum or the portal.
		if ($scripturl == $setLocation && ($modSettings['sp_portal_mode'] == 1 || $modSettings['sp_portal_mode'] == 3))
		{
			// Redirect the user to the forum.
			if (!empty($modSettings['sp_disableForumRedirect']))
			{
				$setLocation = '?action=forum';
			}
			// Redirect the user to the SSI.php standalone portal.
			elseif ($modSettings['sp_portal_mode'] == 3)
			{
				$setLocation = $context['portal_url'];
			}
		}
		// If we are using Search engine friendly URLs then lets do the same for page links
		elseif (!empty($modSettings['queryless_urls'])
			&& (!empty($context['server']['is_apache']) || !empty($context['server']['is_lighttpd']) || !empty($context['server']['is_litespeed']))
			&& (empty($context['server']['is_cgi']) || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1))
		{
			if (defined('SID') && SID !== '')
			{
				$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
			}
			else
			{
				$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
			}
		}
	}

	/**
	 * A single check for the sake of remove yet another code edit. :P
	 * integrate_action_boardindex_after
	 */
	public static function sp_integrate_boardindex()
	{
		global $context, $modSettings, $scripturl;

		if (!empty($_GET) && $_GET !== array('action' => 'forum'))
		{
			$context['robot_no_index'] = true;
		}

		// Set the board index canonical URL correctly when portal mode is set to front page
		if (!empty($modSettings['sp_portal_mode']) && $modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']))
		{
			$context['canonical_url'] = $scripturl . '?action=forum';
		}
	}

	/**
	 * Dealing with the current action?
	 *
	 * @param string $current_action
	 */
	public static function sp_integrate_current_action(&$current_action)
	{
		global $modSettings, $context;

		// If it is home, it may be something else
		if ($current_action === 'home')
		{
			$current_action = (int) $modSettings['sp_portal_mode'] === 3 && empty($context['standalone']) && empty($context['disable_sp'])
				? 'forum' : 'home';
		}

		if (empty($context['disable_sp']) && ((isset($_GET['board']) || isset($_GET['topic']) || in_array($context['current_action'], array('unread', 'unreadreplies', 'collapse', 'recent', 'stats', 'who'))) && in_array((int) $modSettings['sp_portal_mode'], array(1, 3), true)))
		{
			$current_action = 'forum';
		}
	}

	/**
	 * Add in to the xml array our sortable action
	 * integrate_sa_xmlhttp
	 *
	 * @param array $subActions
	 */
	public static function sp_integrate_xmlhttp(&$subActions)
	{
		$subActions['blockorder'] = array('controller' => 'ManagePortalBlocks_Controller', 'file' => 'PortalAdminBlocks.controller.php', 'function' => 'action_blockorder', 'permission' => 'admin_forum');
		$subActions['userblockorder'] = array('controller' => 'PortalMain_Controller', 'dir' => CONTROLLERDIR, 'file' => 'PortalMain.controller.php', 'function' => 'action_userblockorder');
	}

	/**
	 * Add permissions that guest should never be able to have
	 * integrate_load_illegal_guest_permissions called from Permission.subs.php
	 */
	public static function sp_integrate_load_illegal_guest_permissions()
	{
		global $context;

		// Guests shouldn't be able to have any portal specific permissions.
		$context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], array(
			'sp_admin',
			'sp_manage_settings',
			'sp_manage_blocks',
			'sp_manage_articles',
			'sp_manage_pages',
			'sp_manage_shoutbox',
			'sp_manage_profiles',
			'sp_manage_menus',
			'sp_manage_categories'
		));
	}

	/**
	 * Subs hook, integrate_pre_parsebbc
	 *
	 * - Allow addons access before entering the main parse_bbc loop
	 * - Prevents cutoff tag from bleeding into the message
	 *
	 * @param string $message
	 */
	public static function sp_integrate_pre_parsebbc(&$message)
	{
		if (strpos($message, '[cutoff]') !== false)
		{
			$message = str_replace('[cutoff]', '', $message);
		}
	}
}