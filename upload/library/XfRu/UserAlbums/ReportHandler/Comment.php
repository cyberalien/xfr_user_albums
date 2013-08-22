<?php

/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Comment.php 288 2011-08-18 07:01:55Z pepelac $ $Date: 2011-08-18 09:01:55 +0200 (Thu, 18 Aug 2011) $ $Revision: 288 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ReportHandler_Comment extends XenForo_ReportHandler_Abstract
{

	public function getContentLink(array $report, array $contentInfo)
	{
		return XenForo_Link::buildPublicLink('useralbums/image-comments', $contentInfo);
	}

	public function getContentTitle(array $report, array $contentInfo)
	{
		return new XenForo_Phrase('xfr_useralbums_comment_report_title');
	}

	public function getReportDetailsFromContent(array $content)
	{
		/**
		 * @var XfRu_UserAlbums_Model_Comments $commentsModel
		 */
		$commentsModel = XenForo_Model::create('XfRu_UserAlbums_Model_Comments');

		$comment = $commentsModel->getCommentById($content['comment_id']);

		return array(
			$content['comment_id'],
			$comment['user_id'],
			array(
				'comment_id' => $comment['comment_id'],
				'username' => $comment['username'],
				'message' => $comment['message']
			)
		);
	}

	public function getVisibleReportsForUser(array $reports, array $viewingUser)
	{
		return $reports;
	}

	public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
	{
		return $view->createTemplateObject('xfr_useralbums_report_comment_content', array(
			'report'		=> $report,
			'content'		=> $contentInfo
		));
	}
}