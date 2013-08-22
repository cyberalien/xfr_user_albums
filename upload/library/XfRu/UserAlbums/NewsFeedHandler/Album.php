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

class XfRu_UserAlbums_NewsFeedHandler_Album extends XenForo_NewsFeedHandler_Abstract
{
	/* @var XfRu_UserAlbums_Model_Albums $albumsModel  */
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
		return $this->getAlbumsModel()->getAlbumsByIds($contentIds);
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
		return $this->getAlbumsModel()->isAlbumViewable($content);
	}

	/**
	 * @return XfRu_UserAlbums_Model_Albums
	 */
	protected function getAlbumsModel()
	{
		if (!$this->albumsModel)
		{
			$this->albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');
		}
		return $this->albumsModel;
	}
}