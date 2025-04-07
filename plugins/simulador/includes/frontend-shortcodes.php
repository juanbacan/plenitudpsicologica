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

    <div class="pregunta-wrapper">
        <div class="pregunta-enunciado">
            <?php
                $terminos = get_the_terms($post->ID, 'categoria');
                if ($terminos && !is_wp_error($terminos)) {
                    $categoria = esc_html($terminos[0]->name);
                    echo '<span class="pregunta-categoria">' . $categoria . '</span>';
                } else {
                    echo '<span class="pregunta-categoria sin-cat">Sin categorías</span>';
                }
            ?>
            <?= apply_filters('the_content', $post->post_content) ?>
        </div>

        <?php if ($respuestas): ?>
            <div class="pregunta-opciones">
                <?php foreach ($respuestas as $i => $respuesta): 
                    $letra = chr(65 + $i); // A, B, C, D...
                    $es_correcta = $respuesta['correcta'] === '1';
                    ?>
                    <div class="opcion<?= $es_correcta ? ' correcta' : '' ?>">
                        <span class="letra"><?= $letra ?>)</span>
                        <span class="texto"><?= esc_html(wp_strip_all_tags($respuesta['enunciado'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .pregunta-wrapper {
            max-width: 700px;
            margin: 0 auto;
            font-family: system-ui, sans-serif;
        }

        .pregunta-enunciado h3 {
            margin-bottom: 0.5rem;
            color: #d6336c; /* rosado estilo título */
            /* font-size: 1.2rem; */
        }

        .pregunta-enunciado p {
            /* font-size: 1.1rem; */
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .pregunta-opciones {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .opcion {
            display: flex;
            align-items: center;
            border: 2px solid #ddd;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            width: calc(50% - 0.5rem);
            background: #fff;
            transition: all 0.2s;
        }

        .opcion.correcta {
            border-color: #8BC34A;
            background: #f3fff2;
        }

        .letra {
            font-weight: bold;
            margin-right: 0.5rem;
            color: #333;
        }

        .texto {
            flex: 1;
        }

        @media(max-width: 600px) {
            .opcion {
                width: 100%;
            }
        }

        .pregunta-categoria {
            display: inline-block;
            background-color: #fde6ec; /* fondo rosado claro */
            color: #d6336c; /* rosado primario */
            font-weight: 600;
            font-size: 0.9rem;
            padding: 4px 10px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .pregunta-categoria.sin-cat {
            background-color: #dce7f9;
            color: #2363b0;
        }
    </style>

    <?php
    return ob_get_clean();
});


add_shortcode('ver_soluciones', function () {
    global $post;

    // Cargar script solo cuando se usa el shortcode
    wp_enqueue_script(
        'simulador-likes',
        plugin_dir_url(__FILE__) . '../js/simulador-likes.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('simulador-likes', 'SimuladorLikes', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('like_nonce'),
    ]);

    if ($post->post_type !== 'pregunta') {
        return 'Este shortcode solo funciona en preguntas.';
    }

    $soluciones = get_post_meta($post->ID, '_soluciones', true) ?: [];

    if (!$soluciones) {
        return '<p>No hay soluciones aún para esta pregunta.</p>';
    }

    ob_start();
    ?>
    <div class="soluciones-box">
        <h3>Soluciones</h3>
        <?php foreach ($soluciones as $solucion): 
            $user_id = $solucion['user_id'] ?? null;
            $usuario = $user_id ? get_userdata($user_id) : null;
            $nombre = $usuario ? $usuario->display_name : 'Anónimo';
            $avatar = $usuario ? get_avatar_url($user_id, ['size' => 48]) : '';
            $es_aprobada = $solucion['aprobada'] === '1';
            
            $sol_id = $solucion['sol_id'] ?? null;

            $likes = obtener_likes_solucion($post->ID, $sol_id);
            $ya_dio_like = is_user_logged_in() ? usuario_ya_dio_like($post->ID, $sol_id, get_current_user_id()) : false;
            $comentarios = obtener_comentarios_de_solucion($post->ID, $sol_id);
            $fecha = !empty($solucion['fecha']) 
                ? human_time_diff(strtotime($solucion['fecha']), current_time('timestamp')) 
                : 'Fecha desconocida';
        ?>
        <div class="solucion-tarjeta">
            <div class="solucion-encabezado">
                <img src="<?= esc_url($avatar) ?>" alt="<?= esc_attr($nombre) ?>" class="avatar">
                <div class="datos-autor">
                    <div class="autor-nombre">
                        <strong><?= esc_html($nombre) ?></strong>
                        <?php if ($es_aprobada): ?>
                            <span class="etiqueta-solucion">Solución aprobada</span>
                        <?php endif; ?>
                    </div>
                    <span class="fecha">hace <?= esc_html($fecha) ?></span>
                </div>
                <div class="acciones-solucion">
                    <span>❤️ <span class="likes-count"><?= $likes ?></span></span>
                </div>
            </div>
            <div class="solucion-contenido">
                <hr>
                <div class="respuesta-final">
                    <?= wpautop($solucion['contenido']) ?>
                </div>
            </div>
            <div class="solucion-botones">
                <?php if (is_user_logged_in()): ?>
                    <?php if ($ya_dio_like): ?>
                        <button class="btn btn-rojo" disabled title="Ya agradeciste esta solución">
                            ❤️ Ya agradeciste <span class="likes-count"><?= $likes ?></span>
                        </button>
                    <?php else: ?>
                        <button 
                            class="btn btn-rojo btn-like-solucion" 
                            data-post-id="<?= $post->ID ?>" 
                            data-sol-id="<?= esc_attr($solucion['sol_id']) ?>">
                            ❤️ Gracias <span class="likes-count"><?= $likes ?></span>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-rojo" disabled title="Inicia sesión para agradecer">
                        ❤️ Gracias
                    </button>
                <?php endif; ?>
                <button 
                    class="btn btn-comentar toggle-comentarios" 
                    data-sol-id="<?= esc_attr($solucion['sol_id']) ?>">
                    💬 Comentar
                </button>
            </div>
            <div class="lista-comentarios" id="comentarios-<?= esc_attr($solucion['sol_id']) ?>">
                <?php foreach ($comentarios as $comentario): 
                    $coment_user = get_userdata($comentario->user_id);
                ?>
                    <p>
                        <strong><?= esc_html($coment_user->display_name ?? 'Anónimo') ?>:</strong>
                        <?= esc_html($comentario->comentario) ?>
                    </p>
                <?php endforeach; ?>
            </div>
            <div id="comentarios-box-<?= esc_attr($solucion['sol_id']) ?>" style="display: none;">
                <?php if (is_user_logged_in()): ?>
                    <textarea style="width: 100%;" class="comentario-textarea" rows="2" placeholder="Escribe un comentario..."></textarea>
                    <button 
                        class="btn btn-enviar-comentario" 
                        data-post-id="<?= $post->ID ?>" 
                        data-sol-id="<?= esc_attr($solucion['sol_id']) ?>">
                        📩 Enviar
                    </button>
                <?php else: ?>
                    <p><em>Inicia sesión para comentar</em></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <style>
        .solucion-tarjeta {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            font-family: system-ui, sans-serif;
        }

        .solucion-encabezado {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .avatar {
            border-radius: 50%;
            width: 48px;
            height: 48px;
            margin-right: 10px;
        }

        .avatar-mini {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 5px;
        }

        .datos-autor {
            flex: 1;
        }

        .fecha {
            display: block;
            font-size: 0.85rem;
            color: #666;
        }

        .etiqueta-solucion {
            background: #d3f3e6;
            color: #107154;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
            display: inline-block;
        }

        .solucion-contenido {
            margin-top: 15px;
        }

        .respuesta-final {
            font-weight: 500;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .solucion-botones {
            /* margin-top: 10px; */
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }

        .btn-rojo {
            background: #ffe8ea;
            color: #c9184a;
            border: 1px solid #f4cfd4;
        }

        .btn-comentar {
            background: #f1f3f5;
            color: #333;
            border: 1px solid #ccc;
        }

        .btn-icon {
            background: none;
            border: none;
            font-size: 1rem;
        }

        .comentarios-box {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
    </style>

    <?php
    return ob_get_clean();
});


// add_action('wp_enqueue_scripts', function () {
//     wp_enqueue_script(
//         'simulador-likes',
//         plugin_dir_url(__FILE__) . '../js/simulador-likes.js',
//         ['jquery'],
//         '1.0',
//         true
//     );

//     wp_localize_script('simulador-likes', 'SimuladorLikes', [
//         'ajax_url' => admin_url('admin-ajax.php'),
//         'nonce'    => wp_create_nonce('like_nonce'),
//     ]);
// });
