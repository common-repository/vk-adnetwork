(function ( $ ) {
    /**
     * Button.
     */
    $.fn.vk_adnetwork_button = function() {
        var $buttonset = jQuery( this );
        var $ancestor = $buttonset.parent();

        $buttonset.each( function() {
            $this = jQuery( this );
            if ( $this.data( 'vk_adnetwork_button' ) ) {
                return true;
            }
            $this.data( 'vk_adnetwork_button', true );

            var $button = jQuery( this );
            var $label = jQuery( 'label[for="' + $button.attr( 'id' ) + '"]', $ancestor );
            var type = $button.attr( 'type' );

            $button.addClass( 'vk_adnetwork-accessible' );
            $label.addClass( 'vk_adnetwork-button' );
            $label.wrapInner( '<span class="vk_adnetwork-button-text"></span>' );

            if ( $button.is( ':checked' ) ) {
                $label.addClass( 'vk_adnetwork-ui-state-active' );
            }

            $button.on('change', function() {
                var $changed = jQuery( this );
                var $label = jQuery( 'label[for="' + $changed.attr( 'id' ) + '"]', $ancestor );

                if ( type === 'radio' ) {
                    $ancestor.find( 'label' ).removeClass( 'vk_adnetwork-ui-state-active' );
                }

                if ( $changed.is( ':checked' ) ) {
                    $label.addClass( 'vk_adnetwork-ui-state-active' );
                } else {
                    $label.removeClass( 'vk_adnetwork-ui-state-active' );
                }
            } );

        } );
    };
    /**
     * Buttonset.
     */
    $.fn.vk_adnetwork_buttonset = function() {
        var $that = jQuery( this );

        $that.each( function() {
            $buttonset = jQuery( this );

            if ( $buttonset.data( 'vk_adnetwork_buttonset' ) ) {
                return true;
            }
            $buttonset.data( 'vk_adnetwork_buttonset', true );

            var items = 'input[type=checkbox], input[type=radio]';
            var $all_buttons = $buttonset.find( items );

            if ( ! $all_buttons.length ) {
                return;
            }

            // Show selected checkboxes first.
            if ( jQuery.escapeSelector ) {
                $items = jQuery();
                $all_buttons.filter( ':checked' ).each( function() {
                    $items = $items.add( $buttonset.find( 'label[for="' + jQuery.escapeSelector( this.id ) + '"]' ) );
                    $items = $items.add( this );
                } );
                $items.prependTo( $buttonset );
            }

            $buttonset.addClass( 'vk_adnetwork-buttonset' );

            $all_buttons.vk_adnetwork_button();
        } );
    };

    /**
     * Tooltip.
     *
     * @param {Function} options.content A function that returns the content.
     */
    $.fn.vk_adnetwork_tooltip = function( options ) {
        var tooltip;
        var tooltip_target;

        if ( ! options.content ) {
            return this;
        }

        function open( event ) {
            var $target = jQuery( event.currentTarget );

            // check if the correct tooltip is already open.
            if ( tooltip && $target.is( tooltip_target ) ) {
                return;
            }
            if ( tooltip ) {
                tooltip.remove();
                tooltip = null;
                tooltip_target = null;
            }

            if ( event.type === 'mouseover' ) {
                jQuery( $target ).on( 'mouseleave', close );
            }
            if ( event.type === 'focusin' ) {
                jQuery( $target ).on( 'focusout', close );
            }

            var content = options.content.call( $target )
            if ( content ) {
                tooltip = $( '<div>' ).addClass( 'vk_adnetwork-tooltip' );
                const parent = typeof options.parent === 'function' ? options.parent.call( this, $target ) : 'body';
                $( '<div>' ).addClass( 'vk_adnetwork-tooltip-content' ).appendTo( tooltip );
                tooltip.appendTo( parent );
                tooltip.find( '.vk_adnetwork-tooltip-content' ).html( content );

                position = $target.offset();
                position.top += $target.outerHeight() + 15;
                tooltip.offset( position );
                tooltip_target = $target;

                tooltip.show();
            }

        }
        function close( event ) {
            if ( tooltip ) {
                tooltip.remove();
                tooltip = null;
                tooltip_target = null;
            }
        }

        this.each( function() {
            $this = jQuery( this );
            if ( $this.data( 'vk_adnetwork_tooltip' ) ) {
                return true;
            }
            $this.data( 'vk_adnetwork_tooltip', true );

            $this.on( 'mouseover focusin', open );
        } );
    };
} )( jQuery );
