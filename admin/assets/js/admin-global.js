/*
 * global js functions for VK AdNetwork
 */
jQuery( document ).ready(function () {

    /**
     * ADMIN NOTICES
     */
    // close button
    // .vk_adnetwork-notice-dismiss class can be used to add a custom close button (e.g., link)
    jQuery(document).on('click', '.vk_adnetwork-admin-notice .notice-dismiss, .vk_adnetwork-notice-dismiss', function(event){
        event.preventDefault();
        var messagebox = jQuery(this).parents('.vk_adnetwork-admin-notice');
        if( messagebox.attr('data-notice') === undefined) return;

        var query = {
                action: 'vk_adnetwork-close-notice',
                notice: messagebox.attr('data-notice'),
                nonce: vk_adnetwork_global.ajax_nonce
        };
        // send query
        jQuery.post(ajaxurl, query, function (r) {
                messagebox.fadeOut();
        });
    });
    // hide notice for 7 days
    jQuery(document).on('click', '.vk_adnetwork-admin-notice .vk_adnetwork-notice-hide', function(){
        var messagebox = jQuery(this).parents('.vk_adnetwork-admin-notice');
        if( messagebox.attr('data-notice') === undefined) return;

        var query = {
                action: 'vk_adnetwork-hide-notice',
                notice: messagebox.attr('data-notice'),
                nonce: vk_adnetwork_global.ajax_nonce
        };
        // send query
        jQuery.post(ajaxurl, query, function (r) {
                messagebox.fadeOut();
        });
    });

    /**
     * Functions for Ad Health Notifications in the backend
     */
    // hide button (adds item to "ignore" list)
    jQuery(document).on('click', '.vk_adnetwork-ad-health-notice-hide', function(){
        var notice = jQuery(this).parents('li');
        if( notice.attr('data-notice') === undefined) return;
        // var list = notice.parent( 'ul' );
        var remove = jQuery( this ).hasClass( 'remove' );

        // fix height to prevent the box from going smaller first, then show the "show" link and grow again
        var notice_box = jQuery( '#vk_adnetwork_overview_notices' );
        notice_box.css( 'height', notice_box.height() + 'px' );

        var query = {
        action: 'vk_adnetwork-ad-health-notice-hide',
        notice: notice.attr('data-notice'),
        nonce: vk_adnetwork_global.ajax_nonce
        };
        // fade out first or remove, so users can’t click twice
        if( remove ){
        notice.remove();
        } else {
        notice.hide();
        }
        // show loader
        notice_box.find('.vk_adnetwork-loader' ).show();
        vk_adnetwork_ad_health_maybe_remove_list();
        // send query
        jQuery.post(ajaxurl, query, function (r) {
            // update number in menu
            vk_adnetwork_ad_health_reload_number_in_menu();
            // update show button
            vk_adnetwork_ad_health_reload_show_link();
            // remove the fixed height
            jQuery( '#vk_adnetwork_overview_notices' ).css( 'height', '' );
            // remove loader
            notice_box.find('.vk_adnetwork-loader' ).hide();
        });
    });
    // show all hidden notices
    jQuery(document).on('click', '.vk_adnetwork-ad-health-notices-show-hidden', function(){
        vk_adnetwork_ad_health_show_hidden();
    });

    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    vk_adnetwork_deactivate_link = jQuery('.wp-admin.plugins-php tr[data-slug="vk-adnetwork"] .row-actions .deactivate a');
    vk_adnetwork_deactivate_link_url = vk_adnetwork_deactivate_link.attr( 'href' );
    vk_adnetwork_deactivate_link.on( 'click', function ( e ) {
        e.preventDefault();
        // only show feedback form once per 30 days
        var c_value = vk_adnetwork_admin_get_cookie( "vk_adnetwork_hide_deactivate_feedback" );
        if (c_value === undefined){
            jQuery( '#vk-adnetwork-feedback-overlay' ).show();
        } else {
            // click on the link
            window.location.href = vk_adnetwork_deactivate_link_url;
        }
    });
    // show text fields
    jQuery('#vk-adnetwork-feedback-content input[type="radio"]').on('click',function () {
        // show text field if there is one
        jQuery(this).parents('li').next('li').children('input[type="text"], textarea').show();
    });
    // handle technical issue feedback in particular
    jQuery('#vk-adnetwork-feedback-content .vk_adnetwork_disable_help_text').on('focus',function () {
        // show text field if there is one
        jQuery(this).parents('li').siblings('.vk_adnetwork_disable_reply').show();
    });
    // send form or close it
    jQuery('#vk-adnetwork-feedback-content .button').on('click', function ( e ) {
        e.preventDefault();
        var self = jQuery( this );
        // set cookie for 30 days
        vk_adnetwork_store_feedback_cookie();
        // save if plugin should be disabled
        var disable_plugin = !self.hasClass('vk-adnetwork-feedback-not-deactivate');

        // hide the content of the feedback form
        jQuery( '#vk-adnetwork-feedback-content form' ).hide();
        if ( self.hasClass('vk-adnetwork-feedback-submit') ) {
            // show feedback message
            jQuery( '#vk-adnetwork-feedback-after-submit-waiting' ).show();
            if( disable_plugin ){
                jQuery( '#vk-adnetwork-feedback-after-submit-disabling-plugin' ).show();
            }
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'vk_adnetwork_send_feedback',
                    feedback: !!self.hasClass('vk-adnetwork-feedback-not-deactivate'),
                    // formdata: jQuery( '#vk-adnetwork-feedback-content form' ).serialize()
                    vk_adnetwork_disable_form_nonce: jQuery('#vk_adnetwork_disable_form_nonce').val(),
                    vk_adnetwork_disable_from: jQuery('#vk_adnetwork_disable_from').val(),                              // <<hidden>>
                    // <<radio>>  = get help , ads not showing up , temporary , missing feature , stopped showing ads , other plugin
                    vk_adnetwork_disable_reason: jQuery('input[name="vk_adnetwork_disable_reason"]:checked').val(),
                    vk_adnetwork_disable_reply_email: jQuery('#vk_adnetwork_disable_reply_email').val(),                // <<email>>
                    // <<textarea>> + <<text>>
                    vk_adnetwork_disable_text: [jQuery('textarea#vk_adnetwork_disable_textarea').val(), jQuery('#vk_adnetwork_disable_text').val()]
                },
                complete: function (MLHttpRequest, textStatus, errorThrown) {
                    // deactivate the plugin and close the popup with a timeout
                    setTimeout( function(){
                        jQuery( '#vk-adnetwork-feedback-overlay' ).remove();
                    }, 2000 )
                    if( disable_plugin ){
                        window.location.href = vk_adnetwork_deactivate_link_url;
                    }

                }
            });
        } else { // currently not reachable
            jQuery( '#vk-adnetwork-feedback-overlay' ).remove();
            window.location.href = vk_adnetwork_deactivate_link_url;
        }
    });
    // close form and disable the plugin without doing anything
    jQuery('.vk-adnetwork-feedback-only-deactivate').on('click', function () {
        // hide the content of the feedback form
        jQuery( '#vk-adnetwork-feedback-content form' ).hide();
        // show feedback message
        jQuery( '#vk-adnetwork-feedback-after-submit-goodbye' ).show();
        jQuery( '#vk-adnetwork-feedback-after-submit-disabling-plugin' ).show();
        // set cookie for 30 days
        vk_adnetwork_store_feedback_cookie();
        // wait one second
        setTimeout(function(){
            jQuery( '#vk-adnetwork-feedback-overlay' ).hide();
            window.location.href = vk_adnetwork_deactivate_link_url;
        }, 1000 );
    });
    // close button for feedback form
    jQuery('#vk-adnetwork-feedback-overlay-close-button').on('click', function () {
        jQuery( '#vk-adnetwork-feedback-overlay' ).hide();
    });

    jQuery( '.vk_adnetwork-help' ).on( 'mouseenter', function ( event ) {
        const tooltip = jQuery( event.target ).children( '.vk_adnetwork-tooltip' )[0];
        if ( typeof tooltip === 'undefined' ) {
            return;
        }

        // reset inline styles before getting bounding client rect.
        tooltip.style.position = '';
        tooltip.style.left     = '';
        tooltip.style.top      = '';

        const topParentRect = document.getElementById( 'wpbody' ).getBoundingClientRect(),
              helpRect      = event.target.getBoundingClientRect(),
              offsets       = {
                  left: Math.ceil( helpRect.left ) + 20,
                  top:  Math.ceil( helpRect.top ) + 20
              };
        let tooltipRect     = tooltip.getBoundingClientRect();

        tooltip.style.position = 'fixed';
        tooltip.style.left     = offsets.left + 'px';
        tooltip.style.top      = offsets.top + 'px';

        // check element is not overflowing to the right.
        while ( tooltipRect.right > ( topParentRect.right - 20 ) ) {
            offsets.left -= 10;
            tooltip.style.left = offsets.left + 'px';
            tooltipRect = tooltip.getBoundingClientRect();
        }

        // check element is not overflowing bottom of parent and is within viewport.
        while ( tooltipRect.bottom > ( Math.min( topParentRect.bottom, jQuery( window ).height() ) - 20 ) ) {
            offsets.top -= 10;
            tooltip.style.top = offsets.top + 'px';
            tooltipRect = tooltip.getBoundingClientRect();
        }
    } );
});

