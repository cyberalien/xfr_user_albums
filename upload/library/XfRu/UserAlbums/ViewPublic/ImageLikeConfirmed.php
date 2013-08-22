<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ImageLikeConfirmed.php 139 2011-03-07 21:23:38Z pepelac $ $Date: 2011-03-07 22:23:38 +0100 (Mon, 07 Mar 2011) $ $Revision: 139 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ImageLikeConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$image = $this->_params['image'];

		if (!empty($image['like_users']))
		{
			$viewParams = array(
				'image' => $image,
				'likesUrl' => XenForo_Link::buildPublicLink('useralbums/likes-image', $image)
			);

			$output = $this->_renderer->getDefaultOutputArray(get_class($this), $viewParams, 'xfr_useralbums_image_likes_summary');
		} else {
			$output = array('templateHtml' => '', 'js' => '', 'css' => '');
		}

		$output += XenForo_ViewPublic_Helper_Like::getLikeViewParams($this->_params['liked']);

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}