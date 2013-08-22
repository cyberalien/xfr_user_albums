<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comment.php 177 2011-03-20 21:10:28Z pepelac $ $Date: 2011-03-20 22:10:28 +0100 (Sun, 20 Mar 2011) $ $Revision: 177 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_AlertHandler_Comment extends XenForo_AlertHandler_Abstract{
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$commentsModel = $model->getModelFromCache('XfRu_UserAlbums_Model_Comments');
		return $commentsModel->getCommentsByIds($contentIds);
	}

	protected function _getDefaultTemplateTitle($contentType, $action)
	{
		// xfr_useralbum_image_cmnt_insert_alert
		return $contentType . '_' . $action . '_alert';
	}
}