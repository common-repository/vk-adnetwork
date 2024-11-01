jQuery( document ).ready( function ( $ ) {
    function vk_adnetwork_load_ad_type_parameter_metabox ( ad_type ) {
        jQuery( '#vk-adnetwork-ad-type input' ).prop( 'disabled', true )
        $( '#vk-adnetwork-ad-parameters' ).html( '<span class="spinner vk_adnetwork-ad-parameters-spinner vk_adnetwork-spinner"></span>' )
        $.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'vk_adnetwork_load_ad_parameters_metabox',
                'ad_type': ad_type,
                'ad_id': $( '#post_ID' ).val(),
                'nonce': vk_adnetwork_global.ajax_nonce
            },
            success: function ( data, textStatus, XMLHttpRequest ) {
                // toggle main content field.
                if ( data ) {
                    $( '#vk-adnetwork-ad-parameters' ).html( data ).trigger( 'paramloaded' )
                    vk_adnetwork_maybe_textarea_to_tinymce( ad_type )
                }
            },
            error: function ( MLHttpRequest, textStatus, errorThrown ) {
                $( '#vk-adnetwork-ad-parameters' ).html( errorThrown )
            }
        } ).always( function ( MLHttpRequest, textStatus, errorThrown ) {
            jQuery( '#vk-adnetwork-ad-type input[name^="vk_adnetwork"]' ).prop( 'disabled', false );
        } )
    }

    $( document ).on( 'change', '#vk-adnetwork-ad-type input', function () {
        var ad_type = $( this ).val()
        vk_adnetwork_load_ad_type_parameter_metabox( ad_type )
    } )

    // trigger for ad injection after ad creation
    // АКА Выравнивание блока по вертикали [6]: вверху, всерёдке, внизу (инпейдж); футер (970); сайдбар (300х600, 300х250); шорткод
    $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-ad-injection-box-option-input' ).on( 'click', function () {
        var placement_type = this.dataset.placementType, // create new placement
            placement_slug = this.dataset.placementSlug, // use existing placement
            options        = {}

        if ( ! placement_type && ! placement_slug ) { return }

        // create new placement
        if ( placement_type ) {
            // for content injection
            if ( 'post_content' === placement_type ) {
                var paragraph = prompt( vk_adnetwork_txt.after_paragraph_promt, 1 )
                if ( paragraph !== null ) {
                    options.index = parseInt( paragraph, 10 )
                }
            }
        }

        // DL: только когда выбрано местоположение default (АКА ручная вставка кода/шортката)
        // показываем блоки с кодом/шорткатом (в других вариантах расположения -- прячем)
        // ++ /admin/views/placement-injection-top.php:56 (до всех кликов -- показывать или нет)
        if ( 'default' === placement_type ) {
            $( '#vk_adnetwork-usage-shortcode-group' ).show()
            $( '#vk_adnetwork-usage-function-group' ).show()
            $( '.vk_adnetwork-usage' ).show()
            // ВСЕГДА ПРЯЧЕМ: Когда вы нажмёте кнопку <b>Сохранить</b> или <b>Опубликовать</b> вы попадете в
            $( '#vk_adnetwork-description-for-widgets-php-span' ).hide()
        }else{
            $( '#vk_adnetwork-usage-shortcode-group' ).hide()
            $( '#vk_adnetwork-usage-function-group' ).hide()
            $( '.vk_adnetwork-usage' ).hide()
            // только НОВОМУ блоку покажем: Когда вы нажмёте кнопку <b>Сохранить</b> или <b>Опубликовать</b> вы попадете в
            // (у старых блоков выбор формата блока -- disabled)
            if (! $('.vk_adnetwork-ad-injection-box-option-input')[0].disabled ) {
                $('#vk_adnetwork-description-for-widgets-php-span').show()
            }
        }

        // $( '#vk_adnetwork-ad-injection-box-placements' ).hide()
        $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-loader' ).show()

        $.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'vk_adnetwork-ad-injection-content',
                placement_type: placement_type,
                placement_slug: placement_slug,
                ad_id: $( '#post_ID' ).val(),
                options: options,
                nonce: vk_adnetwork_global.ajax_nonce
            },
            success: function ( r, textStatus, XMLHttpRequest ) {
                if ( ! r ) {
                    $( '#vk_adnetwork-ad-injection-box' ).html( 'an error occured' )
                    return
                }

                // $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-ad-injection-button' ).css('border', 'none')

                // DL: выделяю выбранное для объявления местоположение более жирным бордюром \/
                // $( '#vk_adnetwork-ad-injection-button-' +  placement_type).css('border', 'solid 5px #0085ba')

                $( '#vk_adnetwork-ad-injection-box .vk_adnetwork-loader' ).hide()
                $( 'body' ).animate( { scrollTop: $( '#vk_adnetwork-ad-injection-box' ).offset().top - 40 }, 1, 'linear' )

                // DL: записываю в Notes (АКА Заметки) тип расположения в квадратных скобках (или заменяю то, которое было до того
                // (это чтобы в списке объявлений было видно какая объява где расположена)
                // var mun = $( '#vk_adnetwork-usage-notes' )
                // var pt = '[ ' + placement_type + ' ' + (options.index || '') + ' ]'
                // if (mun.val().indexOf('[') !== -1 && mun.val().indexOf(']') !== -1 ) {
                // 	mun.val( mun.val().replace(/\[[^\]]*\]/, pt) )
                // }else{
                // 	mun.val( mun.val() + ' ' + pt )
                // }
                // $( '#vk_adnetwork-ad-notes .js-vk_adnetwork-ad-notes-title' )[0].innerHTML = mun.val() + ' <span class="dashicons dashicons-edit"></span>'

                // $( '#vk_adnetwork-ad-injection-box *' ).hide()
                // // append anchor to placement message
                $( '#vk_adnetwork-ad-injection-message-placement-created .vk_adnetwork-placement-link' ).attr( 'href', $( '#vk_adnetwork-ad-injection-message-placement-created a' ).attr( 'href' ) + r )
                $( '#vk_adnetwork-ad-injection-message-placement-created, #vk_adnetwork-ad-injection-message-placement-created *' ).show()
            },
            error: function ( MLHttpRequest, textStatus, errorThrown ) {
                $( '#vk_adnetwork-ad-injection-box' ).html( errorThrown )
            }
        } ).always( function ( MLHttpRequest, textStatus, errorThrown ) {
            // jQuery( '#vk-adnetwork-ad-type input').prop( 'disabled', false );
        } )
    } )

    // activate general buttons
    $( '.vk_adnetwork-buttonset' ).vk_adnetwork_buttonset()
    // activate accordions
    if ( $.fn.accordion ) {
        $( '.vk_adnetwork-accordion' ).accordion( {
            active: false,
            collapsible: true,
        } )
    }

    // AD OVERVIEW PAGE

    $( '.vk_adnetwork-ad-list-tooltip' ).vk_adnetwork_tooltip( {
        content: function () {
            return jQuery( this ).find( '.vk_adnetwork-ad-list-tooltip-content' ).html()
        }
    } )
    // show edit icon in the last head column
    $( '.post-type-vk_adnetwork .wp-list-table thead th:last-of-type' ).append( '<span class="dashicons dashicons-edit"></span>' ).on( 'click', function() {
        $( '#show-settings-link' ).trigger( 'click' );
    } );

    /**
     * Logic for placement list
     */
    ( () => {
        let selectedValue          = '0';
        let searchTerm             = '';
        const placementRows        = jQuery( '.vk_adnetwork-placements-table tbody tr' );
        const showHidePlacementRow = callback => {
            placementRows.each( ( index, element ) => {
                const $row    = jQuery( element );
                const rowData = $row.data( 'order' );

                if (
                    typeof rowData === 'undefined'
                    || typeof rowData['type'] === 'undefined'
                    || typeof rowData['name'] === 'undefined'
                ) {
                    $row.show();
                    return;
                }

                $row.toggle(
                    ( selectedValue === '0' || rowData['type'] === selectedValue )
                    && ( searchTerm === '' || rowData['name'].toLowerCase().indexOf( searchTerm.toLowerCase() ) !== - 1 )
                );
            } );
        };
        // filter placement by type
        jQuery( '.vk_adnetwork_filter_placement_type' ).on( 'change', function () {
            selectedValue = jQuery( this ).val();
            showHidePlacementRow();
        } );

        // search placement by name
        jQuery( '.vk_adnetwork_search_placement_name' ).on( 'keyup', function () {
            searchTerm = this.value;
            showHidePlacementRow();
        } );
    } )();

    /**
     * Filter ad/ad group selection in new placement form.
     */
    ( () => {
        const placementTypeRadios = document.querySelectorAll( '[name="vk_adnetwork[placement][type]"]' );

        placementTypeRadios.forEach( radio => {
            radio.addEventListener( 'input', event => {
                jQuery( '[name="vk_adnetwork[placement][item]"]' ).attr( 'disabled', true );

                wp.ajax.post( window.vk_adnetwork_txt.placements_allowed_ads.action, {
                    '_ajax_nonce':    window.vk_adnetwork_txt.placements_allowed_ads.nonce,
                    'placement_type': event.target.value
                } )
                    .done( response => {
                        jQuery( '[name="vk_adnetwork[placement][item]"]' )
                            .replaceWith( wp.template( 'vk_adnetwork-placement-ad-select' )( {items: Object.values( response.items )} ) );
                    } );
            } );
        } );
    } )();

    jQuery( '.vk_adnetwork-delete-tag' ).each( function () {
        jQuery( this ).on( 'click', function () {
            var r = confirm( window.vk_adnetwork_txt.delete_placement_confirmation );
            if ( r === true ) {
                var row = jQuery( this ).parents( '.vk-adnetwork-placement-row' );
                row.find( '.vk_adnetwork-placements-item-delete' ).prop( 'checked', true );
                row.data( 'touched', true );
                jQuery( '#vk-adnetwork-placements-form' ).submit();
            }
        } );
    } );

    // sort placement by type order or name
    jQuery( '.vk_adnetwork-sort' ).on( 'click', function ( e ) {
        var sort    = jQuery( this );
        var orderBy = sort.data( 'orderby' );
        var table   = jQuery( '.vk_adnetwork-placements-table' );
        var rows    = jQuery( '> tbody > tr', table );
        var links   = jQuery( '> thead th > a', table );
        links.each( function () {
            jQuery( this ).removeClass( 'vk_adnetwork-placement-sorted' );
        } );
        sort.addClass( 'vk_adnetwork-placement-sorted' );
        rows.sort( function ( a, b ) {
            let orderA = jQuery( a ).data( 'order' );
            let orderB = jQuery( b ).data( 'order' );

            if ( orderBy === 'type' ) {
                if ( orderA['words-between-repeats'] !== orderB['words-between-repeats'] ) {
                    return orderA['words-between-repeats'] ? 1 : -1;
                }

                if ( orderA['order'] === orderB['order'] ) {
                    // Sort by index.
                    if ( orderA['post-content-index'] && orderB['post-content-index'] && orderA['post-content-index'] !== orderB['post-content-index'] ) {
                        return (orderA['post-content-index'] < orderB['post-content-index'] ) ? -1 : 1;
                    }
                    // Sort by name.
                    return orderA['name'].localeCompare( orderB['name'], undefined, { numeric: true } );
                }
                return orderA['order'] - orderB['order'];
            }

            return orderA['name'].localeCompare( orderB['name'], undefined, { numeric: true } );
        } );
        jQuery.each( rows, function ( index, row ) {
            table.append( row );
        } );
        var url   = window.location.pathname + window.location.search;

        if ( url.indexOf( 'orderby=' ) !== - 1 ) {
            url = url.replace( /\borderby=[0-9a-zA-Z_@.#+-]{1,50}\b/, 'orderby=' + orderBy );
        } else {
            url += '&orderby=' + orderBy;
        }
        window.history.replaceState( {orderby: orderBy}, document.title, url );
        e.preventDefault();
    } );

    // show warning if Container ID option contains invalid characters
    $( '#vk_adnetwork-output-wrapper-id' ).on( 'keyup', function () {
        var id_value = $( this ).val()
        if ( /^[a-z-0-9]*$/.test( id_value ) ) {
            $( '.vk_adnetwork-output-wrapper-id-error' ).addClass( 'hidden' )
        } else {
            $( '.vk_adnetwork-output-wrapper-id-error' ).removeClass( 'hidden' )
        }
    } )

    /**
     * SETTINGS PAGE
     */

    /**
     * There are two formats of URL supported:
     * admin.php?page=vk-adnetwork-settings#top#tab_id     go to the `tab_id`
     * admin.php?page=vk-adnetwork-settings#tab_id__anchor go to the `tab_id`, scroll to the `anchor`
     */

    /**
     * Extract the active tab and anchor from the URL hash.
     *
     * @var {string} hash The URL hash.
     *
     * @return {{tab: string, anchor: string}}
     */
    function vk_adnetwork_extract_tab( hash ) {
        var hash_parts = hash.replace( /^#top(#|%23)/, '' ).replace( /(#|%23)/, '' ).split( '__' );

        return {
            'tab':    hash_parts[0] || jQuery( '.vk_adnetwork-tab' ).attr( 'id' ),
            'anchor': hash_parts[1]
        };
    }

    /**
     * Set the active tab and optionally scroll to the anchor.
     */
    function vk_adnetwork_set_tab( tab ) {
        jQuery( '#vk_adnetwork-tabs' ).find( 'a' ).removeClass( 'nav-tab-active' );
        jQuery( '.vk_adnetwork-tab' ).removeClass( 'active' );

        jQuery( '#' + tab.tab ).addClass( 'active' );
        jQuery( '#' + tab.tab + '-tab' ).addClass( 'nav-tab-active' );

        if ( tab.anchor ) {
            var anchor_offset = document.getElementById( tab.anchor ).getBoundingClientRect().top;
            var admin_bar     = 48;
            window.scrollTo( 0, anchor_offset + window.scrollY - admin_bar );
        }
    }

    // While user is already on the Settings page, find links (in admin menu,
    // in the Checks at the top, in the notices at the top) to particular setting tabs and open them on click.
    jQuery( document ).on( 'click', 'a[href*="page=vk-adnetwork-settings"]:not(.nav-tab)', function () {
        // Already on the Settings page, so set the new tab.
        // Extract the tab id from the url.
        var url = jQuery( this ).attr( 'href' ).split( 'vk-adnetwork-settings' )[1];
        var tab = vk_adnetwork_extract_tab( url );
        vk_adnetwork_set_tab( tab );
    } );

    /**
     * Handle the hashchange event, this enables back/forward navigation in the settings page.
     */
    window.addEventListener( 'hashchange', event => {
        const hash = vk_adnetwork_extract_tab( new URL( event.newURL ).hash );
        try {
            document.getElementById( hash.tab + '-tab' ).dispatchEvent( new Event( 'click' ) );
        } catch ( e ) {
            // fail silently if element does not exist.
        }
    } );

    // activate specific or first tab

    var active_tab = vk_adnetwork_extract_tab( window.location.hash );
    vk_adnetwork_set_tab( active_tab );

    // set all tab urls
    vk_adnetwork_set_tab_hashes();

    // dynamically generate the sub-menu
    jQuery( '.vk_adnetwork-tab-sub-menu' ).each( function ( key, e ) {
        // abort if scrollIntoView is not supported; we can’t use anchors because they are used for tabs already
        if ( typeof e.scrollIntoView !== 'function' ) {
            return;
        }
        // get all h2 headlines
        vk_adnetwork_settings_parent_tab = jQuery( e ).parent( '.vk_adnetwork-tab' );
        var headlines              = vk_adnetwork_settings_parent_tab.find( 'h2' );
        // create list
        if ( headlines.length > 1 ) {
            vk_adnetwork_submenu_list = jQuery( '<ul>' );
            headlines.each( function ( key, h ) {
                // create anchor for this headline
                var headline_id = 'vk_adnetwork-tab-headline-' + vk_adnetwork_settings_parent_tab.attr( 'id' ) + key;
                jQuery( h ).attr( 'id', headline_id );
                // place the link in the top menu
                var text = text = h.textContent || h.innerText;
                jQuery( '<li><a onclick="document.getElementById(\'' + headline_id + '\').scrollIntoView()">' + text + '</a></li>' ).appendTo( vk_adnetwork_submenu_list );
            } );
            // place the menu
            vk_adnetwork_submenu_list.appendTo( e );
        }
    } );

    // OVERVIEW LIST (Ads, Groups, Placements)

    // toggle page filters, excluded from the Ads list since the search markup is not editable by us.
    $( 'body:not(.post-type-vk_adnetwork ) #vk_adnetwork-show-filters' ).on( 'click', function() {
        const disabled = $( this ).find( '.dashicons' ).hasClass( 'dashicons-arrow-up' );
        $( '.vk_adnetwork-toggle-with-filters-button' ).toggleClass( 'hidden', disabled );
        $( '#vk_adnetwork-show-filters .dashicons' ).toggleClass( 'dashicons-filter', disabled );
        $( '#vk_adnetwork-show-filters .dashicons' ).toggleClass( 'dashicons-arrow-up', ! disabled );
    } );

    // AD OVERVIEW LIST

    // show the bulk actions sticky, when some lines are selected
    // $( '.post-type-vk_adnetwork .check-column input[type="checkbox"]' ).on( 'change', function() {
    // 	$( '.post-type-vk_adnetwork .tablenav.bottom .bulkactions' ).toggleClass( 'fixed', 0 < $( '.post-type-vk_adnetwork .check-column input[type="checkbox"]:checked' ).length );
    // } );
    // show screen options when clicking on our custom link or the Close button
    $( '#vk_adnetwork-show-screen-options' ).on( 'click', function(){
        $( '#show-settings-link' ).trigger( 'click' );
    } );
    // Add a close button to the screen options
    $( '<button type="button" class="button vk_adnetwork-button-secondary">' + vk_adnetwork_txt.close + '</button>' )
        .appendTo( $( '.post-type-vk_adnetwork #adv-settings .submit' ) )
        .on( 'click', function() { $( '#show-settings-link' ).trigger( 'click' ); } );

    /**
     * PLACEMENTS
     */
    var set_touched_placement = function() {
        var tr = $( this ).closest( 'tr.vk-adnetwork-placement-row' )
        if ( tr ) {
            tr.data( 'touched', true )
        }
    }

    //  keep track of placements that were changed
    $( 'form#vk-adnetwork-placements-form input, #vk-adnetwork-placements-form select' ).on( 'change', set_touched_placement )
    $( 'form#vk-adnetwork-placements-form button' ).on( 'click', set_touched_placement )

    //  some special form elements overwrite the jquery listeners (or render them unusable in some strange way)
    //  to counter that and make it more robust in general, we now listen for mouseover events, that will
    //  only occur, when the settings of a placement are expanded (let's just assume this means editing)
    $( 'form#vk-adnetwork-placements-form .vk_adnetwork-modal' ).on( 'mouseover', set_touched_placement )

    // if the modal is canceled, remove the "touched" data again, since the user discarded any changes.
    $( document ).on( 'vk_adnetwork-modal-canceled', event => {
        const $placementRow = $( '#' + event.detail.modal_id ).parents( '.vk-adnetwork-placement-row' );
        if ( ! $placementRow.length ) {
            return;
        }
        $placementRow.data( 'touched', false );
    } );

    //  on submit remove placements that were untouched
    $( 'form#vk-adnetwork-placements-form' ).on( 'submit', function () {
        var grouprows = jQuery( 'form#vk-adnetwork-placements-form tr.vk-adnetwork-placement-row' )
        jQuery( 'form#vk-adnetwork-placements-form tr.vk-adnetwork-placement-row' ).each( function ( k, v ) {
            v = jQuery( v )
            if ( ! v.data( 'touched' ) ) {
                v.find( 'input, select' ).each( function ( k2, v2 ) {
                    v2 = jQuery( v2 )
                    v2.prop( 'disabled', true )
                } )
            }
        } )
    } )

    // show input field for custom xpath rule when "custom" option is selected for Content placement
    // iterate through all tag options of all placements
    $( '.vk_adnetwork-placements-content-tag' ).each( function(){
        vk_adnetwork_show_placement_content_xpath_field( this );
    })
    // update xpath field when tag option changes
    $( '.vk_adnetwork-placements-content-tag' ).on( 'change', function () {
        vk_adnetwork_show_placement_content_xpath_field( this );
    } )
    /**
     * show / hide input field for xpath rule
     *
     * @param tag field
     */
    function vk_adnetwork_show_placement_content_xpath_field( tag_field ){
        // get the value of the content tag option
        var tag = $( tag_field ).find( 'option:selected').val();
        // show or hide the next following custom xpath option
        if( 'custom' === tag ) {
            $( tag_field ).next( '.vk_adnetwork-placements-content-custom-xpath' ).show();
        } else {
            $( tag_field ).next( '.vk_adnetwork-placements-content-custom-xpath' ).hide();
        }
    }

    // show tooltips for group type or placement type in forms
    $( '.vk_adnetwork-form-type' ).vk_adnetwork_tooltip( {
        content: function () {
            return jQuery( this ).find( '.vk_adnetwork-form-description' ).html()
        },
        parent: ( $target ) => {
            const modal = $target.parents( '.vk_adnetwork-modal' );

            return modal.length ? '#'+modal[0].id : 'body';
        }
    } );

    /**
     * On the placements and ad edit page, check if the form values have changed on beforeunload.
     * On the settings page, additionally check for a tab change.
     */
    ( () => {
        let termination,
            form,
            submitted = false;

        if ( window.vk_adnetwork_txt.admin_page === 'vk_adnetwork' ) {
            // prevent errors on back/forward navigation
            form = document.getElementById( 'post' );
            if ( form !== null ) {
                termination = new VK_Adnetwork_Termination( form );
            }
        }

        if ( window.vk_adnetwork_txt.admin_page === 'vk-adnetwork_page_vk-adnetwork-settings' ) {
            form        = document.querySelector( '.vk_adnetwork-tab.active > form' );
            if ( form !== null ) {
                termination = new VK_Adnetwork_Termination( form );
            }
            [...document.getElementsByClassName( 'nav-tab' )].forEach( tab => {
                tab.addEventListener( 'click', event => {
                    if ( ! termination.terminationNotice() ) {
                        event.preventDefault();
                        return false;
                    }

                    vk_adnetwork_set_tab( vk_adnetwork_extract_tab( new URL( event.target.href ).hash ) );

                    form = document.querySelector( '.vk_adnetwork-tab.active > form' );
                    if ( form !== null ) {
                        termination = new VK_Adnetwork_Termination( form );
                        termination.collectValues();
                        // if the form is submitted, don't fire the beforeunload handler.
                        form.addEventListener( 'submit', () => {
                            submitted = true;
                        } );
                    }
                } );
            } );
        }

        if ( typeof termination !== 'undefined' ) {
            termination.collectValues();
            const beforeUnloadHandler = event => {
                if ( ! submitted && ! termination.terminationNotice() ) {
                    event.preventDefault();
                    event.returnValue = 'string';
                    return false;
                }
            };

            window.addEventListener( 'beforeunload', beforeUnloadHandler );

            // if the form is submitted, don't fire the beforeunload handler.
            form.addEventListener( 'submit', () => {
                submitted = true;
            } );
        }
    } )();

    window.formfield = ''

    // adblocker related code
    $( '#vk-adnetwork-use-adblocker' ).on( 'change', function () {
        vk_adnetwork_toggle_box( this, '#vk_adnetwork-adblocker-wrapper' )
    } )

    // processing of the rebuild asset form and the FTP/SSH credentials form
    var $vk_adnetwork_adblocker_wrapper        = $( '#vk_adnetwork-adblocker-wrapper' ),
        $vk_adnetwork_adblocker_rebuild_button = $( '#vk_adnetwork-adblocker-rebuild' );

    $vk_adnetwork_adblocker_rebuild_button.prop( 'disabled', false );

    $( document ).on( 'click', '#vk_adnetwork-adblocker-rebuild', function ( event ) {
        event.preventDefault();
        var $form = $( '#vk-adnetwork-rebuild-assets-form' );
        $form.prev( '.error' ).remove();
        $vk_adnetwork_adblocker_rebuild_button.prop( 'disabled', true ).after( '<span class="spinner vk_adnetwork-spinner"></span>' );

        var args = {
            data:           {
                action:                      'vk_adnetwork-adblock-rebuild-assets',
                nonce:                       vk_adnetwork_global.ajax_nonce
            },
            done:           function ( data ) {
                $vk_adnetwork_adblocker_wrapper.html( data );
                $vk_adnetwork_adblocker_rebuild_button = $( '#vk_adnetwork-adblocker-rebuild' )
            },
            fail:           function ( jqXHR, textStatus, errorThrown ) {
                $form.before( '<div class="error"><p>' + textStatus + ': ' + errorThrown + '</p></div>' );
                $vk_adnetwork_adblocker_rebuild_button.prop( 'disabled', false ).next( '.vk_adnetwork-spinner' ).remove();
            },
            on_modal_close: function () {
                $vk_adnetwork_adblocker_rebuild_button.prop( 'disabled', false ).next( '.vk_adnetwork-spinner' ).remove();
            }
        };

        if ( $( '[name="vk_adnetwork_ab_assign_new_folder"]' ).is( ':checked' ) ) {
            args.data.vk_adnetwork_ab_assign_new_folder = true;
        }

        vk_adnetwork_admin.filesystem.ajax( args );
    } );

    // process "reserve this space" checkbox
    $( '#vk-adnetwork-ad-parameters' ).on( 'change', '#vk-adnetwork-ad-parameters-size input[type=number]', function () {
        // Check if width and/or height is set.
        if ( $( '#vk-adnetwork-ad-parameters-size input[type=number]' ).filter( function () {
            return parseInt( this.value, 10 ) > 0
        } ).length >= 1 ) {
            $( '#vk_adnetwork-wrapper-add-sizes' ).prop( 'disabled', false )
        } else {
            $( '#vk_adnetwork-wrapper-add-sizes' ).prop( 'disabled', true ).prop( 'checked', false )
        }
    } )
    // process "reserve this space" checkbox - ad type changed
    $( '#vk-adnetwork-ad-parameters' ).on( 'paramloaded', function () {
        $( '#vk-adnetwork-ad-parameters-size input[type=number]:first' ).trigger( 'change' );
    } );
    // process "reserve this space" checkbox - on load
    $( '#vk-adnetwork-ad-parameters-size input[type=number]:first' ).trigger( 'change' );

    // move meta box markup to hndle headline
    $( '.vk_adnetwork-hndlelinks' ).each( function () {
        $( this ).appendTo( $( this ).parents('.postbox').find( 'h2.hndle' ) )
        $( this ).removeClass( 'hidden' )
    } );
    // open tutorial link when clicked on it
    $( '.vk_adnetwork-video-link' ).on( 'click', function (event) {
        event.preventDefault()
        var video_container = $( event.target ).parents( '.postbox' ).find( '.vk_adnetwork-video-link-container' )
        video_container.html( video_container.data( 'videolink' ) )
    } );
    // open inline tutorial link when clicked on it
    $( '.vk_adnetwork-video-link-inline' ).on( 'click', function ( el ) {
        el.preventDefault()
        var video_container = $( this ).parents( 'div' ).siblings( '.vk_adnetwork-video-link-container' )
        video_container.html( video_container.data( 'videolink' ) )
    } );
    // switch import type
    jQuery( '.vk_adnetwork_import_type' ).on( 'change', function () {
        if ( this.value === 'xml_content' ) {
            jQuery( '#vk_adnetwork_xml_file' ).hide()
            jQuery( '#vk_adnetwork_xml_content' ).show()
        } else {
            jQuery( '#vk_adnetwork_xml_file' ).show()
            jQuery( '#vk_adnetwork_xml_content' ).hide()
        }
    } );

    vk_adnetwork_ads_txt_find_issues()

} )

function modal_submit_form( event, ID, modalID, validation = '' ) {
    if ( validation !== '' && ! window[validation]( modalID ) ) {
        event.preventDefault();
        return;
    }
    document.getElementById( ID ).submit();
}

/**
 * Store the action hash in settings form action
 * thanks for Yoast SEO for this idea
 */
function vk_adnetwork_set_tab_hashes() {
    // iterate through forms
    jQuery( '#vk_adnetwork-tabs' ).find( 'a' ).each( function () {
        var id        = jQuery( this ).attr( 'id' ).replace( '-tab', '' );
        var optiontab = jQuery( '#' + id );

        var form = optiontab.children( '.vk_adnetwork-settings-tab-main-form' )
        if ( form.length ) {
            var currentUrl = form.attr( 'action' ).split( '#' )[ 0 ]
            form.attr( 'action', currentUrl + jQuery( this ).attr( 'href' ) )
        }
    } )
}

/**
 * Scroll to position in backend minus admin bar height
 *
 * @param selector jQuery selector
 */
function vk_adnetwork_scroll_to_element ( selector ) {
    var height_of_admin_bar = jQuery( '#wpadminbar' ).height()
    jQuery( 'html, body' ).animate( {
        scrollTop: jQuery( selector ).offset().top - height_of_admin_bar
    }, 1000 )
}

/**
 * toggle content elements (hide/show)
 *
 * @param selector jquery selector
 */
function vk_adnetwork_toggle ( selector ) {
    jQuery( selector ).slideToggle()
}

/**
 * toggle content elements with a checkbox (hide/show)
 *
 * @param selector jquery selector
 */
function vk_adnetwork_toggle_box ( e, selector ) {
    if ( jQuery( e ).is( ':checked' ) ) {
        jQuery( selector ).slideDown()
    } else {
        jQuery( selector ).slideUp()
    }
}

/**
 * disable content of one box when selecting another
 *  only grey/disable it, don’t hide it
 *
 * @param selector jquery selector
 */
function vk_adnetwork_toggle_box_enable ( e, selector ) {
    if ( jQuery( e ).is( ':checked' ) ) {
        jQuery( selector ).find( 'input' ).removeAttr( 'disabled', '' )
    } else {
        jQuery( selector ).find( 'input' ).attr( 'disabled', 'disabled' )
    }
}

/**
 * Validate the form that creates a new group or placement.
 */
function vk_adnetwork_validate_new_form (modalID) {
    // Check if type was selected
    if ( ! jQuery( '.vk_adnetwork-form-type input:checked' ).length ) {
        jQuery( '.vk_adnetwork-form-type-error' ).show()
        return false
    } else {
        jQuery( '.vk_adnetwork-form-type-error' ).hide()
    }
    // Check if name was entered
    if ( jQuery( '.vk_adnetwork-form-name' ).val() == '' ) {
        jQuery( '.vk_adnetwork-form-name-error' ).show()
        return false
    } else {
        jQuery( '.vk_adnetwork-form-name-error' ).hide()
    }
    return true
}

/**
 * Submit only the current group. Submitting the form with all groups could otherwise cause a server timeout or PHP limit error.
 *
 * @param {string} modalID
 * @return {boolean}
 */
function vk_adnetwork_group_edit_submit( modalID ) {
    jQuery( '.vk_adnetwork-ad-group-form' )
        .filter( ( i, element ) => ! jQuery( element ).parents( modalID ).length )
        .remove();

    return true;
}

/**
 * replace textarea with TinyMCE editor for Rich Content ad type
 */
function vk_adnetwork_maybe_textarea_to_tinymce ( ad_type ) {
    var textarea            = jQuery( '#vk_adnetwork-ad-content-plain' ),
        textarea_html       = textarea.val(),
        tinymce_id          = 'vk-adnetwork-tinymce',
        tinymce_id_ws       = jQuery( '#' + tinymce_id )

    if ( ad_type !== 'content' ) {
        tinymce_id_ws.prop( 'name', tinymce_id )
        return false
    }

    if ( typeof tinyMCE === 'object' && tinyMCE.get( tinymce_id ) !== null ) {
        // visual mode
        if ( textarea_html ) {
            // see BeforeSetContent in the wp-includes\js\tinymce\plugins\wordpress\plugin.js
            var wp         = window.wp,
                hasWpautop = (wp && wp.editor && wp.editor.autop && tinyMCE.get( tinymce_id ).getParam( 'wpautop', true ))
            if ( hasWpautop ) {
                textarea_html = wp.editor.autop( textarea_html )
            }
            tinyMCE.get( tinymce_id ).setContent( textarea_html )
        }
        textarea.remove()
        tinymce_id_ws.prop( 'name', textarea.prop( 'name' ) )
    } else if ( tinymce_id_ws.length ) {
        // text mode
        tinymce_id_ws.val( textarea_html )
        textarea.remove()
        tinymce_id_ws.prop( 'name', textarea.prop( 'name' ) )
    }
}

/**
 * Check if a third-party ads.txt file exists.
 */
function vk_adnetwork_ads_txt_find_issues () {
    var $wrapper = jQuery( '#vk_adnetwork-ads-txt-notice-wrapper' )
    var $refresh = jQuery( '#vk_adnetwork-ads-txt-notice-refresh' )
    var $actions = jQuery( '.vk_adnetwork-ads-txt-action' )

    /**
     * Toggle the visibility of the spinner.
     *
     * @param {Bool} state True to show, False to hide.
     */
    function set_loading ( state ) {
        $actions.toggle( ! state )
        if ( state ) {
            $wrapper.html( '<span class="spinner vk_adnetwork-spinner"></span>' )
        }
    }

    if ( ! $wrapper.length ) {
        return
    }

    if ( ! $wrapper.find( 'ul' ).length ) {
        // There are no notices. Fetch them using ajax.
        load( 'get_notices' )
    }

    $refresh.on('click', function () {
        load( 'get_notices' )
    } )

    function done ( response ) {
        $wrapper.html( response.notices )
        set_loading( false )
    }

    function fail ( jqXHR ) {
        $wrapper.html(
            '<p class="vk_adnetwork-notice-inline vk_adnetwork-error">'
            + jQuery( '#vk_adnetwork-ads-txt-notice-error' ).text().replace( '%s', parseInt( jqXHR.status, 10 ) ),
            +'</p>'
        )
        set_loading( false )
    }

    function load ( type ) {
        set_loading( true )

        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'vk_adnetwork-ads-txt',
                nonce: vk_adnetwork_global.ajax_nonce,
                type,
            },
        } ).done( done ).fail( fail )
    }

    jQuery( document ).on( 'click', '#vk_adnetwork-ads-txt-remove-real', function ( event ) {
        event.preventDefault()

        var args = {
            data: {
                action: 'vk_adnetwork-ads-txt',
                nonce: vk_adnetwork_global.ajax_nonce,
                type: 'remove_real_file',
            },
            done: function ( response ) {
                if ( response.additional_content ) {
                    jQuery( '#vk_adnetwork-ads-txt-additional-content' ).val( response.additional_content )
                }
                done( response )
            },
            fail: fail,
            before_send: function () {
                set_loading( true )
            },
        }

        vk_adnetwork_admin.filesystem.ajax( args )
    } )

    jQuery( document ).on( 'click', '#vk_adnetwork-ads-txt-create-real', function ( event ) {
        event.preventDefault()

        var args = {
            data: {
                action: 'vk_adnetwork-ads-txt',
                nonce: vk_adnetwork_global.ajax_nonce,
                type: 'create_real_file',
            },
            done: done,
            fail: fail,
            before_send: function () {
                set_loading( true )
            },
        }

        vk_adnetwork_admin.filesystem.ajax( args )
    } )

}

