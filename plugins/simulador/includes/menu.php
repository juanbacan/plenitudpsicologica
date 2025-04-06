<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Simulador',
        'Simulador',
        'manage_options',
        'simulador',
        '__return_null', // No muestra página principal, solo submenús
        'dashicons-welcome-learn-more',
        5
    );

    add_submenu_page(
        'simulador',
        'Categorías',
        'Categorías',
        'manage_options',
        'edit-tags.php?taxonomy=categoria&post_type=pregunta'
    );

    add_submenu_page(
        'simulador',
        'Preguntas',
        'Preguntas',
        'manage_options',
        'edit.php?post_type=pregunta'
    );

    add_submenu_page(
        'simulador',
        'Simuladores',
        'Simuladores',
        'manage_options',
        'edit.php?post_type=simulador'
    );
});
