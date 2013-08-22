<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Abstract.php 255 2011-06-05 17:13:16Z pepelac $ $Date: 2011-06-05 19:13:16 +0200 (Sun, 05 Jun 2011) $ $Revision: 255 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ControllerPublic_Abstract extends XenForo_ControllerPublic_Abstract
{
	//***** Helper functions *******************************************************************************************

	protected function getAlbumNotFoundException()
	{
		return $this->responseException($this->responseError(new XenForo_Phrase('xfr_useralbums_requested_album_not_found'), 404));
	}

	protected function getImageOrError($imageId)
	{
		$image = $this->getImagesModel()->getImageById($imageId);
		if (!$image)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('xfr_useralbums_requested_image_not_found'), 404));
		}
		return $image;
	}

	protected function getCommentOrError($commentId)
	{
		$comment = $this->getCommentsModel()->getCommentById($commentId);
		if (!$comment)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('xfr_useralbums_requested_comment_not_found'), 404));
		}
		return $comment;
	}

	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	protected function getAlbumsModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Albums');
	}

	/**
	 * @return XfRu_UserAlbums_Model_Images
	 */
	protected function getImagesModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Images');
	}

	/**
	 * @return XfRu_UserAlbums_Model_Comments
	 */
	protected function getCommentsModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Comments');
	}

	/**
	 * @return XfRu_UserAlbums_Model_Counters
	 */
	protected function getCountersModel()
	{
		return $this->getModelFromCache('XfRu_UserAlbums_Model_Counters');
	}

	/**
	 * @return XenForo_Model_Like
	 */
	protected function getLikeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Like');
	}

	/**
	 * @return XenForo_Model_User
	 */
	protected function getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}

}