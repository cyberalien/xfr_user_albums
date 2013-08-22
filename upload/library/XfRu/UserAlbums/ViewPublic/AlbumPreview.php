<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: AlbumPreview.php 78 2011-01-07 15:13:54Z pepelac $ $Date: 2011-01-07 16:13:54 +0100 (Fri, 07 Jan 2011) $ $Revision: 78 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_AlbumPreview extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$previewLength = XenForo_Application::get('options')->XfRu_UA_descrPreviewLength;

		if ($previewLength && !empty($this->_params['album']))
		{
			$formatter = XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_Text');
			$parser = new XenForo_BbCode_Parser($formatter);

			$this->_params['album']['descriptionParsed'] = $parser->render($this->_params['album']['description']);
		}
	}
}