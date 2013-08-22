<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Abstract.php 334 2011-11-17 14:40:38Z pepelac $ $Date: 2011-11-17 15:40:38 +0100 (Thu, 17 Nov 2011) $ $Revision: 334 $
 * @author Pepelac
 *
 */

abstract class XfRu_UserAlbums_CacheRebuilder_Abstract extends XenForo_CacheRebuilder_Abstract
{
	/**
	 * List of cache/data builders.
	 *
	 * @var array [key name] => class name
	 */
	public static $builders = array(
		'Images' => 'XfRu_UserAlbums_CacheRebuilder_Images',
		'Comments' => 'XfRu_UserAlbums_CacheRebuilder_Comments',
		'Thumbnails' => 'XfRu_UserAlbums_CacheRebuilder_Thumbnails',
		'ThumbnailSprites' => 'XfRu_UserAlbums_CacheRebuilder_ThumbnailSprites',
	);

	/**
	 * Gets the specified cache rebuilder.
	 *
	 * @param string $keyName
	 *
	 * @return XfRu_UserAlbums_CacheRebuilder_Abstract
	 */
	public static function getCacheRebuilder($keyName)
	{
		if (!isset(self::$builders[$keyName]))
		{
			throw new XenForo_Exception('Invalid cache builder ' . $keyName . ' specified.');
		}

		$class = self::$builders[$keyName];
		return new $class($keyName);
	}
}