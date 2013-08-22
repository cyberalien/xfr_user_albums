<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Image.php 139 2011-03-07 21:23:38Z pepelac $ $Date: 2011-03-07 22:23:38 +0100 (Mon, 07 Mar 2011) $ $Revision: 139 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_AlertHandler_Image extends XenForo_AlertHandler_Abstract
{
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$imagesModel = $model->getModelFromCache('XfRu_UserAlbums_Model_Images');
		return $imagesModel->getImagesByIds($contentIds);
	}

	protected function _getDefaultTemplateTitle($contentType, $action)
	{
		// xfr_useralbum_image_like_alert
		return $contentType . '_' . $action . '_alert';
	}
}