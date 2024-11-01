( function () {
    window.vk_adnetwork_ready_queue = window.vk_adnetwork_ready_queue || [];

    // replace native push method with our vk_adnetwork_ready function; do this early to prevent race condition between pushing and the loop.
    vk_adnetwork_ready_queue.push = window.vk_adnetwork_ready;

    // handle all callbacks that have been added to the queue previously.
    for ( var i = 0, length = vk_adnetwork_ready_queue.length; i < length; i ++ ) {
        vk_adnetwork_ready( vk_adnetwork_ready_queue[i] );
    }
} )();
