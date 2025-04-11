<?php
function fg_instalar_tablas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tabla_forms = $wpdb->prefix . 'formulario_forms';
    $tabla_campos = $wpdb->prefix . 'formulario_campos';
    $tabla_envios = $wpdb->prefix . 'formulario_envios';

    $sql = "
    CREATE TABLE $tabla_forms (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;

    CREATE TABLE $tabla_campos (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        formulario_id BIGINT(20) UNSIGNED NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        etiqueta VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        requerido BOOLEAN DEFAULT FALSE,
        orden INT DEFAULT 0,
        PRIMARY KEY (id),
        KEY formulario_id (formulario_id)
    ) $charset_collate;

    CREATE TABLE $tabla_envios (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        formulario_id BIGINT(20) UNSIGNED NOT NULL,
        datos LONGTEXT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY formulario_id (formulario_id)
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
