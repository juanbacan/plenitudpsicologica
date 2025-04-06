<?php


add_action('init', function () {
    register_post_type('simulador', [
        'labels' => [
            'name' => 'Simuladores',
            'singular_name' => 'Simulador',
            'add_new' => 'Añadir simulador',
            'add_new_item' => 'Nuevo simulador',
            'edit_item' => 'Editar simulador',
            'new_item' => 'Nuevo simulador',
            'view_item' => 'Ver simulador',
            'all_items' => 'Todos los simuladores',
            'search_items' => 'Buscar simuladores',
            'menu_name' => 'Simuladores',
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false,
        'supports' => ['title', 'editor', 'slug'],
        'show_in_rest' => false
    ]);
});


add_action('add_meta_boxes', function () {
    add_meta_box('simulador_campos', 'Configuración del simulador', 'mostrar_campos_simulador', 'simulador', 'advanced', 'low');
});

function mostrar_campos_simulador($post)
{
    $campos = get_post_meta($post->ID, '_simulador', true) ?: [];

    $categoria = $campos['categoria'] ?? '';
    $tiempo = $campos['tiempo'] ?? 0;
    $cronometro = $campos['cronometro'] ?? '0';
    $numero_preguntas = $campos['numero_preguntas'] ?? 10;
    $descripcion = $campos['descripcion'] ?? '';
    $premium = $campos['premium'] ?? '0';

    // Obtener categorías existentes
    $categorias = get_terms(['taxonomy' => 'categoria', 'hide_empty' => false]);
    ?>

    <table class="form-table">
        <tr>
            <th><label for="categoria">Categoría</label></th>
            <td>
                <select name="simulador[categoria]" id="categoria">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= esc_attr($cat->term_id) ?>" <?= selected($cat->term_id, $categoria) ?>>
                            <?= esc_html($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="tiempo">Tiempo (minutos)</label></th>
            <td><input type="number" name="simulador[tiempo]" id="tiempo" value="<?= esc_attr($tiempo) ?>" min="1"></td>
        </tr>
        <tr>
            <th><label><input type="checkbox" name="simulador[cronometro]" value="1" <?= checked($cronometro, '1', false) ?>> Usar cronómetro</label></th>
        </tr>
        <tr>
            <th><label for="numero_preguntas">Número de preguntas</label></th>
            <td><input type="number" name="simulador[numero_preguntas]" value="<?= esc_attr($numero_preguntas) ?>" min="1"></td>
        </tr>
        <tr>
            <th><label for="descripcion">Descripción</label></th>
            <td><textarea name="simulador[descripcion]" id="descripcion" rows="4" style="width: 100%;"><?= esc_textarea($descripcion) ?></textarea></td>
        </tr>
        <tr>
            <th><label><input type="checkbox" name="simulador[premium]" value="1" <?= checked($premium, '1', false) ?>> ¿Es premium?</label></th>
        </tr>
    </table>
    <?php
}


add_action('save_post_simulador', function ($post_id) {
    if (isset($_POST['simulador'])) {
        $data = $_POST['simulador'];
        $data['cronometro'] = isset($data['cronometro']) ? '1' : '0';
        $data['premium'] = isset($data['premium']) ? '1' : '0';
        update_post_meta($post_id, '_simulador', $data);
    }
});
