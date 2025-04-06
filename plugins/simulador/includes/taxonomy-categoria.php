<?php

// Registrar la taxonomía personalizada "categoria"
add_action('init', function () {
    register_taxonomy('categoria', 'pregunta', [
        'labels' => [
            'name' => 'Categorías',
            'singular_name' => 'Categoría',
        ],
        'hierarchical' => true,
        'show_in_rest' => true,
        'show_ui' => true,
        'show_admin_column' => true,
    ]);
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
