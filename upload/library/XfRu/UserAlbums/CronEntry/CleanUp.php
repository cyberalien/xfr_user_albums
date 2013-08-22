<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: CleanUp.php 288 2011-08-18 07:01:55Z pepelac $ $Date: 2011-08-18 09:01:55 +0200 (Thu, 18 Aug 2011) $ $Revision: 288 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_CronEntry_CleanUp
{
	public static function runHourlyCleanUp()
	{
		// delete unassociated images
		$unassociatedImagesCutOff = XenForo_Application::$time - 86400;

		/** @var XfRu_UserAlbums_Model_Images $imagesModel */
	    $imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');
		$imagesModel->deleteUnassociatedImages($unassociatedImagesCutOff);
		$imagesModel->deleteUnusedImageData();
		/** @var XfRu_UserAlbums_Model_Comments $commentsModel */
		$commentsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Comments');
		$commentsModel->deleteUnassociatedComments($unassociatedImagesCutOff);
	}
}