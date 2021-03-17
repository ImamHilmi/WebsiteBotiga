/**
 * Ajax Dropdown Search form module
 */
;
(function($,Themify){
    'use strict';
    const _init=function(opt) {
        const cache = [],
            $container = $(opt.container),
            $input = 'overlay'===opt.type?$container.find('#searchform input'):$(opt.el);
        let xhr,$result_wrapper;


        if('overlay'===opt.type){
            $result_wrapper = $container.find('.search-results-wrap');
            const overlayCallback = function (){
                if ($input.val().length) {
                    $container.addClass('search-active');
                } else {
                    $container.removeClass('search-active')
                }
            }
            $(opt.el).on('click', function (e){
                e.preventDefault();
                overlayCallback();
                $container.fadeIn(function () {
                    $input.focus();
                    Themify.body.css('overflow-y', 'hidden');
                });
                Themify.body.addClass('searchform-slidedown');
            });
            $container.find('.close-search-box').on('click', function (e){
                e.preventDefault();
                overlayCallback();
                if (xhr) {
                    xhr.abort();
                }
                $container.fadeOut();
                Themify.body.css('overflow-y', 'visible').removeClass('searchform-slidedown');
            });
        }else if('dropdown'===opt.type){
            $container.addClass('tf_rel tf_s_dropdown').append('<div class="search-results-wrap tf_ajax_s_dropdown tf_scrollbar" style="display:none"></div>');
            $result_wrapper = $container.find('.search-results-wrap');
            $input.on('blur', function (e){
                if(e.relatedTarget && null!==e.relatedTarget.closest('.search-results-wrap')){
                    return;
                }
                $result_wrapper.hide();
            });

        }

        // Tab click event
        $result_wrapper.on('click', '.search-option-tab a', function (e) {
            e.preventDefault();
            let $href = $(this).attr('href').replace('#', '');
            if ($href === 'all') {
                $href = 'item';
            }
            else {
                $result_wrapper.find('.result-item').stop().fadeOut();
            }
            const $item = $result_wrapper.find('#result-link-' + $href);
            if ($item.length > 0) {
                $('.view-all-button').hide();
                $item.show();
            }
            $result_wrapper.find('.result-' + $href).stop().fadeIn();
            $(this).closest('li').addClass('active').siblings('li').removeClass('active');
            if('dropdown'===opt.type){
                $input.focus();
            }
        });

        // Search input Ajax event
        $input.prop('autocomplete', 'off').on('keyup', function (e) {
            if ($(this).val().length > 0) {
                $container.addClass('search-active');
            } else {
                $container.removeClass('search-active');
                $result_wrapper.hide();
            }
            function set_active_tab(index) {
                if (index < 0) {
                    index = 0;
                }
                $result_wrapper.find('.search-option-tab li').eq(index).children('a').trigger('click');
                $result_wrapper.show();
            }
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || e.keyCode === 8 || e.keyCode === 229) {
                let $v = $.trim($(this).val());
                if ($v) {
                    if (cache[$v]) {
                        $result_wrapper.hide().html(cache[$v]);
                        set_active_tab($result_wrapper.find('.search-option-tab li.active').index());
                        return;
                    }
                    setTimeout(function () {
                        $v = $.trim($input.val());
                        if (xhr) {
                            xhr.abort();
                        }
                        if (!$v) {
                            $result_wrapper.html('');
                            return;
                        }

                        xhr = $.ajax({
                            url: themify_vars.ajax_url,
                            type: 'POST',
                            data: {'action': 'themify_search_autocomplete', 'term': $v, 'post_type':opt.post_type?opt.post_type:''},
                            beforeSend: function () {
                                $container.addClass('themify-loading');
                            },
                            complete: function () {
                                $container.removeClass('themify-loading');
                            },
                            success: function (resp) {
                                if (!$v) {
                                    $result_wrapper.html('');
                                }else if (resp) {
                                    $result_wrapper.hide().html(resp);
                                    set_active_tab($result_wrapper.find('.search-option-tab li.active').index());
                                    $result_wrapper.find('.search-option-tab li.active')
                                    cache[$v] = resp;
                                }
                            }
                        });
                    }, 100);
                }
                else {
                    $result_wrapper.html('');
                }
            }
        });
    };
    Themify.on('themify_overlay_search_init',function(opt){
        _init(opt);
    });
})(jQuery,Themify);