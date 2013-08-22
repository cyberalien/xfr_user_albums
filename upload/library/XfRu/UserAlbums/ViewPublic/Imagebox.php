<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Imagebox.php 296 2011-08-23 09:33:21Z pepelac $ $Date: 2011-08-23 11:33:21 +0200 (Tue, 23 Aug 2011) $ $Revision: 296 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_Imagebox extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$avatarUrls = XenForo_Template_Helper_Core::getAvatarUrls($this->_params['album']);
		$this->_params['avatarUrl'] = $avatarUrls['s'];
	}
}