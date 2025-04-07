<?php

add_action('init', function () {
    register_post_type('pregunta', [
        'labels' => [
            'name' => 'Preguntas',
            'singular_name' => 'Pregunta',
            'add_new' => 'Añadir nueva',
            'add_new_item' => 'Añadir nueva pregunta',
            'edit_item' => 'Editar pregunta',
            'new_item' => 'Nueva pregunta',
            'view_item' => 'Ver pregunta',
            'view_items' => 'Ver preguntas',
            'search_items' => 'Buscar preguntas',
            'not_found' => 'No se encontraron preguntas',
            'not_found_in_trash' => 'No hay preguntas en la papelera',
            'all_items' => 'Todas las preguntas',
            'archives' => 'Archivo de preguntas',
            'attributes' => 'Atributos de pregunta',
            'insert_into_item' => 'Insertar en pregunta',
            'uploaded_to_this_item' => 'Subido a esta pregunta',
            'filter_items_list' => 'Filtrar lista de preguntas',
            'items_list_navigation' => 'Navegación de preguntas',
            'items_list' => 'Lista de preguntas',
            'menu_name' => 'Preguntas',
            'name_admin_bar' => 'Pregunta',
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_menu' => false, // No mostrar en el menú lateral directamente
        'supports' => ['editor', 'slug'], // <-- AQUI está la solución
        'taxonomies' => ['categoria'],
        'show_in_rest' => false, // Para soporte con Gutenberg
    ]);
});

require_once plugin_dir_path(__FILE__) . 'respuestas.php';
require_once plugin_dir_path(__FILE__) . 'soluciones.php';

function generar_slug_unico_para_pregunta() {
    do {
        $slug = strtolower(bin2hex(random_bytes(4))); // genera 8 caracteres hex (similar a BSON corto)
        $existe = get_page_by_path($slug, OBJECT, 'pregunta');
    } while ($existe);

    return $slug;
}

add_filter('wp_insert_post_data', function ($data, $postarr) {
    if ($data['post_type'] === 'pregunta') {
        // Generar título desde contenido
        $content = strip_tags($data['post_content']);
        $content = trim(preg_replace('/\s+/', ' ', $content));
        $title = mb_substr($content, 0, 60);
        $data['post_title'] = $title ?: 'Pregunta sin título';

        // Si no hay slug definido aún, generar uno tipo BSON
        if (empty($postarr['post_name'])) {
            $data['post_name'] = generar_slug_unico_para_pregunta();
        }
    }
    return $data;
}, 10, 2);


add_action('save_post_pregunta', function ($post_id) {
    // Guardar las respuestas (esto ya lo tienes)
    if (isset($_POST['respuestas'])) {
        $respuestas_sanitizadas = [];
        foreach ($_POST['respuestas'] as $respuesta) {
            $respuestas_sanitizadas[] = [
                'enunciado' => wp_kses_post($respuesta['enunciado']),
                'correcta' => isset($respuesta['correcta']) ? '1' : '0',
            ];
        }
        update_post_meta($post_id, '_respuestas', $respuestas_sanitizadas);
    } else {
        delete_post_meta($post_id, '_respuestas');
    }

    // Guardar soluciones sin sobreescribir el user_id
    if (isset($_POST['soluciones'])) {
        $soluciones_actuales = get_post_meta($post_id, '_soluciones', true) ?: [];
        $soluciones_sanitizadas = [];

        foreach ($_POST['soluciones'] as $index => $solucion) {
            $contenido = wp_kses_post($solucion['contenido']);
            $aprobada = isset($solucion['aprobada']) ? '1' : '0';
        
            // Mantener el user_id si ya existía
            $user_id = isset($soluciones_actuales[$index]['user_id']) 
                ? $soluciones_actuales[$index]['user_id'] 
                : get_current_user_id();
        
            // Mantener o generar sol_id único
            $sol_id = isset($soluciones_actuales[$index]['sol_id']) 
                ? $soluciones_actuales[$index]['sol_id'] 
                : substr(bin2hex(random_bytes(5)), 0, 10);

            // Guardar la fecha si ya existe, o crear una nueva
            $fecha = $soluciones_actuales[$index]['fecha'] ?? current_time('mysql');
        
            $soluciones_sanitizadas[] = [
                'contenido' => $contenido,
                'aprobada' => $aprobada,
                'user_id' => $user_id,
                'sol_id' => $sol_id,
                'fecha' => $fecha,
            ];
        }

        update_post_meta($post_id, '_soluciones', $soluciones_sanitizadas);
    } else {
        delete_post_meta($post_id, '_soluciones');
    }
});
