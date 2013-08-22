<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comment.php 181 2011-03-23 12:53:38Z pepelac $ $Date: 2011-03-23 13:53:38 +0100 (Wed, 23 Mar 2011) $ $Revision: 181 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_LikeHandler_Comment extends XenForo_LikeHandler_Abstract
{

	public function getContentData(array $contentIds, array $viewingUser)
	{
		return null;
	}

	public function getListTemplateName()
	{
//		return 'xfr_useralbums_news_feed_item_comment_like';
	}

	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
//		$writer = XenForo_DataWriter::create('XfRu_UserAlbums_DataWriter_Image');
//		$writer->setExistingData($contentId);
//		$writer->set('likes', $writer->get('likes') + $adjustAmount);
//		$writer->set('like_users', $latestLikes);
//		$writer->save();
	}
}