<?php

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
                        height: 100,
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
