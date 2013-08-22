<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: ViewUserAlbum.php 103 2011-01-11 22:16:56Z pepelac $ $Date: 2011-01-11 23:16:56 +0100 (Tue, 11 Jan 2011) $ $Revision: 103 $
 * @author Pepelac
 *
 */

class XfRu_UserAlbums_ViewPublic_ViewUserAlbum extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		if (!empty($this->_params['album']))
		{
			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		    $this->_params['album']['descriptionHtml'] = new XenForo_BbCode_TextWrapper(
			    $this->_params['album']['description'],
			    $bbCodeParser
		    );
		}
	}
}