// remove duplicate close buttons
jQuery(window).on('load', function () {
    jQuery('a.notice-dismiss').next('button.notice-dismiss').remove();
});

function vk_adnetwork_admin_get_cookie (name) {
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
}

/**
 * Store a cookie for 30 days
 * The cookie prevents the feedback form from showing multiple times
 */
function vk_adnetwork_store_feedback_cookie() {
    var exdate = new Date();
    exdate.setSeconds( exdate.getSeconds() + 2592000 );
    document.cookie = "vk_adnetwork_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";
}

/**
 * Ad Health Notices in backend
 */
// display notices list (deprecated because we load it without AJAX now)
function vk_adnetwork_display_ad_health_notices(){

    var query = {
        action: 'vk_adnetwork-ad-health-notice-display',
        nonce: vk_adnetwork_global.ajax_nonce
    };

    var widget = jQuery( '#vk_adnetwork_overview_notices .main' );

    // add loader icon to the widget
    widget.html( '<span class="vk_adnetwork-loader"></span>' );
    // send query
    jQuery.post(ajaxurl, query, function (r) {
        widget.html( r );

        // update number in menu
        vk_adnetwork_ad_health_reload_number_in_menu();
        // update list headlines
        vk_adnetwork_ad_health_maybe_remove_list();

        // remove widget, if return is empty
        if( r === '' ){
            jQuery( '#vk_adnetwork_overview_notices' ).remove();
        }
    });
}
// push a notice to the queue
function vk_adnetwork_push_notice( key, attr = '' ){

    var query = {
        action: 'vk_adnetwork-ad-health-notice-push-adminui',
        key: key,
        attr: attr,
        nonce: vk_adnetwork_global.ajax_nonce
    };
    // send query
    jQuery.post(ajaxurl, query, function (r) {});
}
// show notices of a given type again
function vk_adnetwork_ad_health_show_hidden(){
    var notice_box = jQuery( '#vk_adnetwork__overview_notices');
    var query = {
        action: 'vk_adnetwork-ad-health-notice-unignore',
        nonce: vk_adnetwork_global.ajax_nonce
    };
    // show all hidden
    jQuery( document ).find( '#vk_adnetwork_overview_notices .vk_adnetwork-ad-health-notices > li:hidden' ).show();
    // show loader
    notice_box.find('.vk_adnetwork-loader' ).show();
    // update the button
    vk_adnetwork_ad_health_reload_show_link();
    vk_adnetwork_ad_health_maybe_remove_list();
    // send query
    jQuery.post(ajaxurl, query, function (r) {
        // update issue count
        vk_adnetwork_ad_health_reload_number_in_menu();
        // hide loader
        notice_box.find('.vk_adnetwork-loader' ).hide();
    });
}
// hide list fragments if last item was hidden/removed
function vk_adnetwork_ad_health_maybe_remove_list(){
    // get all lists
        var lists = jQuery( document ).find( '#vk_adnetwork_overview_notices .vk_adnetwork-ad-health-notices' );

    // check each list separately
    lists.each( function( index ) {
        var list = jQuery( this );
        // check if there are visible items in the list
        if( list.find( 'li:visible' ).length ){
            // show parent headline
            list.prev( 'h3' ).show();
        } else {
            // hide parent headline
            list.prev( 'h3' ).hide();

        }
    });

}
// reload number of notices shown in the sidebar based on element in the problems list
function vk_adnetwork_ad_health_reload_number_in_menu(){
    // get number of notices
    var number = jQuery( document ).find( '#vk_adnetwork_overview_notices .vk_adnetwork-ad-health-notices > li:visible' ).length;
    jQuery( '#toplevel_page_vk-adnetwork .update-count').html( number );
}
// update show X issues link – number and visibility
function vk_adnetwork_ad_health_reload_show_link(){
    // get number of invisible elements
    var number = jQuery( document ).find( '#vk_adnetwork_overview_notices .vk_adnetwork-ad-health-notices > li:hidden' ).length;
    var show_link = jQuery( '.vk_adnetwork-ad-health-notices-show-hidden' );
    // update number in the link
    jQuery( '.vk_adnetwork-ad-health-notices-show-hidden span.count' ).html( number );
    // hide of show, depending on number
    if( 0 === number ){
        show_link.hide();
    } else {
        show_link.show();
    }
}
