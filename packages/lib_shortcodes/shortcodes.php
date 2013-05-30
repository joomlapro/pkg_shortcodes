<?php
/**
 * @package    Joomla.Shortcodes
 * @copyright  Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_PLATFORM') or die;

/**
 * Shortcodes class.
 *
 * @package  Joomla.Shortcodes
 * @since    3.1
 */
abstract class Shortcodes
{
	/**
	 * Container for storing shortcode tags and their hook to call for the shortcode.
	 *
	 * @since   3.1
	 * @var     array
	 */
	protected static $shortcode_tags = array();

	/**
	 * Add hook for shortcode tag.
	 *
	 * @param   string    $tag   Shortcode tag to be searched in post content.
	 * @param   callable  $func  Hook to run when shortcode is found.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addShortcode($tag, $func)
	{
		if (is_callable($func))
		{
			self::$shortcode_tags[$tag] = $func;
		}
	}

	/**
	 * Removes hook for shortcode.
	 *
	 * @param   string  $tag  Shortcode tag to remove hook for.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function removeShortcode($tag)
	{
		unset(self::$shortcode_tags[$tag]);
	}

	/**
	 * Clear all shortcodes.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function removeAllShortcodes()
	{
		self::$shortcode_tags = array();
	}

	/**
	 * Whether a registered shortcode exists named $tag.
	 *
	 * @param   string  $tag  Shortcode tag to be verify if exists.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public static function shortcodeExists($tag)
	{
		return array_key_exists($tag, self::$shortcode_tags);
	}

	/**
	 * Whether the passed content contains the specified shortcode.
	 *
	 * @param   string  $content  Content to search for shortcodes.
	 * @param   string  $tag      Shortcode tag.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public static function hasShortcode($content, $tag)
	{
		if (self::shortcodeExists($tag))
		{
			preg_match_all('/' . self::getShortcodeRegex() . '/s', $content, $matches, PREG_SET_ORDER);

			if (empty($matches))
			{
				return false;
			}

			foreach ($matches as $shortcode)
			{
				if ($tag === $shortcode[2])
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Search content for shortcodes and filter shortcodes through their hooks.
	 *
	 * @param   string  $content  Content to search for shortcodes.
	 *
	 * @return  string  Content with shortcodes filtered out.
	 *
	 * @since   3.1
	 */
	public static function doShortcode($content)
	{
		if (empty(self::$shortcode_tags) || !is_array(self::$shortcode_tags))
		{
			return $content;
		}

		$pattern = self::getShortcodeRegex();

		return preg_replace_callback("/$pattern/s", array('Shortcodes', 'doShortcodeTag'), $content);
	}

	/**
	 * Retrieve the shortcode regular expression for searching.
	 *
	 * @return  string  The shortcode search regular expression.
	 *
	 * @since   3.1
	 */
	public static function getShortcodeRegex()
	{
		// Initialiase variables.
		$tagnames  = array_keys(self::$shortcode_tags);
		$tagregexp = join('|', array_map('preg_quote', $tagnames));

		return '\\[(\\[?)' . "($tagregexp)" . '(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
	}

	/**
	 * Regular Expression callable for Shortcodes::doShortcode() for calling shortcode hook.
	 *
	 * @param   array  $matches  Regular expression match array.
	 *
	 * @return  mixed  False on failure.
	 *
	 * @since   3.1
	 */
	public static function doShortcodeTag($matches)
	{
		// Allow [[foo]] syntax for escaping a tag.
		if ($matches[1] == '[' && $matches[6] == ']')
		{
			return substr($matches[0], 1, -1);
		}

		// Initialiase variables.
		$tag = $matches[2];
		$attr = self::shortcodeParseAtts($matches[3]);

		if (isset($matches[5]))
		{
			// Enclosing tag - extra parameter.
			return $matches[1] . call_user_func(self::$shortcode_tags[$tag], $attr, $matches[5], $tag) . $matches[6];
		}
		else
		{
			// Self-closing tag.
			return $matches[1] . call_user_func(self::$shortcode_tags[$tag], $attr, null,  $tag) . $matches[6];
		}
	}

	/**
	 * Retrieve all attributes from the shortcodes tag.
	 *
	 * @param   string  $text  Text to search for attributes.
	 *
	 * @return  array  List of attributes and their value.
	 *
	 * @since   3.1
	 */
	public static function shortcodeParseAtts($text)
	{
		// Initialiase variables.
		$atts    = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text    = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

		if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				if (!empty($match[1]))
				{
					$atts[strtolower($match[1])] = stripcslashes($match[2]);
				}
				elseif (!empty($match[3]))
				{
					$atts[strtolower($match[3])] = stripcslashes($match[4]);
				}
				elseif (!empty($match[5]))
				{
					$atts[strtolower($match[5])] = stripcslashes($match[6]);
				}
				elseif (isset($match[7]) and strlen($match[7]))
				{
					$atts[] = stripcslashes($match[7]);
				}
				elseif (isset($match[8]))
				{
					$atts[] = stripcslashes($match[8]);
				}
			}
		}
		else
		{
			$atts = ltrim($text);
		}

		return $atts;
	}

	/**
	 * Combine user attributes with known attributes and fill in defaults when needed.
	 *
	 * @param   array  $pairs  Entire list of supported attributes and their defaults.
	 * @param   array  $atts   User defined attributes in shortcode tag.
	 *
	 * @return  array  Combined and filtered attribute list.
	 *
	 * @since 3.1
	 */
	public static function shortcodeAtts($pairs, $atts)
	{
		// Initialiase variables.
		$atts = (array) $atts;
		$out  = array();

		foreach ($pairs as $name => $default)
		{
			if (array_key_exists($name, $atts))
			{
				$out[$name] = $atts[$name];
			}
			else
			{
				$out[$name] = $default;
			}
		}

		return $out;
	}

	/**
	 * Remove all shortcode tags from the given content.
	 *
	 * @param   string  $content  Content to remove shortcode tags.
	 *
	 * @return  string  Content without shortcode tags.
	 *
	 * @since   3.1
	 */
	public function stripShortcodes($content)
	{
		if (empty(self::$shortcode_tags) || !is_array(self::$shortcode_tags))
		{
			return $content;
		}

		$pattern = self::getShortcodeRegex();

		return preg_replace_callback("/$pattern/s", array('Shortcodes', 'stripShortcodeTag'), $content);
	}

	/**
	 * Metho to strip shortcode of tag.
	 *
	 * @param   array  $matches  Regular expression match array.
	 *
	 * @return  mixed  False on failure.
	 *
	 * @since   3.1
	 */
	public function stripShortcodeTag($matches)
	{
		// Allow [[foo]] syntax for escaping a tag.
		if ($matches[1] == '[' && $matches[6] == ']')
		{
			return substr($matches[0], 1, -1);
		}

		return $matches[1] . $matches[6];
	}
}
