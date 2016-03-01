<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 *
 * This version of SimplePortal is based on SimplePortal core 2.4
 *
 * 	This file here, unbelievably, has your portal within.
 *
 * In order to use SimplePortal in standalone mode:
 * - Go to "SPortal Admin" >> "Configuration" >> "General Settings"
 * - Select "Standalone" mode as "Portal Mode"
 * - Set "Standalone URL" as the full url of this file.
 * - Edit path to the forum ($forum_dir) in this file.
 *
 * See? It's just magic!
 *
 */

global $sp_standalone;

// Should be the full path!
$forum_dir = 'full/path/to/forum';

// Let them know the mode.
$sp_standalone = true;

// Hmm, wrong forum dir?
if (!file_exists($forum_dir . '/index.php'))
{
	die('Wrong $forum_dir value. Please make sure that the $forum_dir variable points to your forum\'s directory.');
}

// Get out the forum's Elkarte version number.
$data = substr(file_get_contents($forum_dir . '/index.php'), 0, 4096);
if (preg_match('~\*\s@version\s+(.+)[\s]{2}~i', $data, $match))
{
	$forum_version = 'ElkArte ' . $match[1];
}

// Load the SSI magic.
require_once($forum_dir . '/SSI.php');

// Its all about the blocks
require_once(SUBSDIR . '/spblocks/SPAbstractBlock.class.php');

// Initialize SP in standalone mode mode
loadTemplate('Portal', 'portal');
sportal_init(true);

// We'll catch you...
writeLog();

// Articles
require_once(CONTROLLERDIR . '/PortalMain.controller.php');
$controller = new Sportal_Controller();
$controller->pre_dispatch();
$controller->action_index();

// Here we go!
obExit(true);