<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Tab.php 186 2011-04-03 13:47:51Z pepelac $ $Date: 2011-04-03 15:47:51 +0200 (Sun, 03 Apr 2011) $ $Revision: 186 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_EventListener_Tab
{
	public static function addTab(array &$extraTabs, $selected)
	{
		if (XfRu_UserAlbums_Permissions::canViewAlbums())
		{
			if (!XenForo_Visitor::getUserId() && XenForo_Application::get('options')->XfRu_UA_hideMenuTabToGuests)
			{
				return;
			}
			$extraTabs['useralbums'] =array(
				'title' => new XenForo_Phrase('xfr_useralbums_user_albums'),
				'href' =>  XenForo_Link::buildPublicLink('useralbums'),
				'position' => 'middle',
				'selected' => ($selected == 'useralbums'),
				'linksTemplate' => 'xfr_useralbums_links',
			);
		}
	}
}