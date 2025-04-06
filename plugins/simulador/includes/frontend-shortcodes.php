<?php

add_shortcode('ver_pregunta', function ($atts) {
    global $post;

    if ($post->post_type !== 'pregunta') {
        return 'Este shortcode solo funciona en preguntas.';
    }

    $respuestas = get_post_meta($post->ID, '_respuestas', true) ?: [];
    $soluciones = get_post_meta($post->ID, '_soluciones', true) ?: [];

    ob_start();
    ?>

    <div class="pregunta-box">
        <h2><?= esc_html(get_the_title()) ?></h2>
        <div class="pregunta-enunciado">
            <?= apply_filters('the_content', $post->post_content) ?>
        </div>

        <?php if ($respuestas): ?>
            <div class="pregunta-respuestas">
                <h3>Opciones:</h3>
                <ul>
                    <?php foreach ($respuestas as $respuesta): ?>
                        <li>
                            <?= wpautop($respuesta['enunciado']) ?>
                            <?php if ($respuesta['correcta'] === '1'): ?>
                                <span style="color: green;">✔ Correcta</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($soluciones): ?>
            <div class="pregunta-soluciones">
                <h3>Soluciones:</h3>
                <?php foreach ($soluciones as $solucion): ?>
                    <div class="solucion">
                        <?= wpautop($solucion['contenido']) ?>
                        <?php if ($solucion['aprobada'] === '1'): ?>
                            <p><strong style="color: green;">✔ Aprobada</strong></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .pregunta-respuestas ul {
            list-style: disc;
            padding-left: 20px;
        }
    </style>

    <?php
    return ob_get_clean();
});
