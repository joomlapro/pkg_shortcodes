<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.YouTube
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * YouTube Shortcode Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.YouTube
 * @since       3.1
 */
class PlgShortcodeYoutube extends JPlugin
{
	/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function onShortcodePrepare()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		Shortcodes::addShortcode('youtube', 'PlgShortcodeYoutube::youtube');

		return true;
	}

	/**
	 * Method to create YouTube shortcode.
	 *
	 * @param   string  $atts  User defined attributes in shortcode tag.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public static function youtube($atts)
	{
		extract(
			Shortcodes::shortcodeatts(
				array(
					'id' => '',
					'width' => 480,
					'height' => 390
				),
				$atts
			)
		);

		$id = trim($id);

		if (empty($id))
		{
			return;
		}

		return '<iframe title="YouTube video player" width="' . $width . '" height="' . $height . '" src="http://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
	}
}
