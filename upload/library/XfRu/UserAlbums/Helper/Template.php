<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Template.php 63 2010-12-25 12:03:28Z pepelac $ $Date: 2010-12-25 13:03:28 +0100 (Sat, 25 Dec 2010) $ $Revision: 63 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Helper_Template
{
	public function getTemplateContent($type, &$template, $params)
	{
		return call_user_func_array(array($this, $type), array($template, $params));
	}

	private function getUserAlbumsMemberProfileTab(&$template, $params)
	{
		$viewParams['requestUri'] = $params['requestUri'];
		$tpl = new XenForo_Template_Public('xfr_useralbums_member_profile_tab', $viewParams);
		$html = $tpl->render();
		return $html;
	}

	private function getUserAlbumsMemberProfileTabContent(&$template, $params)
	{
		$viewParams['user'] = $params['user'];
		$tpl = new XenForo_Template_Public('xfr_useralbums_member_profile_tab_content', $viewParams);
		$html = $tpl->render();
		return $html;
	}


}