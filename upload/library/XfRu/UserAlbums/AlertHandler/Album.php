<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Album.php 288 2011-08-18 07:01:55Z pepelac $ $Date: 2011-08-18 09:01:55 +0200 (Thu, 18 Aug 2011) $ $Revision: 288 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_AlertHandler_Album extends XenForo_AlertHandler_Abstract
{
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		/** @var XfRu_UserAlbums_Model_Albums $albumsModel  */
		$albumsModel = $model->getModelFromCache('XfRu_UserAlbums_Model_Albums');
		return $albumsModel->getAlbumsByIds($contentIds);
	}

	protected function _getDefaultTemplateTitle($contentType, $action)
	{
		// xfr_useralbum_like_alert
		return $contentType . '_' . $action . '_alert';
	}
}