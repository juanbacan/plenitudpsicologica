<?php
/*
Plugin Name: Generador de Formularios Personalizados
Description: Plugin para crear formularios configurables desde el administrador de WordPress.
Version: 1.0
Author: Tu Nombre
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/register-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/create-builder-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/install-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/render-shortcode.php';


// Crear tablas al activar plugin
register_activation_hook(__FILE__, 'fg_instalar_tablas');
