<?php
add_action('init', function () {
    register_post_type('formulario', [
        'labels' => [
            'name' => 'Formularios',
            'singular_name' => 'Formulario',
            'add_new' => 'Agregar nuevo',
            'add_new_item' => 'Agregar nuevo formulario',
            'edit_item' => 'Editar formulario',
            'new_item' => 'Nuevo formulario',
            'view_item' => 'Ver formulario',
            'search_items' => 'Buscar formularios',
            'not_found' => 'No se encontraron formularios',
            'not_found_in_trash' => 'No se encontraron formularios en la papelera',
            'all_items' => 'Todos los formularios',
            'menu_name' => 'Formularios',
        ],
        'public' => false,
        'show_ui' => true,              // ✅ importante: mostrar en el admin
        'show_in_menu' => true,         // ✅ importante: mostrar en el menú
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-feedback',
        'supports' => ['title'],        // no uses 'editor' si lo vas a reemplazar
    ]);
});
