<?php

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
                        height: 100,
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
