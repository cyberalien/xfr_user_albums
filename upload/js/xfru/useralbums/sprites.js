/** @param {jQuery} $ jQuery Object */
//noinspection BadExpressionStatementJS
!function($, window, document, _undefined)
{
	XenForo.XfrSpriteSlider = function($elm)
	{
		var imageSrc = null,
			imageCount = $elm.data('imagecount'),
			spriteHash = $elm.data('spritehash'),
			spriteUrl = $elm.data('spriteurl'),
			spriteHeight = Math.min(imageCount, 10) * 150;

		if (imageCount == 1)
		{
			return;
		}

		$elm.mouseenter(function(e)
		{

			if (imageCount > 1 && spriteHash != '')
			{
				imageSrc = $elm.attr('src');

				$elm.attr('src', spriteUrl);
				$elm.css({
					height: spriteHeight + "px",
					width: '150px',
					top: 0,
					left: 0,
					position: 'relative'
				});
			}

		});

		$elm.mouseleave(function(e)
		{
			if (imageCount > 1 && spriteHash != '')
			{
				$elm.attr('src', imageSrc);
				$elm.css({
					height: '150px',
					width: '150px',
					top: 0,
					left: 0
				});
			}
		});

		$elm.mousemove(function(e)
		{
			if (imageCount > 1 && spriteHash != '')
			{
				var mouseX = e.pageX,
					offset = $elm.offset(),
					imageLeft = offset.left,
					relX = mouseX - imageLeft,
					pixelPerImage = 150 / Math.min(imageCount, 10),
					index = Math.floor(relX / pixelPerImage),
					posOffset = -150 * index;
				$elm.css({left: 0,top: posOffset + "px"});
			}
		});
	};

	XenForo.register('.SpriteSlider', 'XenForo.XfrSpriteSlider');
}
(jQuery, this, document);