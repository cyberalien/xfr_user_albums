<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ImageEditInline.php 144 2011-03-13 21:18:48Z pepelac $ $Date: 2011-03-13 22:18:48 +0100 (Sun, 13 Mar 2011) $ $Revision: 144 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ImageEditInline extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'message',
			$this->_params['image']['description'],
			array('editorId' => 'message' . $this->_params['image']['image_id'])
		);
	}
}