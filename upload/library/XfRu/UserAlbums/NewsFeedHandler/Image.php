<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Image.php 215 2011-04-25 21:58:31Z pepelac $ $Date: 2011-04-25 23:58:31 +0200 (Mon, 25 Apr 2011) $ $Revision: 215 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_NewsFeedHandler_Image extends XenForo_NewsFeedHandler_Abstract
{
	/* @var $imagesModel XfRu_UserAlbums_Model_Albums */
	protected $albumsModel;

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
		return $this->getImagesModel()->getAlbumsByIds($contentIds);
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
		return $this->getImagesModel()->isAlbumViewable($content);
	}

	protected function _prepareInsert(array $item, array $viewingUser)
	{
		$item['extra'] = unserialize($item['extra_data']);
		return $item;
	}

	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	protected function getImagesModel()
	{
		if (!$this->albumsModel)
		{
			$this->albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');
		}
		return $this->albumsModel;
	}
}