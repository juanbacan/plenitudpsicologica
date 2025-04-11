<?php
add_shortcode('formulario_generator', function ($atts) {
    global $wpdb;
    $atts = shortcode_atts(['id' => 0], $atts);
    $campos_json = get_post_meta($atts['id'], '_fg_campos', true);
    if (!$campos_json) return '<p>No se han configurado campos.</p>';

    $campos = json_decode($campos_json, true);
    if (!is_array($campos)) return '<p>Error en la configuraci\u00f3n del formulario.</p>';

    ob_start();
    ?>
    <form method="post">
        <input type="hidden" name="fg_formulario_id" value="<?php echo esc_attr($atts['id']); ?>">
        <?php foreach ($campos as $campo): ?>
            <label><?php echo esc_html($campo['label']); ?></label>
            <?php if ($campo['type'] === 'textarea'): ?>
                <textarea name="<?php echo esc_attr($campo['name']); ?>" <?php if ($campo['required']) echo 'required'; ?>></textarea>
            <?php else: ?>
                <input type="<?php echo esc_attr($campo['type']); ?>" name="<?php echo esc_attr($campo['name']); ?>" <?php if ($campo['required']) echo 'required'; ?>>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" name="fg_enviar">Enviar</button>
    </form>
    <?php
    if (isset($_POST['fg_enviar']) && $_POST['fg_formulario_id'] == $atts['id']) {
        $datos = [];
        foreach ($campos as $campo) {
            $key = $campo['name'];
            $datos[$key] = sanitize_text_field($_POST[$key] ?? '');
        }
        $wpdb->insert($wpdb->prefix . 'formulario_envios', [
            'formulario_id' => $atts['id'],
            'datos' => maybe_serialize($datos)
        ]);
        echo '<p><strong>Formulario enviado con éxito.</strong></p>';
    }
    return ob_get_clean();
});
