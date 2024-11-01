// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact -- PHPCS can't handle es5 short functions
const modal = element => {
    let termination;

    let hasForm = false;

    /**
     * Remove the pound sign from the location hash.
     *
     * @return {string}
     */
    const getId = () => window.location.hash.replace( '#', '' );

    const showModal = () => {
        element.showModal();

        termination = new VK_Adnetwork_Termination( element );

        if ( hasForm ) {
            termination.collectValues();
        }
    };

    /**
     * If the current hash matches the modal id attribute, open it.
     */
    const showIfHashMatches = () => {
        if ( getId() === element.id ) {
            showModal();
        }
    };

    // Check whether to open modal on page load.
    showIfHashMatches();

    /**
     * Listen to the hashchange event, to check if the current modal needs to be opened.
     */
    window.addEventListener( 'hashchange', () => {
        showIfHashMatches();

        if ( getId() !== 'close' ) {
            return;
        }

        if ( ! hasForm || termination.terminationNotice( true ) ) {
            element.close();
        }
    } );

    /**
     * Attach a click listener to all links referencing this modal and prevent their default action.
     * By changing the hash on every click, we also create a history entry.
     */
    document.querySelectorAll( 'a[href$="#' + element.id + '"]' ).forEach( link => {
        link.addEventListener( 'click', e => {
            e.preventDefault();
            showModal();
        } );
    } );

    /**
     * On the cancel event, check for termination notice and fire a custom event.
     */
    element.addEventListener( 'cancel', event => {
        event.preventDefault();
        if ( ! hasForm ) {
            element.close();
            return;
        }

        if ( termination.terminationNotice( true ) ) {
            element.close();

            termination.observers.disconnect();

            document.dispatchEvent( new CustomEvent( 'vk_adnetwork-modal-canceled', {
                detail: {
                    modal_id: element.id
                }
            } ) );
        }
    } );

    /**
     * On the close event, i.e., a form got submit, empty the hash to prevent form from reopening.
     */
    element.addEventListener( 'close', event => {
        if ( getId() === element.id ) {
            window.location.hash = '';
        }
    } );

    try {
        // try if there is a form inside the modal, otherwise continue in catch.
        element.querySelector( 'form' ).addEventListener( 'submit', () => {
            window.location.hash = '';
        } );
        hasForm = true;
    } catch ( e ) {
        let targetForm;
        try {
            targetForm = element.querySelector( 'button.vk_adnetwork-modal-close-action' ).form;
            hasForm    = true;
        } catch ( e ) {
        }
        try {
            /**
             * Listen for the keydown event in all inputs.
             * If the enter key is pressed and the modal has a form, submit it, else do nothing.
             */
            element.querySelectorAll( 'input' ).forEach( input => {
                input.addEventListener( 'keydown', e => {
                    if ( e.key !== 'Enter' ) {
                        return;
                    }

                    if ( typeof targetForm !== 'undefined' && targetForm.reportValidity() ) {
                        targetForm.submit();
                        return;
                    }

                    // if there are inputs, but there is no form associated with them, do nothing.
                    e.preventDefault();
                } );
            } );
        } catch ( e ) {

        }
    }

    /**
     * On the cancel buttons, check termination notice and close the modal.
     */
    element.querySelectorAll( '.vk_adnetwork-modal-close, .vk_adnetwork-modal-close-background' ).forEach( button => {
        button.addEventListener( 'click', e => {
            e.preventDefault();
            element.dispatchEvent( new Event( 'cancel' ) );
        } );
    } );

    try {
        /**
         * If the save button is not a `<button>` element. Close the form without changing the hash.
         */
        element.querySelector( 'a.vk_adnetwork-modal-close-action' ).addEventListener( 'click', e => {
            e.preventDefault();
            element.close();
        } );
    } catch ( e ) {
    }
};

window.addEventListener( 'DOMContentLoaded', () => {
    try {
        if ( typeof document.querySelector( '.vk_adnetwork-modal[id^=modal-]' ).showModal !== 'function' ) {
            return;
        }
    } catch ( e ) {
        return;
    }
    [...document.getElementsByClassName( 'vk_adnetwork-modal' )].forEach( modal );
} );
