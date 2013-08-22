<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ImageComment.php 215 2011-04-25 21:58:31Z pepelac $ $Date: 2011-04-25 23:58:31 +0200 (Mon, 25 Apr 2011) $ $Revision: 215 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_NewsFeedHandler_ImageComment extends XenForo_NewsFeedHandler_Abstract
{
	/* @var $imagesModel XfRu_UserAlbums_Model_Comments */
	protected $commentsModel;

	/**
	 * Fetches the content required by news feed items.
	 * Designed to be overridden by child classes using $model->getContentByIds($contentIds) or similar
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_NewsFeed $model
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */
	public function getContentByIds(array $contentIds, $model, array $viewingUser)
	{
		return $this->getCommantsModel()->getCommentsByIds($contentIds);
	}

	/**
	 * Determines if the given news feed item is viewable.
	 *
	 * @param array $item
	 * @param mixed $content
	 * @param array $viewingUser
	 *
	 * @return boolean
	 */
	public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
	{
		// todo: check album and image visibility
		return true;

	}

	/**
	 * @return XfRu_UserAlbums_Model_Comments
	 */
	protected function getCommantsModel()
	{
		if (!$this->commentsModel)
		{
			$this->commentsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Comments');
		}
		return $this->commentsModel;
	}
}