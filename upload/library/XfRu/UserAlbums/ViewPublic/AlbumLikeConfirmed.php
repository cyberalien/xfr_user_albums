<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: AlbumLikeConfirmed.php 288 2011-08-18 07:01:55Z pepelac $ $Date: 2011-08-18 09:01:55 +0200 (Thu, 18 Aug 2011) $ $Revision: 288 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_AlbumLikeConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$album = $this->_params['album'];

		if (!empty($album['like_users']))
		{
			$viewParams = array(
				'album' => $album,
				'likesUrl' => XenForo_Link::buildPublicLink('useralbums/likes-album', $album)
			);

			$output = $this->_renderer->getDefaultOutputArray(get_class($this), $viewParams, 'xfr_useralbums_album_likes_summary');
		} else {
			$output = array('templateHtml' => '', 'js' => '', 'css' => '');
		}

		$output += XenForo_ViewPublic_Helper_Like::getLikeViewParams($this->_params['liked']);

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}