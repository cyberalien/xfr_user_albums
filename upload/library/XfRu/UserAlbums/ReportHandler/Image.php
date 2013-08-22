<?php

/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Image.php 134 2011-03-06 21:45:42Z pepelac $ $Date: 2011-03-06 22:45:42 +0100 (Sun, 06 Mar 2011) $ $Revision: 134 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ReportHandler_Image extends XenForo_ReportHandler_Abstract
{

	public function getContentLink(array $report, array $contentInfo)
	{
		return XenForo_Link::buildPublicLink('useralbums/view-image', $contentInfo);
	}

	public function getContentTitle(array $report, array $contentInfo)
	{
		return new XenForo_Phrase('xfr_useralbums_image_report_title', array('title' => $contentInfo['title']));
	}

	public function getReportDetailsFromContent(array $content)
	{
		$imagesModel = XenForo_Model::create('XfRu_UserAlbums_Model_Images');

		$image = $imagesModel->getImageInfoById($content['image_id']);

		return array(
			$content['image_id'],
			$image['user_id'],
			array(
				'image_id' => $image['image_id'],
				'title' => $image['title'],
				'username' => $image['username'],
				'description' => $image['description']
			)
		);
	}

	public function getVisibleReportsForUser(array $reports, array $viewingUser)
	{
		return $reports;
	}

	public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
	{
		return $view->createTemplateObject('xfr_useralbums_report_image_content', array(
			'report'		=> $report,
			'content'		=> $contentInfo
		));
	}
}