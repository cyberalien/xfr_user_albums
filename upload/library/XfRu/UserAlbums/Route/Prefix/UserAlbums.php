<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: UserAlbums.php 294 2011-08-22 07:04:59Z pepelac $ $Date: 2011-08-22 09:04:59 +0200 (Mon, 22 Aug 2011) $ $Revision: 294 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_Route_Prefix_UserAlbums implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
		$routePath = trim($routePath, '/');

		$parts = explode('/', $routePath);
	    @list($id, $action) = $parts;

		$field = $this->getField($action);

	    $action = $router->resolveActionWithIntegerParam($routePath, $request, $field[0]);
		$param = '';

	    return $router->getRouteMatch($this->getController($action), $action, $this->getMajorSection(), $this->getMinorSection($action, $param));
    }

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$field = '';
		if (strpos($action, '/') !== false)
		{
			list($prefix, $action) = explode('/', $action, 2);
		    $outputPrefix .= '/'.$prefix;
		    $field = $this->getField($prefix);
		} else {
			$field = $this->getField($action);
		}

		if (!empty($data['temp_hash']) && empty($data['album_id']))
		{
			$extraParams['temp_hash'] = $data['temp_hash'];
		}

		$hashedActions = array(
			'view', 'preview', 'view-image', 'standalone', 'report-image', 'like-image', 'likes-image'
		);

		if (!empty($data['access_hash']) &&
			($data['album_type'] == 'private' && in_array($action, $hashedActions)))
		{
			$extraParams['access_hash'] = $data['access_hash'];
		}

		if (array_key_exists('title', $data))
		{
			$data['title'] = XenForo_Helper_String::censorString($data['title']);
		}

		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $field[0], $field[1]);
	}

	private function getController($key)
	{
		$controller = '';
		switch ($key)
		{
			// images common
			case 'manage-images' :
			case 'upload' :
			case 'do-upload' :
			case 'delete-image' :
				$controller = 'XfRu_UserAlbums_ControllerPublic_Images'; break;
			// image
			case 'standalone' :
			case 'view-image' :
			case 'show-image' :
			case 'edit-image-descr' :
			case 'save-image-descr' :
			case 'edit-image-descr-inline' :
			case 'save-image-descr-inline' :
			case 'report-image' :
			case 'like-image' :
			case 'likes-image' :
			case 'unapprove-image' :
			case 'delete-image-confirm' :
			case 'imagebox' :
				$controller = 'XfRu_UserAlbums_ControllerPublic_Image'; break;
			// comments
			case 'image-comments' :
			case 'image-add-comment' :
			case 'comment-edit' :
			case 'comment-save' :
			case 'comment-delete' :
			case 'comment-report' :
				$controller = 'XfRu_UserAlbums_ControllerPublic_Comments'; break;
			// albums
			default: $controller = 'XfRu_UserAlbums_ControllerPublic_Albums'; break;
		}

	    return $controller;
	}

	private function getField($key)
	{
		$field = '';
		switch ($key)
		{
			case 'list' : $field = array('user_id', 'username'); break;
			case 'standalone' :
			case 'view-image' :
			case 'show-image' :
			case 'edit-image-descr' :
			case 'save-image-descr' :
			case 'edit-image-descr-inline' :
			case 'save-image-descr-inline' :
			case 'report-image' :
			case 'like-image' :
			case 'likes-image' :
			case 'unapprove-image' :
			case 'image-add-comment' :
			case 'delete-image-confirm' :
			case 'imagebox' :
			case 'delete-image' : $field = array('image_id', 'title'); break;
			case 'image-comments' :
			case 'comment-edit' :
			case 'comment-save' :
			case 'comment-delete' :
			case 'comment-report' :
				$field = array('comment_id', null); break;
		    default : $field = array('album_id', 'title'); break;
		}

	    return $field;
	}

	private function getMinorSection($key, $param)
	{
		if ($key == 'index') return '';
		$section = '';
	    $section .= (!empty($param)) ? $param : '' ;
	    $section .= (!empty($key)) ? '/'.$key : '' ;
	    return $section;
	}

	private function getMajorSection($key = '')
	{
		$section = 'useralbums';
	    $section .= (!empty($key)) ? '/'.$key : '' ;
	    return $section;
	}
}