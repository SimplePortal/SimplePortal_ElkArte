<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
	die('No access...');

/**
 * SimplePortal Blocks Administration controller class.
 * This class handles the adding/editing/listing of blocks
 */
class ManagePortalBlocks_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have block permissions
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_blocks');

		// We'll need the utility functions from here.
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminBlocks');

		$subActions = array(
			'list' => array($this, 'action_list'),
			'header' => array($this, 'action_list'),
			'left' => array($this, 'action_list'),
			'top' => array($this, 'action_list'),
			'bottom' => array($this, 'action_list'),
			'right' => array($this, 'action_list'),
			'footer' => array($this, 'action_list'),
			'add' => array($this, 'action_edit'),
			'edit' => array($this, 'action_edit'),
			'delete' => array($this, 'action_delete'),
			'move' => array($this, 'action_move'),
			'statechange' => array($this, 'action_state_change'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_blocks');

		// Tabs for the menu
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp-blocksBlocks'],
			'help' => 'sp_BlocksArea',
			'description' => $txt['sp-adminBlockListDesc'],
			'tabs' => array(
				'list' => array(
					'description' => $txt['sp-adminBlockListDesc'],
				),
				'add' => array(
					'description' => $txt['sp-adminBlockAddDesc'],
				),
				'header' => array(
					'description' => $txt['sp-adminBlockHeaderListDesc'],
				),
				'left' => array(
					'description' => $txt['sp-adminBlockLeftListDesc'],
				),
				'top' => array(
					'description' => $txt['sp-adminBlockTopListDesc'],
				),
				'bottom' => array(
					'description' => $txt['sp-adminBlockBottomListDesc'],
				),
				'right' => array(
					'description' => $txt['sp-adminBlockRightListDesc'],
				),
				'footer' => array(
					'description' => $txt['sp-adminBlockFooterListDesc'],
				),
			),
		);

		// The default action will show the block list
		$subAction = $action->initialize($subActions, 'list');
		$context['sub_action'] = $subAction;

		// Call the right function for this sub-action
		$action->dispatch($subAction);
	}

	/**
	 * Show the Block List.
	 */
	public function action_list()
	{
		global $txt, $context, $scripturl;

		// We have 6 sides...like a cube!
		$context['sides'] = array(
			'header' => array(
				'id' => '5',
				'name' => 'adminHeader',
				'label' => $txt['sp-positionHeader'],
				'help' => 'sp-blocksHeaderList',
			),
			'left' => array(
				'id' => '1',
				'name' => 'adminLeft',
				'label' => $txt['sp-positionLeft'],
				'help' => 'sp-blocksLeftList',
			),
			'top' => array(
				'id' => '2',
				'name' => 'adminTop',
				'label' => $txt['sp-positionTop'],
				'help' => 'sp-blocksTopList',
			),
			'bottom' => array(
				'id' => '3',
				'name' => 'adminBottom',
				'label' => $txt['sp-positionBottom'],
				'help' => 'sp-blocksBottomList',
			),
			'right' => array(
				'id' => '4',
				'name' => 'adminRight',
				'label' => $txt['sp-positionRight'],
				'help' => 'sp-blocksRightList',
			),
			'footer' => array(
				'id' => '6',
				'name' => 'adminFooter',
				'label' => $txt['sp-positionFooter'],
				'help' => 'sp-blocksFooterList',
			),
		);

		$sides = array('header', 'left', 'top', 'bottom', 'right', 'footer');

		// Are we viewing any of the sub lists for an individual side?
		if (in_array($context['sub_action'], $sides))
		{
			// Remove any sides that we don't need to show. ;)
			foreach ($sides as $side)
			{
				if ($context['sub_action'] != $side)
					unset($context['sides'][$side]);
			}

			$context['sp_blocks_single_side_list'] = true;
		}

		// Columns to show.
		$context['columns'] = array(
			'label' => array(
				'width' => '40%',
				'label' => $txt['sp-adminColumnName'],
				'class' => 'first_th',
			),
			'type' => array(
				'width' => '40%',
				'label' => $txt['sp-adminColumnType'],
			),
			'action' => array(
				'width' => '20%',
				'label' => $txt['sp-adminColumnAction'],
				'class' => 'centertext last_th',
			),
		);

		// Get the block info for each side.
		foreach ($context['sides'] as $side_id => $side)
		{
			$context['blocks'][$side['name']] = getBlockInfo($side['id']);
			foreach ($context['blocks'][$side['name']] as $block_id => $block)
			{
				$context['sides'][$side_id]['last'] = $block_id;
				$context['blocks'][$side['name']][$block_id]['actions'] = array(
					'state_icon' => empty($block['state']) ? '<a href="' . $scripturl . '?action=admin;area=portalblocks;sa=statechange;' . (empty($context['sp_blocks_single_side_list']) ? '' : 'redirect=' . $block['column'] . ';') . 'block_id=' . $block['id'] . ';type=block;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('deactive', $txt['sp-blocksActivate']) . '</a>' : '<a href="' . $scripturl . '?action=admin;area=portalblocks;sa=statechange;' . (empty($context['sp_blocks_single_side_list']) ? '' : 'redirect=' . $block['column'] . ';') . 'block_id=' . $block['id'] . ';type=block;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('active', $txt['sp-blocksDeactivate']) . '</a>',
					'edit' => '&nbsp;<a href="' . $scripturl . '?action=admin;area=portalblocks;sa=edit;block_id=' . $block['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
					'delete' => '&nbsp;<a href="' . $scripturl . '?action=admin;area=portalblocks;sa=delete;block_id=' . $block['id'] . ';col=' . $block['column'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['sp-deleteblock'] . '\');">' . sp_embed_image('delete') . '</a>',
				);
			}
		}

		// Call the sub template.
		createToken('admin-sort', 'post');
		$context['sub_template'] = 'block_list';
		$context['page_title'] = $txt['sp-adminBlockListName'];
	}

	/**
	 * Adding or editing a block.
	 */
	public function action_edit()
	{
		global $txt, $context, $modSettings, $boards;

		// Just in case, the admin could be doing something silly like editing a SP block while SP is disabled. ;)
		require_once(SUBSDIR . '/spblocks/SPAbstractBlock.class.php');

		$context['SPortal']['is_new'] = empty($_REQUEST['block_id']);

		// Convert HTML to BBC if needed
		$this->_fixBBC();

		// Passing the selected type via $_GET instead of $_POST?
		$start_parameters = $this->_getStartParameters();

		// Want use a block on the portal?
		if ($context['SPortal']['is_new'] && empty($_POST['selected_type']) && empty($_POST['add_block']))
		{
			// Gather the blocks we have available
			$context['SPortal']['block_types'] = getFunctionInfo();

			// Create a list of the blocks in use
			$in_use = getBlockInfo();
			foreach ($in_use as $block)
				$context['SPortal']['block_inuse'][$block['type']] = array('state' => $block['state'], 'column' => $block['column']);

			$context['location'] = array(1 => $txt['sp-positionLeft'], $txt['sp-positionTop'], $txt['sp-positionBottom'], $txt['sp-positionRight'], $txt['sp-positionHeader'], $txt['sp-positionFooter']);

			if (!empty($_REQUEST['col']))
				$context['SPortal']['block']['column'] = $_REQUEST['col'];

			$context['sub_template'] = 'block_select_type';
			$context['page_title'] = $txt['sp-blocksAdd'];
		}
		// Selecting a block to place in to service, load some initial values
		elseif ($context['SPortal']['is_new'] && !empty($_POST['selected_type']))
		{
			$block = sp_instantiate_block($_POST['selected_type'][0]);
			$context['SPortal']['block'] = array(
				'id' => 0,
				'label' => $txt['sp-blocksDefaultLabel'],
				'type' => $_POST['selected_type'][0],
				'type_text' => !empty($txt['sp_function_' . $_POST['selected_type'][0] . '_label']) ? $txt['sp_function_' . $_POST['selected_type'][0] . '_label'] : $txt['sp_function_unknown_label'],
				'column' => !empty($_POST['block_column']) ? $_POST['block_column'] : 0,
				'row' => 0,
				'permissions' => 3,
				'styles' => 4,
				'visibility' => 14,
				'state' => 1,
				'force_view' => 0,
				'parameters' => !empty($start_parameters) ? $start_parameters : array(),
				'options' => $block->parameters(),
				'list_blocks' => !empty($_POST['block_column']) ? getBlockInfo((int) $_POST['block_column']) : array(),
			);
		}
		// Saving the block to one of the zones
		elseif (!$context['SPortal']['is_new'] && empty($_POST['add_block']))
		{
			$_REQUEST['block_id'] = (int) $_REQUEST['block_id'];
			$context['SPortal']['block'] = current(getBlockInfo(null, $_REQUEST['block_id']));

			$block = sp_instantiate_block($context['SPortal']['block']['type']);
			$context['SPortal']['block'] += array(
				'options' => $block->parameters(),
				'list_blocks' => getBlockInfo($context['SPortal']['block']['column']),
			);
		}

		// Want to take a look at how this block will appear, well we try our best
		if (!empty($_POST['preview_block']) || isset($_SESSION['sp_error']))
		{
			// An error was generated on save, lets set things up like a preview and return to the preview
			if (isset($_SESSION['sp_error']))
			{
				$context['SPortal']['error'] = $_SESSION['sp_error'];
				$_POST = $_SESSION['sp_error_post'];
				$_POST['preview_block'] = true;

				// Clean up
				unset($_SESSION['sp_error'], $_SESSION['sp_error_post'], $_POST['add_block']);
			}

			// Just in case, the admin could be doing something silly like editing a SP block while SP is disabled. ;)
			require_once(BOARDDIR . '/SSI.php');
			sportal_init_headers();
			loadTemplate('Portal');

			$block = sp_instantiate_block($_POST['block_type']);
			$type_parameters = $block->parameters();

			if (!empty($_POST['parameters']) && is_array($_POST['parameters']) && !empty($type_parameters))
			{
				foreach ($type_parameters as $name => $type)
				{
					if (isset($_POST['parameters'][$name]))
						$this->_prepare_parameters($type, $name);
				}
			}
			else
				$_POST['parameters'] = array();

			// Create all the information we know about this block
			$context['SPortal']['block'] = array(
				'id' => $_POST['block_id'],
				'label' => Util::htmlspecialchars($_POST['block_name'], ENT_QUOTES),
				'type' => $_POST['block_type'],
				'type_text' => !empty($txt['sp_function_' . $_POST['block_type'] . '_label']) ? $txt['sp_function_' . $_POST['block_type'] . '_label'] : $txt['sp_function_unknown_label'],
				'column' => $_POST['block_column'],
				'row' => !empty($_POST['block_row']) ? $_POST['block_row'] : 0,
				'placement' => !empty($_POST['placement']) && in_array($_POST['placement'], array('before', 'after')) ? $_POST['placement'] : '',
				'permissions' => $_POST['permissions'],
				'styles' => $_POST['styles'],
				'visibility' => $_POST['visibility'],
				'state' => !empty($_POST['block_active']),
				'force_view' => !empty($_POST['block_force']),
				'parameters' => !empty($_POST['parameters']) ? $_POST['parameters'] : array(),
				'options' => $block->parameters(),
				'list_blocks' => getBlockInfo($_POST['block_column']),
				'collapsed' => false,
			);

			if (strpos($modSettings['leftwidth'], '%') !== false || strpos($modSettings['leftwidth'], 'px') !== false)
				$context['widths'][1] = $modSettings['leftwidth'];
			else
				$context['widths'][1] = $modSettings['leftwidth'] . 'px';

			if (strpos($modSettings['rightwidth'], '%') !== false || strpos($modSettings['rightwidth'], 'px') !== false)
				$context['widths'][4] = $modSettings['rightwidth'];
			else
				$context['widths'][4] = $modSettings['rightwidth'] . 'px';

			if (strpos($context['widths'][1], '%') !== false)
			{
				$context['widths'][2] = $context['widths'][3] = 100 - ($context['widths'][1] + $context['widths'][4]) . '%';
				$context['widths'][5] = $context['widths'][6] = '100%';
			}
			elseif (strpos($context['widths'][1], 'px') !== false)
			{
				$context['widths'][2] = $context['widths'][3] = 960 - ($context['widths'][1] + $context['widths'][4]) . 'px';
				$context['widths'][5] = $context['widths'][6] = '960px';
			}

			$context['SPortal']['preview'] = true;
		}

		if (!empty($_POST['selected_type']) || !empty($_POST['preview_block']) || (!$context['SPortal']['is_new'] && empty($_POST['add_block'])))
		{
			// Only the admin can use PHP blocks
			if ($context['SPortal']['block']['type'] === 'sp_php' && !allowedTo('admin_forum'))
				fatal_lang_error('cannot_admin_forum', false);

			loadLanguage('SPortalHelp');

			// Load up the permissions
			$context['SPortal']['block']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
			if (empty($context['SPortal']['block']['permission_profiles']))
				fatal_lang_error('error_sp_no_permission_profiles', false);

			// Load in the style profiles
			$context['SPortal']['block']['style_profiles'] = sportal_get_profiles(null, 2, 'name');
			if (empty($context['SPortal']['block']['style_profiles']))
				fatal_lang_error('error_sp_no_style_profiles', false);

			// Load in the display profiles
			$context['SPortal']['block']['visibility_profiles'] = sportal_get_profiles(null, 3, 'name');
			if (empty($context['SPortal']['block']['visibility_profiles']))
				fatal_lang_error('error_sp_no_visibility_profiles', false);

			$context['SPortal']['block']['style'] = sportal_select_style($context['SPortal']['block']['styles']);

			// Prepare the Textcontent for BBC, only the first bbc will be detected correctly!
			$firstBBCFound = false;
			foreach ($context['SPortal']['block']['options'] as $name => $type)
			{
				// Selectable Boards :D
				if ($type === 'board_select' || $type === 'boards')
				{
					if (empty($boards))
					{
						require_once(SUBSDIR . '/Boards.subs.php');
						getBoardTree();
					}

					// Merge the array ;)
					if (!isset($context['SPortal']['block']['parameters'][$name]))
						$context['SPortal']['block']['parameters'][$name] = array();
					elseif (!empty($context['SPortal']['block']['parameters'][$name]) && is_array($context['SPortal']['block']['parameters'][$name]))
						$context['SPortal']['block']['parameters'][$name] = implode('|', $context['SPortal']['block']['parameters'][$name]);

					$context['SPortal']['block']['board_options'][$name] = array();
					$config_variable = !empty($context['SPortal']['block']['parameters'][$name]) ? $context['SPortal']['block']['parameters'][$name] : array();
					$config_variable = !is_array($config_variable) ? explode('|', $config_variable) : $config_variable;
					$context['SPortal']['block']['board_options'][$name] = array();

					// Create the list for this Item
					foreach ($boards as $board)
					{
						// Ignore the redirected boards :)
						if (!empty($board['redirect']))
							continue;

						$context['SPortal']['block']['board_options'][$name][$board['id']] = array(
							'value' => $board['id'],
							'text' => $board['name'],
							'selected' => in_array($board['id'], $config_variable),
						);
					}
				}
				// Prepare the Text content for BBC, only the first bbc will be correct detected!
				elseif ($type === 'bbc')
				{
					// ELK onlys supports one bbc correctly, multiple bbc do not work at the moment
					if (!$firstBBCFound)
					{
						$firstBBCFound = true;

						// Start Elk BBC System :)
						require_once(SUBSDIR . '/Editor.subs.php');

						// Prepare the output :D
						$form_message = !empty($context['SPortal']['block']['parameters'][$name]) ? $context['SPortal']['block']['parameters'][$name] : '';

						// But if it's in HTML world, turn them into htmlspecialchar's so they can be edited!
						if (strpos($form_message, '[html]') !== false)
						{
							$parts = preg_split('~(\[/code\]|\[code(?:=[^\]]+)?\])~i', $form_message, -1, PREG_SPLIT_DELIM_CAPTURE);
							for ($i = 0, $n = count($parts); $i < $n; $i++)
							{
								// It goes 0 = outside, 1 = begin tag, 2 = inside, 3 = close tag, repeat.
								if ($i % 4 == 0)
									$parts[$i] = preg_replace_callback('~\[html\](.+?)\[/html\]~is', function($m) {return '[html]' . preg_replace('~<br\s?/?>~i', '&lt;br /&gt;<br />', $m[1]) . '[/html]';}, $parts[$i]);
							}
							$form_message = implode('', $parts);
						}
						$form_message = preg_replace('~<br(?: /)?' . '>~i', "\n", $form_message);

						// Prepare the data before i want them inside the textarea
						$form_message = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $form_message);
						$context['SPortal']['bbc'] = 'bbc_' . $name;
						$message_data = array(
							'id' => $context['SPortal']['bbc'],
							'width' => '95%',
							'height' => '200px',
							'value' => $form_message,
							'form' => 'sp_block',
						);

						// Run the ELK bbc editor routine
						create_control_richedit($message_data);

						// Store the updated data on the parameters
						$context['SPortal']['block']['parameters'][$name] = $form_message;
					}
					else
						$context['SPortal']['block']['options'][$name] = 'textarea';
				}
			}

			loadJavascriptFile('portal.js?sp100');
			$context['sub_template'] = 'block_edit';
			$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp-blocksAdd'] : $txt['sp-blocksEdit'];
		}

		// Want to add / edit a block on the portal
		if (!empty($_POST['add_block']))
		{
			checkSession();

			// Only the admin can do php here
			if ($_POST['block_type'] === 'sp_php' && !allowedTo('admin_forum'))
				fatal_lang_error('cannot_admin_forum', false);

			// Make sure the block name is something safe
			if (!isset($_POST['block_name']) || Util::htmltrim(Util::htmlspecialchars($_POST['block_name'], ENT_QUOTES)) === '')
				fatal_lang_error('error_sp_name_empty', false);

			if ($_POST['block_type'] === 'sp_php' && !empty($_POST['parameters']['content']) && empty($modSettings['sp_disable_php_validation']))
			{
				require_once(SUBSDIR . '/DataValidator.class.php');

				$validator = new Data_Validator();
				$validator->validation_rules(array('content' => 'php_syntax'));
				$validator->validate(array('content' => $_POST['parameters']['content']));
				$error = $validator->validation_errors();

				if ($error)
				{
					$_SESSION['sp_error'] = $error[0];
					$_SESSION['sp_error_post'] = $_POST;
					redirectexit('action=admin;area=portalblocks;sa=' . $_REQUEST['sa'] . (!empty($_REQUEST['block_id']) ? ';block_id=' . $_REQUEST['block_id'] : ''));
				}
			}

			// If we have a block ID passed, we must be editing, so load the blocks current data
			if (!empty($_REQUEST['block_id']))
				$current_data = current(getBlockInfo(null, $_REQUEST['block_id']));

			// Where are we going to place this new block, before, after, no change
			if (!empty($_POST['placement']) && (($_POST['placement'] === 'before') || ($_POST['placement'] === 'after')))
			{
				if (!empty($current_data))
					$current_row = $current_data['row'];
				else
					$current_row = null;

				// Before or after the chosen block
				if ($_POST['placement'] === 'before')
					$row = (int) $_POST['block_row'];
				else
					$row = (int) $_POST['block_row'] + 1;

				if (!empty($current_row) && ($row > $current_row))
					sp_update_block_row($current_row, $row - 1, $_POST['block_column'], true);
				else
					sp_update_block_row($current_row, $row, $_POST['block_column'], false);
			}
			elseif (!empty($_POST['placement']) && $_POST['placement'] === 'nochange')
				$row = 0;
			else
			{
				$block_id = !empty($_REQUEST['block_id']) ? (int) $_REQUEST['block_id'] : 0;
				$row = sp_block_nextrow($_POST['block_column'], $block_id);
			}

			$block = sp_instantiate_block($_POST['block_type']);
			$type_parameters = $block->parameters();

			if (!empty($_POST['parameters']) && is_array($_POST['parameters']) && !empty($type_parameters))
			{
				foreach ($type_parameters as $name => $type)
				{
					// Prepare BBC Content for ELK
					if (isset($_POST['parameters'][$name]))
						$this->_prepare_parameters($type, $name);
				}
			}
			else
				$_POST['parameters'] = array();

			$blockInfo = array(
				'id' => (int) $_POST['block_id'],
				'label' => Util::htmlspecialchars($_POST['block_name'], ENT_QUOTES),
				'type' => $_POST['block_type'],
				'col' => $_POST['block_column'],
				'row' => $row,
				'permissions' => (int) $_POST['permissions'],
				'styles' => (int) $_POST['styles'],
				'visibility' => (int) $_POST['visibility'],
				'state' => !empty($_POST['block_active']) ? 1 : 0,
				'force_view' => !empty($_POST['block_force']) ? 1 : 0,
			);

			// Insert a new block in to the portal
			if ($context['SPortal']['is_new'])
			{
				unset($blockInfo['id']);
				$blockInfo['id'] = sp_block_insert($blockInfo);
			}
			// Update one that is there
			else
				sp_block_update($blockInfo);

			// Save any parameters for the block
			if (!empty($_POST['parameters']))
				sp_block_insert_parameters($_POST['parameters'], $blockInfo['id']);

			redirectexit('action=admin;area=portalblocks');
		}
	}

	/**
	 * Moves get var to post for compatibility and get parameters to start params
	 *
	 * @return mixed[]
	 */
	private function _getStartParameters()
	{
		$start_parameters = array();

		if (!empty($_GET['selected_type']) && empty($_POST['selected_type']))
		{
			$_POST['selected_type'] = array($_GET['selected_type']);

			if (!empty($_GET['parameters']))
			{
				foreach ($_GET['parameters'] as $param)
				{
					if (isset($_GET[$param]))
						$start_parameters[$param] = $_GET[$param];
				}
			}
		}

		return $start_parameters;
	}

	/**
	 * Convert html to bbc
	 */
	private function _fixBBC()
	{
		// BBC Fix move the parameter to the correct position.
		if (!empty($_POST['bbc_name']))
		{
			$_POST['parameters'][$_POST['bbc_name']] = !empty($_POST[$_POST['bbc_parameter']]) ? $_POST[$_POST['bbc_parameter']] : '';

			// If we came from WYSIWYG then turn it back into BBC regardless.
			if (!empty($_REQUEST['bbc_' . $_POST['bbc_name'] . '_mode']) && isset($_POST['parameters'][$_POST['bbc_name']]))
			{
				require_once(SUBSDIR . '/Html2BBC.class.php');
				$bbc_converter = new Html_2_BBC($_POST['parameters'][$_POST['bbc_name']]);
				$_POST['parameters'][$_POST['bbc_name']] = $bbc_converter->get_bbc();

				// We need to unhtml it now as it gets done shortly.
				$_POST['parameters'][$_POST['bbc_name']] = un_htmlspecialchars($_POST['parameters'][$_POST['bbc_name']]);

				// We need this for everything else.
				// @todo what, thats what it is already?
				$_POST['parameters'][$_POST['bbc_name']] = $_POST['parameters'][$_POST['bbc_name']];
			}
		}
	}

	/**
	 * Preparse the post parameters for use
	 *
	 * - Cleans them as needed an places them back in $_POST
	 *
	 * @param string $type
	 * @param string $name
	 */
	private function _prepare_parameters($type, $name)
	{
		if ($type === 'bbc')
		{
			$value = $_POST['parameters'][$name];
			require_once(SUBSDIR . '/Post.subs.php');

			// Prepare the message a bit for some additional testing.
			$value = Util::htmlspecialchars($value, ENT_QUOTES);
			preparsecode($value);

			// Store now the correct and fixed value ;)
			$_POST['parameters'][$name] = $value;
		}
		elseif ($type === 'boards' || $type === 'board_select')
			$_POST['parameters'][$name] = is_array($_POST['parameters'][$name]) ? implode('|', $_POST['parameters'][$name]) : $_POST['parameters'][$name];
		elseif ($type === 'int' || $type === 'select')
			$_POST['parameters'][$name] = (int) $_POST['parameters'][$name];
		elseif ($type === 'text' || $type === 'textarea' || is_array($type))
			$_POST['parameters'][$name] = Util::htmlspecialchars($_POST['parameters'][$name], ENT_QUOTES);
		elseif ($type === 'check')
			$_POST['parameters'][$name] = !empty($_POST['parameters'][$name]) ? 1 : 0;
	}

	/**
	 * Reorders the blocks in response to a D&D ajax request
	 */
	public function action_blockorder()
	{
		global $context, $txt;

		// Start off with nothing
		$context['xml_data'] = array();
		$errors = array();
		$order = array();

		// Chances are
		loadLanguage('Errors');
		loadLanguage('SPortalAdmin');

		// You have to be allowed to do this
		$validation_token = validateToken('admin-sort', 'post', true, false);
		$validation_session = validateSession();
		if (empty($validation_session) && $validation_token === true)
		{
			// No questions that we are reordering the blocks
			if (isset($_POST['order'], $_POST['received'], $_POST['moved']))
			{
				require_once(SUBSDIR . '/PortalAdmin.subs.php');
				require_once(SUBSDIR . '/Portal.subs.php');

				$target_side = (int) str_replace('side_', '', $_POST['received']);
				$block_id = (int) str_replace('block_', '', $_POST['moved']);
				list ($current_side, ) = sp_block_get_position($block_id);

				// The block ids arrive in 1-n view order ...
				$blocks = array();
				foreach ($_POST['block'] as $id)
					$blocks[] = $id;

				// Find where the moved block is in the block stack
				$moved_key = array_search($block_id, $blocks);
				if ($moved_key !== false)
				{
					$check_above_row = 0;
					$check_below_row = 0;

					// Find the details about the blocks above and below our moved one
					if ($moved_key !== 0)
						list ($check_above_side, $check_above_row) = sp_block_get_position($blocks[$moved_key - 1]);
					if ($moved_key + 1 < count($blocks))
						list ($check_below_side, $check_below_row) = sp_block_get_position($blocks[$moved_key + 1]);

					// The block above is in the same side, so we place it after that block
					if (isset($check_above_side) && $check_above_side == $target_side)
						$target_row = $check_above_row + 1;
					// The block above is not in the same side, but the block below is, move it above that one
					elseif (isset($check_below_side) && $check_below_side == $target_side)
						$target_row = $check_below_row;
					// Perhaps the first in what was an empty side or "other"
					else
						$target_row = sp_block_nextrow($target_side);

					// Is the block moving sides?
					if ($current_side != $target_side)
						sp_block_move_col($block_id, $target_side);

					// Position it in right row in the side
					sp_blocks_move_row($block_id, $target_side, $target_row);

					// Update the sides that may have been affected
					foreach (array_unique(array($current_side, $target_side)) as $side)
						fixColumnRows($side);

					$order[] = array(
						'value' => $txt['sp-blocks_success_moving'],
					);
				}
				else
					$errors[] = array('value' => $txt['sp-blocks_fail_moving']);
			}
		}
		// Failed validation, tough to be you
		else
		{
			if (!empty($validation_session))
				$errors[] = array('value' => $txt[$validation_session]);

			if (empty($validation_token))
				$errors[] = array('value' => $txt['token_verify_fail']);
		}

		// New generic token for use
		createToken('admin-sort', 'post');
		$tokens = array(
			array(
				'value' => $context['admin-sort_token'],
				'attributes' => array('type' => 'token'),
			),
			array(
				'value' => $context['admin-sort_token_var'],
				'attributes' => array('type' => 'token_var'),
			),
		);

		// Return the response
		$context['sub_template'] = 'generic_xml';
		$context['xml_data'] = array(
			'orders' => array(
				'identifier' => 'order',
				'children' => $order,
			),
			'tokens' => array(
				'identifier' => 'token',
				'children' => $tokens,
			),
			'errors' => array(
				'identifier' => 'error',
				'children' => $errors,
			),
		);
	}

	/**
	 * Function for moving a block
	 *
	 * - Moves a block to a new row and/or a new column
	 */
	public function action_move()
	{
		checkSession('get');

		// Not moving yet
		$target_side = null;
		$block_id = null;

		// What block is being moved?
		if (empty($_REQUEST['block_id']))
			fatal_lang_error('error_sp_id_empty', false);
		else
			$block_id = (int) $_REQUEST['block_id'];

		// Can't move outside our known columns 1-6
		if (empty($_REQUEST['col']) || $_REQUEST['col'] < 1 || $_REQUEST['col'] > 6)
			fatal_lang_error('error_sp_side_wrong', false);
		else
			$target_side = (int) $_REQUEST['col'];

		// Specific row requested?
		if (empty($_REQUEST['row']))
			$target_row = sp_block_nextrow($target_side);
		else
			$target_row = (int) $_REQUEST['row'];

		// Get the blocks current position in the portal
		list ($current_side, $current_row)  = sp_block_get_position($block_id);

		// Is a move needed, new row, new column?
		if ($current_side != $target_side || $current_row + 1 != $target_row)
		{
			// Shift the column
			if ($current_side != $target_side)
				sp_block_move_col($block_id, $target_side);

			// Position it in the column
			sp_blocks_move_row($block_id, $target_side, $target_row);

			foreach (array_unique(array($current_side, $target_side)) as $side)
				fixColumnRows($side);
		}

		redirectexit('action=admin;area=portalblocks');
	}

	/**
	 * Function for deleting a block.
	 */
	public function action_delete()
	{
		global $context;

		// Check if he can?
		checkSession('get');

		// Make sure ID is an integer.
		$_REQUEST['block_id'] = (int) $_REQUEST['block_id'];

		// Do we have that?
		if (empty($_REQUEST['block_id']))
			fatal_lang_error('error_sp_id_empty', false);

		// Make sure column ID is an integer too.
		$_REQUEST['col'] = (int) $_REQUEST['col'];

		// Only Admins can Remove PHP Blocks
		if (!allowedTo('admin_forum'))
		{
			$context['SPortal']['block'] = current(getBlockInfo(null, $_REQUEST['block_id']));
			if ($context['SPortal']['block']['type'] === 'sp_php' && !allowedTo('admin_forum'))
				fatal_lang_error('cannot_admin_forum', false);
		}

		// We don't need it anymore.
		sp_block_delete($_REQUEST['block_id']);

		// Fix column rows.
		fixColumnRows($_REQUEST['col']);

		// Return back to the block list.
		redirectexit('action=admin;area=portalblocks');
	}

	/**
	 * Pass this off to the state change function
	 */
	public function action_state_change()
	{
		$id = (int) $_REQUEST['block_id'];
		$type = $_REQUEST['type'];

		sportal_admin_state_change($type, $id);
	}
}