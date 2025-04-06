<?php
/**
 * Plugin Name: Simulador Precavidos
 * Description: Un plugin que permite generar un simulador y banco de preguntas para un curso
 * Version: 1.0
 * Author: Juan Inga
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/cpt-pregunta.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomy-categoria.php';
require_once plugin_dir_path(__FILE__) . 'includes/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend-shortcodes.php';
