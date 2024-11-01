/**
 * Wait for the page to be ready before firing JS.
 * Дождитесь, пока страница будет готова, прежде чем запускать JS.
 *
 * @param {function} callback - A callable function to be executed.
 * @param {string} [requestedState=complete] - document.readyState to wait for. Defaults to 'complete', can be 'interactive'.
 */
window.vk_adnetwork_ready = function ( callback, requestedState ) {
    requestedState = requestedState || 'complete';
    var checkState = function ( state ) {
        return requestedState === 'interactive' ? state !== 'loading' : state === 'complete';
    };

    // If we have reached the correct state, fire the callback.
    if ( checkState( document.readyState ) ) {
        callback();
        return;
    }
    // We are not yet in the correct state, attach an event handler, only fire once if the requested state is 'interactive'.
    document.addEventListener( 'readystatechange', function ( event ) {
        if ( checkState( event.target.readyState ) ) {
            callback();
        }
    }, {once: requestedState === 'interactive'} );
};

window.vk_adnetwork_ready_queue = window.vk_adnetwork_ready_queue || [];