window.vk_adnetwork_admin     = window.vk_adnetwork_admin || {}
vk_adnetwork_admin.filesystem = {
    /**
     * Holds the current job while the user writes data in the 'Connection Information' modal.
     *
     * @type {obj}
     */
    _locked_job: false,

    /**
     * Toggle the 'Connection Information' modal.
     */
    _requestForCredentialsModalToggle: function () {
        this.$filesystemModal.toggle()
        jQuery( 'body' ).toggleClass( 'modal-open' )
    },

    _init: function () {
        this._init = function () {}
        var self   = this

        self.$filesystemModal = jQuery( '#vk-adnetwork-rfc-dialog' )
        /**
         * Sends saved job.
         */
        self.$filesystemModal.on( 'submit', 'form', function ( event ) {
            event.preventDefault()

            self.ajax( self._locked_job, true )
            self._requestForCredentialsModalToggle()
        } )

        /**
         * Closes the request credentials modal when clicking the 'Cancel' button.
         */
        self.$filesystemModal.on( 'click', '[data-js-action="close"]', function () {
            if ( jQuery.isPlainObject( self._locked_job ) && self._locked_job.on_modal_close ) {
                self._locked_job.on_modal_close()
            }

            self._locked_job = false
            self._requestForCredentialsModalToggle()
        } )
    },

    /**
     * Sends AJAX request. Shows 'Connection Information' modal if needed.
     *
     * @param {object} args
     * @param {bool} skip_modal
     */
    ajax: function ( args, skip_modal ) {
        this._init()

        if ( ! skip_modal && this.$filesystemModal.length > 0 ) {
            this._requestForCredentialsModalToggle()
            this.$filesystemModal.find( 'input:enabled:first' ).focus()

            // Do not send request.
            this._locked_job = args
            return
        }

        var options = {
            method: 'POST',
            url: window.ajaxurl,
            data: {
                username: jQuery( '#username' ).val(),
                password: jQuery( '#password' ).val(),
                hostname: jQuery( '#hostname' ).val(),
                connection_type: jQuery( 'input[name="connection_type"]:checked' ).val(),
                public_key: jQuery( '#public_key' ).val(),
                private_key: jQuery( '#private_key' ).val(),
                _fs_nonce: jQuery( '#_fs_nonce' ).val()

            }
        }

        if ( args.before_send ) {
            args.before_send()
        }

        options.data = jQuery.extend( options.data, args.data )
        var request  = jQuery.ajax( options )

        if ( args.done ) {
            request.done( args.done )
        }

        if ( args.fail ) {
            request.fail( args.fail )
        }
    }
}

