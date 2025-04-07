<?php

add_action('wp_ajax_enviar_comentario_solucion', 'ajax_enviar_comentario_solucion');

function ajax_enviar_comentario_solucion() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['mensaje' => 'Debes iniciar sesión.']);
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'simulador_solucion_comentarios';

    $post_id = absint($_POST['post_id']);
    $sol_id = sanitize_text_field($_POST['sol_id']);
    $comentario = sanitize_text_field($_POST['texto']);
    $user_id = get_current_user_id();

    $wpdb->insert($tabla, [
        'post_id' => $post_id,
        'sol_id' => $sol_id,
        'user_id' => $user_id,
        'comentario' => $comentario,
        'fecha' => current_time('mysql')
    ]);

    $usuario = wp_get_current_user();
    wp_send_json_success([
        'usuario' => $usuario->display_name,
        'texto' => $comentario,
        'fecha' => current_time('mysql')
    ]);
}

function obtener_comentarios_de_solucion($post_id, $sol_id) {
    global $wpdb;
    $tabla = $wpdb->prefix . 'simulador_solucion_comentarios';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $tabla WHERE post_id = %d AND sol_id = %s ORDER BY fecha ASC",
        $post_id, $sol_id
    ));
}
