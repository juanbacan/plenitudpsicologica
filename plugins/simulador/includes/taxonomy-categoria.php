<?php

// Registrar la taxonomía personalizada "categoria"
add_action('init', function () {
    register_taxonomy('categoria', 'pregunta', [
        'labels' => [
            'name' => 'Categorías Preguntas',
            'singular_name' => 'Categoría Preguntas',
        ],
        'public' => true,
        'hierarchical' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'categoria'],
    ]);
});


add_filter('parent_file', function ($parent_file) {
    global $current_screen;

    if ($current_screen->taxonomy === 'categoria' && $current_screen->post_type === 'pregunta') {
        $parent_file = 'simulador'; // marca "Simulador" como menú padre activo
    }

    return $parent_file;
});

add_filter('submenu_file', function ($submenu_file) {
    global $current_screen;

    if ($current_screen->taxonomy === 'categoria' && $current_screen->post_type === 'pregunta') {
        $submenu_file = 'edit-tags.php?taxonomy=categoria&post_type=pregunta'; // marca el submenú como activo
    }

    return $submenu_file;
});


// Mostrar campo "Descripción corta" al AÑADIR una nueva categoría
add_action('categoria_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="descripcion_corta">Descripción corta</label>
        <input type="text" name="descripcion_corta" id="descripcion_corta" value="" />
        <p class="description">Una breve descripción para mostrar en listados o filtros.</p>
    </div>
    <?php
});

// Mostrar campo "Descripción corta" al EDITAR una categoría existente
add_action('categoria_edit_form_fields', function ($term) {
    $valor = get_term_meta($term->term_id, 'descripcion_corta', true);
    ?>
    <tr class="form-field">
        <th><label for="descripcion_corta">Descripción corta</label></th>
        <td>
            <input type="text" name="descripcion_corta" id="descripcion_corta" value="<?= esc_attr($valor) ?>" />
            <p class="description">Una breve descripción para mostrar en listados o filtros.</p>
        </td>
    </tr>
    <?php
});

// Guardar el campo al crear o editar categoría
add_action('created_categoria', 'guardar_descripcion_corta');
add_action('edited_categoria', 'guardar_descripcion_corta');

function guardar_descripcion_corta($term_id) {
    if (isset($_POST['descripcion_corta'])) {
        update_term_meta($term_id, 'descripcion_corta', sanitize_text_field($_POST['descripcion_corta']));
    }
}