window.VK_Adnetwork_Admin = window.VK_Adnetwork_Admin || {
    init_ad_source_editor: function () {

    },
    get_ad_source_editor_text: function () {
        let text = '';
        if ( VK_Adnetwork_Admin.editor && VK_Adnetwork_Admin.editor.codemirror ) {
            text = VK_Adnetwork_Admin.editor.codemirror.getValue();
        } else {
            const ta = jQuery( '#vk_adnetwork-ad-content-plain' );
            if ( ta ) {
                text = ta.val();
            }
        }

        return text;
    },
    set_ad_source_editor_text: function ( text ) {
        if ( VK_Adnetwork_Admin.editor && VK_Adnetwork_Admin.editor.codemirror ) {
            VK_Adnetwork_Admin.editor.codemirror.setValue( text )
        } else {
            jQuery( '#vk_adnetwork-ad-content-plain' ).val( text )
        }
    },
    check_ad_source: function () {
        const text            = VK_Adnetwork_Admin.get_ad_source_editor_text();
        const phpWarning      = jQuery( '#vk_adnetwork-parameters-php-warning' );
        const allowPhpWarning = jQuery( '#vk_adnetwork-allow-php-warning' );

        phpWarning.hide();
        allowPhpWarning.hide();

        const plainTextarea = document.getElementById( 'vk_adnetwork-ad-content-plain' );
        plainTextarea.value = text;
        plainTextarea.dispatchEvent( new Event( 'input' ) );

        if ( jQuery( '#vk_adnetwork-parameters-php' ).prop( 'checked' ) ) {
            // ad content has opening php tag.
            if ( /<\?(?:php|=)/.test( text ) ) {
                allowPhpWarning.show();
            } else {
                phpWarning.show();
            }
        }
        // execute shortcodes is enabled.
        if ( jQuery( '#vk_adnetwork-parameters-shortcodes' ).prop( 'checked' ) && ! /\[[^\]]+\]/.test( text ) ) {
            jQuery( '#vk_adnetwork-parameters-shortcodes-warning' ).show();
        } else {
            jQuery( '#vk_adnetwork-parameters-shortcodes-warning' ).hide();
        }
    },

    /**
     * Change the user id to the current user and save the post.
     *
     * @param {int} user_id
     */
    reassign_ad: function ( user_id ) {
        let $authorBox = jQuery( '#post_author_override' );
        if ( ! $authorBox.length ) {
            $authorBox = jQuery( '#post_author' );
        }

        $authorBox.val( user_id ).queue( () => {
            jQuery( '#post' ).submit();
        } );
    },

    /**
     * Toggle placement advanced options.
     *
     * @deprecated. Used only by add-ons when the base plugin version < 1.19.
     */
    toggle_placements_visibility: function ( elm, state ) {
        var vk_adnetwork_placementformrow = jQuery( elm ).next( '.vk_adnetwork-placements-advanced-options' )

        var hide = ( typeof state !== 'undefined' ) ? ! state : vk_adnetwork_placementformrow.is( ':visible' );;
        if ( hide ) {
            vk_adnetwork_placementformrow.hide()
            // clear last edited id
            jQuery( '#vk_adnetwork-last-edited-placement' ).val( '' )
        } else {
            var placement_id = jQuery( elm ).parents( '.vk_adnetwork-placements-table-options' ).find( '.vk_adnetwork-placement-slug' ).val()
            vk_adnetwork_placementformrow.show()
            jQuery( '#vk_adnetwork-last-edited-placement' ).val( placement_id )
            // Some special elements (color picker) may not be detected with jquery.
            var tr = jQuery( elm ).closest( 'tr.vk-adnetwork-placement-row' )
            if ( tr ) {
                tr.data( 'touched', true )
            }
        }
    },

    /**
     * get a cookie value
     *
     * @param {str} name of the cookie
     */
    get_cookie: function (name) {
        var i, x, y, ADVcookies = document.cookie.split( ";" );
        for (i = 0; i < ADVcookies.length; i++)
        {
            x = ADVcookies[i].substr( 0, ADVcookies[i].indexOf( "=" ) );
            y = ADVcookies[i].substr( ADVcookies[i].indexOf( "=" ) + 1 );
            x = x.replace( /^\s+|\s+$/g, "" );
            if (x === name)
            {
                return unescape( y );
            }
        }
    },

    /**
     * set a cookie value
     *
     * @param {str} name of the cookie
     * @param {str} value of the cookie
     * @param {int} exdays days until cookie expires
     *  set 0 to expire cookie immidiatelly
     *  set null to expire cookie in the current session
     */
    set_cookie: function (name, value, exdays, path, domain, secure) {
        // days in seconds
        var expiry = ( exdays == null ) ? null : exdays * 24 * 60 * 60;
        this.set_cookie_sec( name, value, expiry, path, domain, secure );
    },
    /**
     * set a cookie with expiry given in seconds
     *
     * @param {str} name of the cookie
     * @param {str} value of the cookie
     * @param {int} expiry seconds until cookie expires
     *  set 0 to expire cookie immidiatelly
     *  set null to expire cookie in the current session
     */
    set_cookie_sec: function (name, value, expiry, path, domain, secure) {
        var exdate = new Date();
        exdate.setSeconds( exdate.getSeconds() + parseInt( expiry ) );
        document.cookie = name + "=" + escape( value ) +
            ((expiry == null) ? "" : "; expires=" + exdate.toUTCString()) +
            ((path == null) ? "; path=/" : "; path=" + path) +
            ((domain == null) ? "" : "; domain=" + domain) +
            ((secure == null) ? "" : "; secure");
    }
}

