<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Tools.php 296 2011-08-23 09:33:21Z pepelac $ $Date: 2011-08-23 11:33:21 +0200 (Tue, 23 Aug 2011) $ $Revision: 296 $
 * @author Pepelac
 *
 */
class XfRu_UserAlbums_Route_PrefixAdmin_Tools implements XenForo_Route_Interface
{

	/**
	 * Method to be called when attempting to match this rule against a routing path.
	 * Should return false if no matching happened or a {@link XenForo_RouteMatch} if
	 * some level of matching happened. If no {@link XenForo_RouteMatch::$controllerName}
	 * is returned, the {@link XenForo_Router} will continue to the next rule.
	 *
	 * @param string                       Routing path
	 * @param Zend_Controller_Request_Http Request object
	 * @param XenForo_Router                  Router that routing is done within
	 *
	 * @return false|XenForo_RouteMatch
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('XfRu_UserAlbums_ControllerAdmin_Tools', $routePath, 'useralbums_tools');
	}
}