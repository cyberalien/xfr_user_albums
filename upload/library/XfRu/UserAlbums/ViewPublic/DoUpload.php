<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: DoUpload.php 80 2011-01-07 21:55:28Z pepelac $ $Date: 2011-01-07 22:55:28 +0100 (Fri, 07 Jan 2011) $ $Revision: 80 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_DoUpload extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($this->prepareFileForJson($this->_params['image']));
	}

	protected function prepareFileForJson(array $image)
	{
		$keys = array('image_id', 'image_date', 'filename', 'thumbnailUrl', 'title');

		$template = $this->createTemplateObject('xfr_useralbums_image_editor_image', array('image' => $image));

		$image = XenForo_Application::arrayFilterKeys($image, $keys);

		$image['templateHtml'] = $template;

		return $image;
	}
}