if ( ! window.VK_AdnetworkAdmin ) window.VK_AdnetworkAdmin = {}

/**
 * The "abstract" base class for handling external ad units
 * Every ad unit will provide you with a set of methods to control the GUI and trigger requests to the server
 * while editing an ad that is backed by this network. The main logic takes place in admin/assets/admin.js,
 * and the methods in this class are the ones that needed abstraction, depending on the ad network. When you
 * need new network-dependant features in the frontend, this is the place to add new methods.
 *
 * An VK_AdnetworkAdnetwork uses these fields:
 * id string The identifier, that is used for this network. Must match with the one used in the PHP code of VK AdNetwork
 * units array Holds the ad units of this network.
 * vars map These are the variables that were transmitted from the underlying PHP class (method: append_javascript_data)
 * hideIdle Remembers, wheter idle ads should be displayed in the list;
 * fetchedExternalAds Remembers if the external ads list has already been loaded to prevent unneccesary requests
 */
class VK_AdnetworkAdnetwork {
    /**
     * @param id string representing the id of this network. has to match the identifier of the PHP class
     */
    constructor ( id ) {
        this.id                 = id
        this.units              = []
        this.vars               = window[ id + 'VK_AdnetworkJS' ]
        this.hideIdle           = true
        this.fetchedExternalAds = false
    }

