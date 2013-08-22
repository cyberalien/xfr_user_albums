<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: CreateAlbum.php 70 2011-01-03 19:46:43Z pepelac $ $Date: 2011-01-03 20:46:43 +0100 (Mon, 03 Jan 2011) $ $Revision: 70 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_CreateAlbum extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate($this, 'description');
	}
}