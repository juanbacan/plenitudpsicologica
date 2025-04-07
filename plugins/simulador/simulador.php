<?php
/**
 * Plugin Name: Simulador Precavidos
 * Description: Un plugin que permite generar un simulador y banco de preguntas para un curso
 * Version: 1.0
 * Author: Juan Inga
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/cpt-pregunta.php';
require_once plugin_dir_path(__FILE__) . 'includes/cpt-simulador.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomy-categoria.php';
require_once plugin_dir_path(__FILE__) . 'includes/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/likes.php';
require_once plugin_dir_path(__FILE__) . 'includes/comentarios.php'; // <- este debe estar ANTES de frontend-shortcodes


register_activation_hook(__FILE__, 'crear_tablas');

register_activation_hook(__FILE__, 'crear_tablas');

function crear_tablas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $tabla_likes = $wpdb->prefix . 'simulador_solucion_likes';
    dbDelta("CREATE TABLE IF NOT EXISTS $tabla_likes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        sol_index INT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (post_id, sol_index, user_id)
    ) $charset_collate;");

    $tabla_comentarios = $wpdb->prefix . 'simulador_solucion_comentarios';
    dbDelta("CREATE TABLE IF NOT EXISTS $tabla_comentarios (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        sol_index INT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        comentario TEXT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;");
}
