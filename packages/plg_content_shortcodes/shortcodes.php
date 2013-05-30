<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Shortcodes
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('shortcodes.shortcodes');

/**
 * Shortcodes Content Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Shortcodes
 * @since       3.1
 */
class PlgContentShortcodes extends JPlugin
{
	/**
	 * Plugin that loads module positions within content.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available.
	 * @param   object   &$params   The article params.
	 * @param   integer  $page      The 'page' number.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Get the event dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Load the shortcode plugin group.
		JPluginHelper::importPlugin('shortcode');

		// Trigger the onShortcodePrepare event.
		$dispatcher->trigger('onShortcodePrepare', array($context, &$article, &$params));

		$article->text = Shortcodes::doShortcode($article->text);
	}
}
