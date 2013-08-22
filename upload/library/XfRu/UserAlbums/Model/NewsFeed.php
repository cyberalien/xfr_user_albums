<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: NewsFeed.php 215 2011-04-25 21:58:31Z pepelac $ $Date: 2011-04-25 23:58:31 +0200 (Mon, 25 Apr 2011) $ $Revision: 215 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Model_NewsFeed extends XenForo_Model
{
	/**
	 * Adds record to users news feed
	 *
	 * @param array $data Data to insert
	 * @param  $contentType Content type
	 * @return void
	 */
	public function publishFeedItem(array $data, $contentType)
	{
		/* @var $newsFeedModel \XenForo_Model_NewsFeed */
		$newsFeedModel = $this->getModelFromCache('XenForo_Model_NewsFeed');
		$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($data['user_id']);

		switch ($contentType)
		{
			case 'album':
				$newsFeedModel->publish(
					$data['user_id'],
					$user['username'],
					XfRu_UserAlbums_Helper::CT_ALBUM,
					$data['album_id'],
					'insert'
				);
				break;

			case 'image':
				$newsFeedModel->publish(
					$data['user_id'],
					$user['username'],
					XfRu_UserAlbums_Helper::CT_ALBUM_IMAGE,
					$data['album_id'],
					'insert',
					array('newImages' => $data['newImages'])
				);
				break;

			case 'comment':
				$newsFeedModel->publish(
					$data['user_id'],
					$user['username'],
					XfRu_UserAlbums_Helper::CT_ALBUM_IMAGE_COMMENT,
					$data['comment_id'],
					'insert'
				);
				break;
		}
	}
}