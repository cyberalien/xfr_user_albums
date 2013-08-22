<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ViewImagebox.php 336 2011-11-17 17:59:01Z pepelac $ $Date: 2011-11-17 18:59:01 +0100 (Thu, 17 Nov 2011) $ $Revision: 336 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ViewImagebox extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$avatarUrls = XenForo_Template_Helper_Core::getAvatarUrls($this->_params['image']);

		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		$this->_params['image']['descriptionHtml'] = new XenForo_BbCode_TextWrapper(
			$this->_params['image']['description'],
			$bbCodeParser
		);

		$this->_params['album']['url'] = XenForo_Link::buildPublicLink('useralbums/view', $this->_params['album']);

		$this->_params['image']['dateFormatted'] = XenForo_Template_Helper_Core::dateTime($this->_params['image']['image_date']);
		$this->_params['image']['url'] = XenForo_Link::buildPublicLink('useralbums/view-image', $this->_params['image']);
		$this->_params['image']['urlStandalone'] = XenForo_Link::buildPublicLink('useralbums/standalone', $this->_params['image']);

		$this->_params['imageNeighbours']['next']['url'] = XenForo_Link::buildPublicLink('useralbums/view-image', $this->_params['imageNeighbours']['next']);
		$this->_params['imageNeighbours']['prev']['url'] = XenForo_Link::buildPublicLink('useralbums/view-image', $this->_params['imageNeighbours']['prev']);

		$output = array(
			'avatarUrl' => $avatarUrls['s'],
			'album' => $this->_params['album'],
			'image' => $this->_params['image'],
			'imageNeighbours' => $this->_params['imageNeighbours'],
			'commentsHtml' => $this->createTemplateObject('xfr_useralbums_imagebox_image_comments', $this->_params)->render(),
			'contentHtml' => $this->createTemplateObject('xfr_useralbums_image_content', $this->_params)->render(),

		);

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}