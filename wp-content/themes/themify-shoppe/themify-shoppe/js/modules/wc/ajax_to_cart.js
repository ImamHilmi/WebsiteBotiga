/**
 * Ajax To cart module
 */
;
(function ($, Themify, themifyScript) {
    'use strict';
    // Ajax add to cart   
    let isWorking=false,
        xhr;
	const icons = Themify.body[0].querySelectorAll('#header .icon-shopping-cart');
    Themify.body.on('adding_to_cart', function (e, $button, data) {
        Themify.trigger('themify_theme_spark', [$button]);
        for(let i=icons.length-1;i>-1;--i){
        	icons[i].className+=' tf_loader';
		}
    })
    .on('wc_fragments_loaded', function (e, fragments, cart_hash) {
        const cartButton = document.getElementById('cart-icon-count');
			if(cartButton!==null){
				this.classList.toggle('wc-cart-empty',cartButton.getElementsByClassName('cart_empty')[0]!==undefined);
			}
    })
	.on('added_to_cart', function (e) {
		for(let i=icons.length-1;i>-1;--i){
			icons[i].classList.remove('tf_loader');
		}
		let shopping_cart = Themify.body[0].querySelector('.cart .icon-shopping-cart');
		if ( shopping_cart ) {
			shopping_cart.classList.remove('tf_loader');
		}
		if(themifyScript.ajaxCartSeconds && isWorking===false && !this.classList.contains('post-lightbox')){
			isWorking=true;
			let seconds=parseInt(themifyScript.ajaxCartSeconds);
			if(!Themify.body[0].classList.contains('cart-style-dropdown')){
				const id=Themify.isTouch?'cart-link-mobile-link':'cart-link',
				el=document.getElementById(id);
				if(el!==null){
					const panelId=el.getAttribute('href'),
						panel=document.getElementById(panelId.replace('#',''));
					if(panel!==null){
						Themify.on('sidemenushow.themify', function(panel_id, side,_this){
							if(panelId===panel_id){
								setTimeout(function () {
									if($(panel).is(':hover')){
										panel.addEventListener('mouseleave',function(){
											_this.hidePanel();
											Themify.body[0].classList.remove('tf_auto_cart_open');
										},{once:true,passive:true});
									}else{
										_this.hidePanel();
										Themify.body[0].classList.remove('tf_auto_cart_open');
									}
									isWorking=false;
								},seconds);
							}
						},true);
						Themify.body[0].classList.add('tf_auto_cart_open');
						setTimeout(function(){
							el.click();
						},100);
					}
				}
			}
			else{
				const items=document.getElementsByClassName('shopdock');
				for(let i=items.length-1;i>-1;--i){
					items[i].parentNode.classList.add('show_cart');
					setTimeout(function () {
						items[i].parentNode.classList.remove('show_cart');
						isWorking=false;
					},seconds);
				}
			}
		
		}
	})
    // remove item ajax
    .on('click', '.remove_from_cart_button', function (e) {
        e.preventDefault();
        this.classList.remove('tf_close');
        this.classList.add('tf_loader');
    });
    // Ajax add to cart in single page
    if (!themifyScript.ajaxSingleCart) {
        $(document).on('submit', 'form.cart', function (e) {
            if ($(this).closest('.product-type-external').length) {
                return;
            }
            // WooCommerce Subscriptions plugin compatibility
            if (window.location.search.indexOf('switch-subscription') > -1)
                return this;

            e.preventDefault();

            const data = new FormData(this),
                _orgAjax = $.ajaxSettings.xhr,
                currentLocation = window.location.href;

            if ($(this).find('input[name="add-to-cart"]').length === 0) {
                data.append('add-to-cart', $(this).find('[name="add-to-cart"]').val());
            }

            data.append('action', 'theme_add_to_cart');
            Themify.body.triggerHandler('adding_to_cart', [$(this).find('[type="submit"]'), data]);

            $.ajaxSettings.xhr = function () {
                xhr = _orgAjax();
                return xhr;
            };

            // Ajax action
            $.ajax({
                url: woocommerce_params.ajax_url,
                type: 'POST',
                data: data,
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    if (!response) {
                        return;
                    }
                    if (themifyScript.redirect) {
                        window.location.href = themifyScript.redirect;
                        return;
                    }

                    if (!response.fragments && currentLocation !== xhr.responseURL) {
                        window.location.href = xhr.responseURL;
                        return;
                    }

                    const fragments = response.fragments,
                            cart_hash = response.cart_hash;

                    // Block fragments class
                    if (fragments) {
                        $.each(fragments, function (key, value) {
                            $(key).addClass('updating').replaceWith(value);
                        });
                    }

                    // Trigger event so themes can refresh other areas
                    Themify.body.triggerHandler('added_to_cart', [fragments, cart_hash]);
                }
            });
        });
    }

})(jQuery, Themify, themifyScript);
