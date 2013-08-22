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

class XfRu_UserAlbums_LikeHandler_Album extends XenForo_LikeHandler_Abstract
{

	public function getContentData(array $contentIds, array $viewingUser)
	{
		/** @var XfRu_UserAlbums_Model_Albums $albumsModel  */
		$albumsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Albums');
		return $albumsModel->getAlbumsByIds($contentIds);
	}

	public function getListTemplateName()
	{
		return 'xfr_useralbums_news_feed_item_album_like';
	}

	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Album');
		$writer->setExistingData($contentId);
		$writer->set('likes', $writer->get('likes') + $adjustAmount);
		$writer->set('like_users', $latestLikes);
		$writer->save();
	}
}