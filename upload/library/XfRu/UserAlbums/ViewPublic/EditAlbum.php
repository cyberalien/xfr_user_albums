<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: EditAlbum.php 74 2011-01-04 22:20:52Z pepelac $ $Date: 2011-01-04 23:20:52 +0100 (Tue, 04 Jan 2011) $ $Revision: 74 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_EditAlbum extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'description', $this->_params['album']['description']
		);
	}
}