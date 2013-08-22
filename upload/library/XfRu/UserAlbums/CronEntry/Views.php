<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Views.php 117 2011-01-16 13:35:25Z pepelac $ $Date: 2011-01-16 14:35:25 +0100 (Sun, 16 Jan 2011) $ $Revision: 117 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_CronEntry_Views
{
	public static function runViewUpdate()
	{
		XenForo_Model::create('XfRu_UserAlbums_Model_Albums')->updateAlbumViews();
		XenForo_Model::create('XfRu_UserAlbums_Model_Images')->updateImageViews();
	}
}