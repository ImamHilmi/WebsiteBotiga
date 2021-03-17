(function($, window, document){
	'use strict';
	var is_working = false;

	function setCookie( cname, cvalue, exdays ) {
		var d = new Date();
		d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
		var expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	
	function ThemifyLazy(el){
		if( typeof Themify !== 'undefined' ) {
			if(el instanceof jQuery){
				el=el[0];
			}
			Themify.lazyScroll(Themify.convert(el.querySelectorAll('[data-lazy]')).reverse(),true);
		}
	}

	function getCookie( cname ) {
		var name = cname + "=",
			decodedCookie = decodeURIComponent( document.cookie ),
			ca = decodedCookie.split( ';' );
		for ( var i = 0; i <ca.length; i++ ) {
			var c = ca[i];
			while ( c.charAt(0) == ' ' ) {
				c = c.substring(1);
			}
			if ( c.indexOf( name ) == 0 ) {
				return c.substring( name.length, c.length );
			}
		}
		return "";
	}

	function getScrollPercent() {
		var h = document.documentElement, 
			b = document.body,
			st = 'scrollTop',
			sh = 'scrollHeight';
		return (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;
	}

	function triggerEvent(a, b) {
		var c;
		document.createEvent ? (c = document.createEvent('HTMLEvents'), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
	}

	function open( $el ) {
		// keep track of how many times visitor has seen this popup
		var view_count = getCookie( "themify-popup-" + $el.data( 'object-id' ) );
		if ( view_count !== '' && parseInt( view_count ) !== NaN ) {
			view_count = parseInt( view_count );
		} else {
			view_count = 0;
		}

		// visitor has seen this popup already? Bail!
		if ( $el.data( 'limit-count' ) && view_count >= $el.data( 'limit-count' ) ) {
			return;
		}

		setCookie( "themify-popup-" + $el.data( 'object-id' ), view_count + 1, $el.data( 'cookie-expiration' ) );

		var style = $el.data( 'style' ),
		classes = 'themify-popup-showing themify-popup-style-' + style + ' themify-popup-showing-' + $el.data( 'object-id' ) + ' tf-popup-position-' + $el.data( 'position' );
		$el.show();
		if( style == 'classic' || style == 'fullscreen' ) {

			// do not display the popup if one is showing already
			if ( $( 'body' ).hasClass( 'themify-popup-showing' ) || is_working ) {
				return;
			}
			is_working = true;

			var magnificCallback = function() {

				$.magnificPopup.open({
					closeOnBgClick : ( style == 'fullscreen' || ( style == 'classic' && $el.data( 'close-overlay' ) == 'no' ) ) ? false : true,
					enableEscapeKey : $el.data( 'enableescapekey' ) === 'yes',
					removalDelay: 1000,
					items: {
						src: $el,
						type: 'inline'
					},
					callbacks : {
						open : function(){
							apply_animation( $( '.mfp-wrap .mfp-content' ), $el.data( 'animation' ) );

							$( 'body' ).addClass( classes );
							
							ThemifyLazy($el);

							// move close button to the top-right corner of the screen
							$( '.mfp-close' ).addClass( 'themify-popup-close' ).appendTo( $( '.mfp-content' ) );

							/* force elements inside the popup to respond to the popup being opened */
							triggerEvent( window, 'resize' );

							is_working = false;
						},
						beforeClose: function(){
							var el = $( '.mfp-wrap .mfp-content' );
							apply_animation( el, $el.data( 'animation-exit' ), function(){
								el.hide();
							} );
							$("video,audio",this.contentContainer).each(function() {
								$(this).get(0).pause();
							});
						},
						close : function(){
							$( 'body' ).removeClass( classes );
						}
					}
				});
			};
			if ( typeof $.fn.magnificPopup === 'function' ) {
				magnificCallback();
			} else {
				$.getScript( themifyPopup.assets + '/lightbox.min.js', function() {
					magnificCallback();
				} );
			}
		} else if ( style == 'slide-out' ) {
			slide_out_fix_position( $el );
			$( window ).resize( function(){
				slide_out_fix_position( $el );
			} );
			$el.show();
			apply_animation( $el, $el.data( 'animation' ) );
			$el.append( '<button class="themify-popup-close">x</button>' );
			ThemifyLazy($el);
		}

		if( $el.data( 'auto-close' ) ) {
			setTimeout(function() {
				close( $el );
			}, $el.data( 'auto-close' ) * 1000 );
		}
	}

	function apply_animation( $el, name, callback ) {
		if ( name === 'none' ) {
			if ( callback ) {
				callback();
			}
		} else {
			$el.one( 'animationend', function(){
					// remove the animation classes after animation ends
					// required in order to apply new animation on close
					$( this ).removeClass( 'animated ' + name );
					if( callback ) {
						callback();
					}
				} ).addClass( 'animated ' + name );
		}
	}

	function slide_out_fix_position( $el ) {
		if( $el.hasClass( 'bottom-center' ) || $el.hasClass( 'top-center' ) ) {
			$el.css( 'marginLeft', ( ( $el.width() / 2 ) * -1 ) + 'px' );
		} else if ( $el.hasClass( 'center-left' ) || $el.hasClass( 'center-right' ) ) {
			$el.css( 'marginTop', ( ( $el.height() / 2 ) * -1 ) + 'px' );
		}
	}

	function close( $el ) {
		var style = $el.data( 'style' );
		if( style == 'classic' || style == 'fullscreen' ) {
			$( '.mfp-close' ).click();
		} else if ( style == 'slide-out' ) {
			$el.find( '.themify-popup-close' ).click();
		}
	}

	/**
	 * Keep track of how many pages of the website the visitor has seen.
	 * This is saved in the "themify_popup_page_view" cookie.
	 */ 
	function update_page_view_counter() {
		var counter = getCookie( 'themify_popup_page_view' ) || 1;
		setCookie( 'themify_popup_page_view', ++counter, 1 );
	}

	/* handle close button for Slide Out popups, and custom close button */
	$( 'body' ).on( 'click', '.themify-popup .themify-popup-close', function(){
		var popup = $( this ).closest( '.themify-popup' );
		if ( popup.length ) {
			var style = popup.data( 'style' );
			if( style == 'classic' || style == 'fullscreen' ) {
				$( this ).closest( '.mfp-wrap' ).find( '.mfp-close' ).click();
			} else if ( style == 'slide-out' ) {
				apply_animation( popup, popup.data( 'animation-exit' ), function() {
					popup.hide();
				} );
			}
		}
	} );

	$(function(){
		if ( typeof themifyPopupCountViews !== 'undefined' && themifyPopupCountViews === '1' ) {
			update_page_view_counter();
		}
		var ev=(('ontouchstart' in window) ||  navigator.msMaxTouchPoints > 0) ? 'touchstart' : 'mousemove';
		$( '.themify-popup' ).each( function(){
			var $this = $( this ),
			type=$this.data( 'trigger' );

			// manual trigger, open popup when a link calls it
			$( 'body' ).on( 'click', '[href="#themify-popup-' + $this.data( 'object-id' ) + '"]', function(e){
				e.preventDefault();
				open( $this );
			} );

			if ( type === 'default' || type === 'pageview' ) {
				// automatic trigger. "default" is kept for backwards compatibility
				open( $this );
			} else if( type === 'timedelay' ) {
				setTimeout(function() {
					open( $this );
				}, $this.data( 'time-delay' ) * 1000 );
			} else if( type === 'scroll' ) {
				var position = $this.data( 'scroll-position' ),
					on = $this.data( 'scroll-on' );
				$( window ).scroll( function(){
					if( position > 0 ) {
						if( ( on == 'px' && window.scrollY > position ) || ( on == '%' && getScrollPercent() > position ) ) {
							open( $this );
							position = -1; // prevent the popup from being displayed again
						}
					}
				} );
			} else if( type === 'exit' ) {
				var show = true;
				document.addEventListener(ev, function(e) {
					// Get current scroll position
					var scroll = window.pageYOffset || document.documentElement.scrollTop,
						y=e.touches?e.touches[0].clientY:e.clientY;
					if ( show && ( y - scroll ) < 7) {
						open( $this );
						show = false;
					}
				},{passive:true});
			}
		});
	});

})(jQuery, window, document);
