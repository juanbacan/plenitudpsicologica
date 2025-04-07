<?php
if (!defined('ABSPATH')) exit;

// Registrar un like
function registrar_like_a_solucion($post_id, $sol_id, $user_id) {
    global $wpdb;

    # Imprimir en consola que esamos en la función
    error_log("LIKE | Entrando a la función registrar_like_a_solucion: post_id=$post_id, sol_id=$sol_id, user_id=$user_id");

    $tabla = $wpdb->prefix . 'simulador_solucion_likes';

    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $tabla WHERE post_id = %d AND sol_id = %s AND user_id = %d",
        $post_id, $sol_id , $user_id
    ));

    if (!$existe) {
        $insertado = $wpdb->insert($tabla, [
            'post_id' => $post_id,
            'sol_id' => $sol_id,
            'user_id' => $user_id,
        ]);
    
        if ($insertado === false) {
            error_log("LIKE ERROR | Insert fallido: " . $wpdb->last_error);
        }
    }
    error_log("LIKE | Like registrado: post_id=$post_id, sol_id =$sol_id , user_id=$user_id");
}


// Contar likes
function obtener_likes_solucion($post_id, $sol_id ) {
    global $wpdb;
    $tabla = $wpdb->prefix . 'simulador_solucion_likes';

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $tabla WHERE post_id = %d AND sol_id  = %d",
        $post_id, $sol_id 
    ));
}


add_action('wp_ajax_dar_like_solucion', 'ajax_dar_like_solucion');

function ajax_dar_like_solucion() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['mensaje' => 'Debes iniciar sesión para dar like.']);
    }

    $post_id = absint($_POST['post_id']);
    $sol_id = sanitize_text_field($_POST['sol_id']);
    $user_id = get_current_user_id();

    registrar_like_a_solucion($post_id, $sol_id, $user_id);
    $total = obtener_likes_solucion($post_id, $sol_id);

    wp_send_json_success(['likes' => $total]);
}

function usuario_ya_dio_like($post_id, $sol_id, $user_id) {
    global $wpdb;
    $tabla = $wpdb->prefix . 'simulador_solucion_likes';

    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $tabla WHERE post_id = %d AND sol_id = %d AND user_id = %d",
        $post_id, $sol_id, $user_id
    )) > 0;
}