<?php
// Ocultar el bloque de "Campos personalizados" en preguntas
add_action('admin_head', function () {
    $screen = get_current_screen();
    if ($screen->post_type === 'pregunta') {
        echo '<style>#postcustom { display: none !important; }</style>';
    }
});

add_action('add_meta_boxes', function () {
    remove_meta_box('astra_settings_meta_box', 'pregunta', 'normal');
}, 99);

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

add_filter('wp_insert_post_data', function ($data, $postarr) {
    // Solo afecta al tipo 'pregunta'
    if ($data['post_type'] === 'pregunta') {
        // Solo si no tiene título
        // if (empty($data['post_title'])) {
            // Limpiar el contenido y extraer una línea como título
        $content = strip_tags($data['post_content']);
        $content = trim(preg_replace('/\s+/', ' ', $content));
        $title = mb_substr($content, 0, 60); // Máx 60 caracteres
        $data['post_title'] = $title ?: 'Pregunta sin título';
        // }
    }
    return $data;
}, 10, 2);

// Agregar meta box para respuestas en línea
add_action('add_meta_boxes', function () {
    add_meta_box(
        'respuestas_inline',
        'Respuestas de esta pregunta',
        'mostrar_respuestas_inline',
        'pregunta',
        'normal',
        'default'
    );
});

function mostrar_respuestas_inline($post)
{
    $respuestas = get_post_meta($post->ID, '_respuestas', true) ?: [];
    ?>
    <div id="respuestas-container">
        <?php foreach ($respuestas as $index => $respuesta): ?>
            <div class="respuesta-item" data-index="<?= $index ?>">
                <h4>Respuesta <?= $index + 1 ?></h4>
                <textarea name="respuestas[<?= $index ?>][enunciado]" class="editor"><?= esc_textarea($respuesta['enunciado']) ?></textarea>
                <p>
                    <label>
                        <input type="checkbox" name="respuestas[<?= $index ?>][correcta]" value="1" <?= !empty($respuesta['correcta']) ? 'checked' : '' ?>>
                        ¿Es correcta?
                    </label>
                </p>
                <button type="button" class="button button-secondary eliminar-respuesta">Eliminar</button>
                <hr>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-primary" id="agregar-respuesta">+ Añadir respuesta</button>

    <template id="respuesta-template">
        <div class="respuesta-item" data-index="{index}">
            <h4>Respuesta {numero}</h4>
            <textarea name="respuestas[{index}][enunciado]" class="editor"></textarea>
            <p>
                <label>
                    <input type="checkbox" name="respuestas[{index}][correcta]" value="1">
                    ¿Es correcta?
                </label>
            </p>
            <button type="button" class="button button-secondary eliminar-respuesta">Eliminar</button>
            <hr>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let container = document.getElementById('respuestas-container');
            let template = document.getElementById('respuesta-template').innerHTML;
            let btnAgregar = document.getElementById('agregar-respuesta');
            let total = container.children.length;

            // Inicializar los editores existentes
            initTinyMCE();

            btnAgregar.addEventListener('click', function () {
                const index = total++;
                const html = template.replaceAll('{index}', index).replaceAll('{numero}', index + 1);
                const div = document.createElement('div');
                div.innerHTML = html;
                container.appendChild(div.firstElementChild);
                initTinyMCE(`#respuestas-container .respuesta-item:last-child .editor`);
            });

            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('eliminar-respuesta')) {
                    const item = e.target.closest('.respuesta-item');
                    const editorId = item.querySelector('textarea').id;
                    if (tinymce.get(editorId)) tinymce.get(editorId).remove();
                    item.remove();
                }
            });

            function initTinyMCE(selector = '.editor') {
                document.querySelectorAll(selector).forEach((el) => {
                    if (!el.id) el.id = 'editor_' + Math.random().toString(36).substring(2, 10);
                    if (tinymce.get(el.id)) tinymce.get(el.id).remove();
                    tinymce.init({
                        selector: '#' + el.id,
                        menubar: true,
                        toolbar: 'formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link removeformat undo redo',
                        plugins: 'lists link',
                        height: 200,
                        branding: false
                    });
                });
            }
        });
    </script>
    <style>
        .respuesta-item {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #0073aa;
        }
    </style>
    <?php
}



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

    if (isset($_POST['soluciones'])) {
        $soluciones_sanitizadas = [];
        foreach ($_POST['soluciones'] as $solucion) {
            $soluciones_sanitizadas[] = [
                'contenido' => wp_kses_post($solucion['contenido']),
                'aprobada' => isset($solucion['aprobada']) ? '1' : '0',
            ];
        }
        update_post_meta($post_id, '_soluciones', $soluciones_sanitizadas);
    } else {
        delete_post_meta($post_id, '_soluciones');
    }
});


