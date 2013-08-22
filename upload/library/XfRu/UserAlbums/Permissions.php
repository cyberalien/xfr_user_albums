<?php
/**
 * XenForo User Albums
 *
 * @category XenForo Application
 * @package    XfRu_UserAlbums
 * @copyright  Copyright (c) 2010 XF-Russia. (http://www.xf-russia.ru)
 * @license
 * @version   $Id: Permissions.php 336 2011-11-17 17:59:01Z pepelac $ $Date: 2011-11-17 18:59:01 +0100 (Thu, 17 Nov 2011) $ $Revision: 336 $
 * @author Pepelac
 *
 */

final class XfRu_UserAlbums_Permissions
{
	const PERMISSIONS_GROUP = 'xfrUserAlbumsPermissions';
	private static $permissions = array(
		// user permissions
		'canViewAlbums' => 'xfr_UA_ViewAlbums',
		'canCreateAlbum' => 'xfr_UA_CreateAlbums',
		'canCreateGlobalAlbum' => 'xfr_UA_CreateGlobalAlbums',
		'canEditAlbumsBySelf' => 'xfr_UA_EditAlbumsBySelf',
		'canDeleteAlbumsBySelf' => 'xfr_UA_DeleteAlbumsBySelf',
		'canLikeImages' => 'xfr_UA_LikeImages',
		'canPostComments' => 'xfr_UA_PostCmnts',
		'canEditCommentsBySelf' => 'xfr_UA_EditCmntsBySelf',
		'canDeleteCommentsBySelf' => 'xfr_UA_DeleteCmntsBySelf',
		'canIgnoreUploadLimits' => 'xfr_UA_IgnoreLimit',
		//moderator permissions
		'canViewEmptyAlbums' => 'xfr_UA_ViewEmptyAlbums',
		'canViewPrivateAlbums' => 'xfr_UA_ViewPrivateAlbums',
		'canEditAlbumsByAnyone' => 'xfr_UA_EditAlbumsByAnyone',
		'canDeleteAlbumsByAnyone' => 'xfr_UA_DeleteAlbumsByAny',
		'canEditCommentsByAnyone' => 'xfr_UA_EditCmntsByAnyone',
		'canDeleteCommentsByAnyone' => 'xfr_UA_DeleteCmntsByAny',
		'canApproveUnapprove' => 'xfr_UA_ApproveUnapprove'
	);

	public static function get($key)
	{
		return self::$permissions[$key];
	}

	public static function canViewAlbums()
	{
		$visitor = XenForo_Visitor::getInstance();

		return $visitor->hasPermission(
			XfRu_UserAlbums_Permissions::PERMISSIONS_GROUP,
			XfRu_UserAlbums_Permissions::get('canViewAlbums')
		);
	}

	public static function canCreateAlbum()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can create albums
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canCreateAlbum')
			);
		} else {
			return false;
		}
	}

    public static function canCreateGlobalAlbum()
    {
        $visitor = XenForo_Visitor::getInstance();

        // only logged in user can create albums
        if ($visitor->getUserId())
        {
            return $visitor->hasPermission(
                self::PERMISSIONS_GROUP,
                self::get('canCreateGlobalAlbum')
            );
        } else {
            return false;
        }
    }

	public static function canIgnoreUploadLimits()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can create albums
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canIgnoreUploadLimits')
			);
		} else {
			return false;
		}
	}

	public static function canEditAlbumsBySelf()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canEditAlbumsBySelf')
			);
		} else {
			return false;
		}
	}

	public static function canDeleteAlbumsBySelf()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canDeleteAlbumsBySelf')
			);
		} else {
			return false;
		}
	}

	public static function canLikeImages()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can like images
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canLikeImages')
			);
		} else {
			return false;
		}
	}

	public static function canPostComments()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can post comments
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canPostComments')
			);
		} else {
			return false;
		}
	}


	public static function canEditCommentsBySelf()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can edit comments
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canEditCommentsBySelf')
			);
		} else {
			return false;
		}
	}

	public static function canDeleteCommentsBySelf()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can delete comments
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canDeleteCommentsBySelf')
			);
		} else {
			return false;
		}
	}

	public static function canViewEmptyAlbums()
	{
		$visitor = XenForo_Visitor::getInstance();

		return $visitor->hasPermission(
			self::PERMISSIONS_GROUP,
			self::get('canViewEmptyAlbums')
		);
	}

	public static function canViewPrivateAlbums()
	{
		$visitor = XenForo_Visitor::getInstance();

		return $visitor->hasPermission(
			self::PERMISSIONS_GROUP,
			self::get('canViewPrivateAlbums')
		);

	}

	public static function canEditAlbumsByAnyone()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canEditAlbumsByAnyone')
			);
		} else {
			return false;
		}
	}

	public static function canDeleteAlbumsByAnyone()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canDeleteAlbumsByAnyone')
			);
		} else {
			return false;
		}
	}

	public static function canEditCommentsByAnyone()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can edit comments
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canEditCommentsByAnyone')
			);
		} else {
			return false;
		}
	}

	public static function canDeleteCommentsByAnyone()
	{
		$visitor = XenForo_Visitor::getInstance();

		// only logged in user can delete comments
		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canDeleteCommentsByAnyone')
			);
		} else {
			return false;
		}
	}

	public static function canApproveUnapprove()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($visitor->getUserId())
		{
			return $visitor->hasPermission(
				self::PERMISSIONS_GROUP,
				self::get('canApproveUnapprove')
			);
		} else {
			return false;
		}
	}
}