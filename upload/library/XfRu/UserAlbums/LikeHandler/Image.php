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

class XfRu_UserAlbums_LikeHandler_Image extends XenForo_LikeHandler_Abstract
{

	public function getContentData(array $contentIds, array $viewingUser)
	{
		$imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');
		return $imagesModel->getImagesByIds($contentIds);
	}

	public function getListTemplateName()
	{
		return 'xfr_useralbums_news_feed_item_image_like';
	}

	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
		$writer->setExistingData($contentId);
		$writer->set('likes', $writer->get('likes') + $adjustAmount);
		$writer->set('like_users', $latestLikes);
		$writer->save();
	}
}