    /**
     * will be called when an ad network is selected (ad type in edit ad)
     */
    onSelected () {
        console.error( 'Please override onSelected.' )
    }

    /**
     * will be called when an ad network deselected (ad type in edit ad)
     */
    onBlur () {
        console.error( 'Please override onBlur.' )
    }

    /**
     * opens the selector list containing the external ad units
     */
    openSelector () {
        console.error( 'Please override openSelector.' )
    }

    /**
     * returns the network specific id of the currently selected ad unit
     */
    getSelectedId () {
        console.error( 'Please override getSelectedId.' )
    }

    /**
     * will be called when an external ad unit has been selected from the selector list
     * @param slotId string the external ad unit id
     */
    selectAdFromList ( slotId ) {
        console.error( 'Please override selectAdFromList.' )
    }

    /**
     * will be called when an the update button of an external ad unit has been clicked
     * TODO: decide wheter to remove this method. not required anymore - the button was removed.
     * @param slotId string the external ad unit id
     */
    updateAdFromList ( slotId ) {
        console.error( 'Please override updateAdFromList.' )
    }

    /**
     * return the POST params that you want to send to the server when requesting a refresh of the external ad units
     * (like nonce and action and everything else that is required)
     */
    getRefreshAdsParameters () {
        console.error( 'Please override getRefreshAdsParameters.' )
    }

