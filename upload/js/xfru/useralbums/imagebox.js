//noinspection BadExpressionStatementJS
/**
 * @param {jQuery} $ jQuery Object
 */
!function($, window, document, _undefined)
{
	XenForo.XfrImageBox = function(Overlay, containerSelector) { this.__construct(Overlay, containerSelector); };
	XenForo.XfrImageBox.prototype =
	{
		__construct: function(Overlay, containerSelector)
		{
			this.Overlay = Overlay;
			this.containerSelector = containerSelector;
		},

		/**
		 * Calculates the maximum allowable height of the lightbox image
		 *
		 * @returns {XenForo.XfrImageBox}
		 */
		setImageMaxHeight: function()
		{
			var maxHeight = 700;

			console.log('Setting LightBoxImage max height = %d', maxHeight);

			$('#LbImage').css('max-height', maxHeight);
			return this;
		},

		setOverlayPosition: function()
		{
			$('.lightBox').css('position', 'absolute');
		},

		/**
		 * Loads data and sets a new image to be shown in the lightbox
		 *
		 * @param jQuery $trigger
		 *
		 * @returns {XenForo.XfrImageBox}
		 */
		setData: function($trigger)
		{
			var dataSource = $trigger.attr('href') || $trigger.data('href'),
				$lightBox = $('#LightBox'),
				$lightBoxImage = $('#LbImage'),
				animateWindow = false,
				animateSpeed = (XenForo.isTouchBrowser() ? 0 : XenForo.speed.fast),
				context = this;

			if (dataSource == $lightBox.data('loadedData'))
			{
				console.log('Requested image is already displayed: %s.', dataSource);
				return this;
			}

			console.log('setData to %s from %o', dataSource, $lightBox.data('loadedData'));

			$lightBoxImage.fadeTo(0, 0, function()
			{
				$('#LbProgress').show();
				$lightBox.data('loadedData', dataSource);
				XenForo.ajax(dataSource, null, $.context(context, 'processLoadedData'));
			}).one('load', function(e)
			{
				$('#LbProgress').hide();

				if (animateWindow)
				{
					$lightBoxImage.css('height', 40).closest('.image').animate({ height: $lightBoxImage.height() }, 0, function()
					{
						$lightBoxImage.css('height', 'auto').fadeTo(animateSpeed, 1);
					});
				}
				else
				{
					$lightBoxImage.fadeTo(animateSpeed, 1);
				}

				var height = Math.max($('body').height(), $('.lightBox').height(), $(window).height());
				$('#exposeMask').css('height', (height + 30));
			});
		},

		processLoadedData: function(ajaxData, textStatus)
		{
			console.log('XenForo.XfrImageBox.processLoadedData called');

			this.setContentLink(ajaxData);
			this.setNavLinks(ajaxData);
			$('#LbAvatar img').attr('src', ajaxData.avatarUrl);
			$('#LbUsername').text(ajaxData.album.title);
			$('#LbDateTime').text(ajaxData.image.dateFormatted);
			$('#LbNewWindow').attr('href', ajaxData.image.urlStandalone);
			$('#LbImage').attr('src', ajaxData.image.urlStandalone);
			$('#LbSelectedImage').text(ajaxData.imageNeighbours.current);
			$('#LbTotalImages').text(ajaxData.imageNeighbours.total);

			this.prepareHtml(ajaxData.contentHtml, '#LbImageContent');
			this.prepareHtml(ajaxData.commentsHtml, '#LbComments');

			if (ajaxData.imageNeighbours.total < 2)
			{
				$('#LightBox .imageNav').hide();
			}
		},

		prepareHtml: function(html, elementSelector)
		{
			$(elementSelector).html(html).find('a.OverlayTrigger').addClass('XfrPopupTrigger').removeClass('OverlayTrigger');
			$(elementSelector + ' .DateTime').parent().click(function(e){e.preventDefault();});
			$(elementSelector + ' a.avatar, ' + elementSelector + ' a.username').addClass('NoOverlay XfrPopupTrigger');
			$(elementSelector).xfActivate();
		},

		setContentLink: function(data)
		{
			if (data.image.url)
			{
				$('#LbContentLink, #LbDateTime').attr('href', data.image.url);
			} else {
				$('#LbContentLink').text('').removeAttr('href');
			}
			return this;
		},

		setNavLinks: function(data)
		{
			$('#LbPrev, #LbPrev span').data('href', data.imageNeighbours.prev.url);
			$('#LbImage, #LbNext, #LbNext span').data('href', data.imageNeighbours.next.url);
		},

		imageNavClick: function(e)
		{
			console.log('XenForo.XfrImageBox.imageNavClick called');
			e.preventDefault();
			this.setData($(e.target));
		}
	};

	XenForo.XfrImageBoxTrigger = function($link)
    {
        var containerSelector = '.thumbnailList';

        new XenForo.OverlayTrigger($link.data('cacheOverlay', 0),
        {
            top: 15,
            speed: 1, // prevents the onLoad event being fired prematurely
            closeSpeed: 0,
            closeOnResize: true,
            mask:
            {
                color: 'rgb(0,0,0)',
                opacity: 0.6,
                loadSpeed: 0,
                closeSpeed: 0
            },
            onBeforeLoad: function(e)
            {

                if (typeof XenForo.XfrImageBox == 'function')
                {
                    if (XenForo._LightBoxObj === undefined)
                    {
                        XenForo._LightBoxObj = new XenForo.XfrImageBox(this, containerSelector);
                    }

                    var $imageContainer = (parseInt(XenForo._lightBoxUniversal) ? $(document) : $link.closest(containerSelector));
                    console.info('Opening LightBox for %o', $imageContainer);
					XenForo._LightBoxObj.setData(this.getTrigger());
                    $(document).triggerHandler('LightBoxOpening');
                }

                return true;
            },

	        onLoad: function(e)
            {
	            $('#LbPrev, #LbNext, #LbImage').click($.context(XenForo._LightBoxObj, 'imageNavClick'));
                XenForo._LightBoxObj.setImageMaxHeight();
	            XenForo._LightBoxObj.setOverlayPosition();
	            window.scrollTo(0, 0);
	            console.log('Lightbox: %o', XenForo._LightBoxObj);
	            $(XenForo._LightBoxObj).resize(function(){
		            var height = Math.max($('body').height(), $('.lightBox').height(), $(window).height());
					$('#exposeMask').css('height', (height + 30));
	            });
                return true;
            }
        });
    };

	XenForo.XfrPopupTrigger = function($link)
	{
		$link.click(function(e)
		{
			// todo: support overlay over lightbox
		});
	};

	XenForo.register('a.xfrIbTrigger', 'XenForo.XfrImageBoxTrigger');
	XenForo.register('a.XfrPopupTrigger', 'XenForo.XfrPopupTrigger');
}
(jQuery, this, document);