add_action('add_meta_boxes', function () {
    add_meta_box(
        'solucion_inline',
        'Solución de esta pregunta',
        'mostrar_solucion_inline',
        'pregunta',
        'normal',
        'default'
    );
});

function mostrar_solucion_inline($post)
{
    $soluciones = get_post_meta($post->ID, '_soluciones', true) ?: [];

    ?>
    <div id="soluciones-container">
        <?php foreach ($soluciones as $index => $sol): ?>
            <div class="solucion-item" data-index="<?= $index ?>">
                <h4>Solución <?= $index + 1 ?></h4>
                <textarea name="soluciones[<?= $index ?>][contenido]" class="editor"><?= esc_textarea($sol['contenido']) ?></textarea>
                <p>
                    <label>
                        <input type="checkbox" name="soluciones[<?= $index ?>][aprobada]" value="1" <?= !empty($sol['aprobada']) ? 'checked' : '' ?>>
                        ¿Aprobada?
                    </label>
                </p>
                <button type="button" class="button eliminar-solucion">Eliminar</button>
                <hr>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-primary" id="agregar-solucion">+ Añadir solución</button>

    <template id="solucion-template">
        <div class="solucion-item" data-index="{index}">
            <h4>Solución {numero}</h4>
            <textarea name="soluciones[{index}][contenido]" class="editor"></textarea>
            <p>
                <label>
                    <input type="checkbox" name="soluciones[{index}][aprobada]" value="1">
                    ¿Aprobada?
                </label>
            </p>
            <button type="button" class="button eliminar-solucion">Eliminar</button>
            <hr>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('soluciones-container');
            const template = document.getElementById('solucion-template').innerHTML;
            const btnAgregar = document.getElementById('agregar-solucion');
            let total = container.children.length;

            // Inicializar editores existentes
            initTinyMCE('#soluciones-container .editor');

            btnAgregar.addEventListener('click', function () {
                const index = total++;
                const html = template.replaceAll('{index}', index).replaceAll('{numero}', index + 1);
                const div = document.createElement('div');
                div.innerHTML = html;
                container.appendChild(div.firstElementChild);
                initTinyMCE(`#soluciones-container .solucion-item:last-child .editor`);
            });

            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('eliminar-solucion')) {
                    const item = e.target.closest('.solucion-item');
                    const textarea = item.querySelector('textarea');
                    if (tinymce.get(textarea.id)) tinymce.get(textarea.id).remove();
                    item.remove();
                }
            });

            function initTinyMCE(selector = '.editor') {
                document.querySelectorAll(selector).forEach((el) => {
                    if (!el.id) el.id = 'editor_' + Math.random().toString(36).substring(2, 10);
                    if (tinymce.get(el.id)) tinymce.get(el.id).remove();
                    tinymce.init({
                        selector: '#' + el.id,
                        menubar: true,
                        toolbar: 'formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link removeformat undo redo',
                        plugins: 'lists link',
                        height: 200,
                        branding: false
                    });
                });
            }
        });
    </script>

    <style>
        .solucion-item {
            background: #f1f1f1;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #00a32a;
        }
    </style>
    <?php
}