    /**
     * return the jquery objects for all the custom html elements of this ad type
     */
    getCustomInputs () {
        console.error( 'Please override getCustomInputs.' )
    }

    /**
     * what to do when the DOM is ready
     */
    onDomReady () {
        console.error( 'Please override onDomReady.' )
    }

    /**
     * when you need custom behaviour for ad networks that support manual setup of ad units, override this method
     */
    onManualSetup () {
        //no console logging. this is optional
    }
}

class VK_AdnetworkExternalAdUnit {

}

/**
 * todo: this looks like something we could use in general, but where to put it?
 */
jQuery( document ).ready( function () {
    // delete an existing row by removing the parent tr tag
    // todo: this could be moved to a general file
    jQuery( document ).on( 'click', '.vk_adnetwork-tr-remove', function(){
        jQuery( this ).closest( 'tr' ).remove();
    });
});

/**
 * If the jQuery function `escapeSelector` does not exist (add in jQuery 3.0) then add it.
 */
if ( typeof jQuery.escapeSelector === 'undefined' ) {
    jQuery.escapeSelector = function ( selector ) {
        return selector.replace(
            // phpcs:ignore WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore,WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter,WordPress.WhiteSpace.OperatorSpacing.NoSpaceBeforeAmp,WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfterAmp -- PHPCS incorrectly reports whitespace errors for this regex
            /([$%&()*+,./:;<=>?@\[\\\]^{|}~])/g,
            '\\$1'
        );
    };
}

