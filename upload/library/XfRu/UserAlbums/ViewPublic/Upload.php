<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Upload.php 77 2011-01-06 20:42:48Z pepelac $ $Date: 2011-01-06 21:42:48 +0100 (Thu, 06 Jan 2011) $ $Revision: 77 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_Upload extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$this->_templateName = 'xfr_useralbums_image_upload_overlay';
	}
}