////

// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact -- PHPCS can't handle es5 short functions
function VK_Adnetwork_Termination( element ) {
    /**
     * Function to reset the changed nodes to default values.
     *
     * @constructor
     */
    function FormValues() {
        this.addedNodes   = [];
        this.removedNodes = [];
    }

    let initialFormValues = new FormValues(),
        changedFormValues = new FormValues();

    const blocklist = [
        'active_post_lock'
    ];

    this.observers = {
        list: [],

        push: item => {
            this.observers.list.push( item );
        },

        disconnect: () => {
            this.observers.list.forEach( observer => {
                observer.disconnect();
            } );
            this.observers.list = [];
        }
    };

    /**
     * Reset the form values to their empty default.
     */
    const reset = () => {
        initialFormValues = new FormValues();
        changedFormValues = new FormValues();
    };

    /**
     * Collect input values.
     * Checkboxes are true/false, unless they are part of a group.
     * Radio buttons have a boolean value on the saved value, only the checked one will be collected.
     *
     * @param {FormValues} object
     * @param {Node} input
     * @return {FormValues}
     */
    const collectInputValue = function ( object, input ) {
        /**
         * Collect checkbox group values.
         * If there are multiple checkboxes with the same `nome` attribute, collect all values for this group.
         *
         * @param {NodeList} group Iterable of inputs with the same `name` attribute.
         * @return {FormValues}
         */
        const collectCheckboxGroup = ( group ) => {

            object[group[0].name] = [];
            group.forEach( input => {
                if ( input.checked ) {
                    object[input.name].push( input.value );
                }
            } );

            return object;
        };

        if ( input.type === 'checkbox' ) {
            const checkboxGroup = element.querySelectorAll( '[name="' + input.name + '"]' );
            if ( checkboxGroup.length > 1 ) {
                return collectCheckboxGroup( checkboxGroup, input );
            }

            object[input.name] = input.checked;

            return object;
        }

        // if a radio button is not checked, don't collect it.
        if ( input.type === 'radio' && ! input.checked ) {
            return object;
        }

        object[input.name] = input.value;

        return object;
    };

    /**
     * Setup a mutationobserver to check for added and removed form fields.
     * This especially applies to conditions.
     *
     * @type {MutationObserver}
     */
    const addedRemovedObserver = new MutationObserver( mutations => {
        for ( const mutation of mutations ) {
            for ( const removedNode of mutation.removedNodes ) {
                const nodes = document.createTreeWalker( removedNode, NodeFilter.SHOW_ELEMENT );
                while ( nodes.nextNode() ) {
                    if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
                        const index = changedFormValues.addedNodes.indexOf( nodes.currentNode.name );
                        if ( index > - 1 ) {
                            changedFormValues.addedNodes.splice( index, 1 );
                        } else {
                            changedFormValues.removedNodes.push( nodes.currentNode.name );
                        }
                    }
                }
            }
            for ( const addedNode of mutation.addedNodes ) {
                if ( addedNode.nodeType === Node.TEXT_NODE ) {
                    continue;
                }

                const nodes = document.createTreeWalker( addedNode, NodeFilter.SHOW_ELEMENT );
                while ( nodes.nextNode() ) {
                    if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
                        changedFormValues.addedNodes.push( nodes.currentNode.name );
                    }
                }
            }
        }
    } );

    // attach the mutation observer to the passed element.
    addedRemovedObserver.observe( element, {childList: true, subtree: true} );
    this.observers.push( addedRemovedObserver );

    /**
     * Check if there are inputs that have been changed and if their value is different.
     *
     * @param {Object} reference The initial values when the modal loaded, indexed by name attribute.
     * @param {Object} changed The input values that were changed, indexed by name.
     *
     * @return {boolean}
     */
    this.hasChanged = ( reference, changed ) => {
        for ( const name in changed ) {
            if ( ! reference.hasOwnProperty( name ) || reference[name].toString() !== changed[name].toString() ) {
                return true;
            }
        }

        return false;
    };

    /**
     * If the modal is associated with a form and any values have changed, ask for confirmation to navigate away.
     * Returns true if the user agrees with termination, false otherwise.
     *
     * @param {boolean} reload Whether to reload the page on added and removed nodes (needed for the modal). Default false.
     *
     * @return {boolean}
     */
    this.terminationNotice = ( reload = false ) => {
        if ( ! this.hasChanged( initialFormValues, changedFormValues ) ) {
            return true;
        }

        // ask user for confirmation.
        if ( window.confirm( window.vk_adnetwork_txt.confirmation ) ) {
            // if we have added or removed nodes, we might need to reload the page.
            if ( changedFormValues.addedNodes.length || changedFormValues.removedNodes.length ) {
                if ( reload ) {
                    window.location.reload();
                }
                return true;
            }

            // otherwise, we'll replace the values with the previous values.
            for ( const name in changedFormValues ) {
                const input = element.querySelector( '[name="' + name + '"]' );
                if ( input === null ) {
                    continue;
                }

                if ( input.type === 'checkbox' ) {
                    input.checked = initialFormValues[name];
                } else if ( input.type === 'radio' ) {
                    element.querySelector( '[name="' + name + '"][value="' + initialFormValues[name] + '"]' ).checked = true;
                } else {
                    input.value = initialFormValues[name];
                }
            }

            return true;
        }

        return false;
    };

    /**
     * Collect inputs in this modal and save their initial and changed values (if any).
     */
    this.collectValues = () => {
        const isDialog = element.tagName === 'DIALOG';

        element.querySelectorAll( 'input, select, textarea' ).forEach( input => {
            if ( ! input.name.length || blocklist.includes( input.id ) || blocklist.includes( input.name ) ) {
                return;
            }

            // if the element itself is not a dialog but the input is within a dialog, ignore it. This accounts for split forms, e.g. the placements page where some inputs are hidden in a modal dialog.
            if ( ! isDialog && input.closest( 'dialog' ) ) {
                return;
            }

            initialFormValues = collectInputValue( initialFormValues, input );

            // if the input is `hidden` no change event gets triggered. Use MutationObservers to check for changes in the value attribute.
            if ( input.type === 'hidden' ) {
                const hiddenObserver = new MutationObserver( function ( mutations, observer ) {
                    mutations.forEach( mutation => {
                        if ( mutation.attributeName === 'value' ) {
                            mutation.target.dispatchEvent( new Event( 'input' ) );
                        }
                    } );
                } );
                hiddenObserver.observe( element, {
                    attributes: true,
                    subtree:    true
                } );
                this.observers.push( hiddenObserver );
            }

            input.addEventListener( 'input', event => {
                changedFormValues = collectInputValue( changedFormValues, input );
            } );
        } );
    };
}
