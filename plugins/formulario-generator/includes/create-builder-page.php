<?php
add_action('load-post.php', function () {
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    if ($post_id && get_post_type($post_id) === 'formulario') {
        wp_redirect(admin_url('edit.php?post_type=formulario&page=fg_form_builder_visual&form_id=' . $post_id));
        exit;
    }
});

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=formulario',
        'Constructor Visual',
        '',
        'manage_options',
        'fg_form_builder_visual',
        'fg_render_form_builder_visual'
    );
});

function fg_render_form_builder_visual() {
    global $wpdb;

    $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
    $form = get_post($form_id);
    if (!$form || $form->post_type !== 'formulario') {
        echo "<div class='notice notice-error'><p>Formulario no encontrado.</p></div>";
        return;
    }

    $campos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}formulario_campos WHERE formulario_id = $form_id ORDER BY orden ASC");
    ?>

    <div class="wrap">
        <h1>Constructor Visual - <?php echo esc_html($form->post_title); ?></h1>

        <form method="post">
            <input type="hidden" name="fg_form_id" value="<?php echo esc_attr($form_id); ?>">

            <div class="fg-builder-wrap" style="display: flex; gap: 20px;">
                <div class="fg-builder-sidebar" style="width: 30%; background: #f9f9f9; padding: 10px; border: 1px solid #ccc;">
                    <h2>Campos disponibles</h2>
                    <?php
                    $tipos = ['text', 'email', 'textarea', 'number', 'date', 'checkbox', 'radio', 'file', 'select'];
                    foreach ($tipos as $tipo) {
                        echo "<div class='fg-field-type button' data-type='{$tipo}'>" . ucfirst($tipo) . "</div>";
                    }
                    ?>
                </div>

                <div class="fg-builder-canvas" id="fg-drop-area" style="width: 70%; min-height: 300px; border: 2px dashed #aaa; padding: 10px;">
                    <?php foreach ($campos as $campo): ?>
                        <div class="fg-field-instance" style="margin-bottom:10px; border:1px solid #ddd; padding:10px; display: flex; align-items: center; gap: 10px;">
                            <span class="fg-drag-handle" title="Arrastrar">☰</span>
                            <input type="hidden" name="tipo[]" value="<?php echo esc_attr($campo->tipo); ?>">
                            <input type="text" name="etiqueta[]" value="<?php echo esc_attr($campo->etiqueta); ?>" placeholder="Etiqueta">
                            <input type="text" name="nombre[]" value="<?php echo esc_attr($campo->nombre); ?>" placeholder="Nombre">
                            <label><input type="checkbox" name="requerido[]" value="<?php echo esc_attr($campo->nombre); ?>" <?php checked($campo->requerido, 1); ?>> Requerido</label>
                            <button type="button" class="fg-delete-field button">Eliminar</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p><button type="submit" class="button button-primary">Guardar Formulario</button></p>
        </form>

        <div class="wrap">
            <h2>Previsualización del formulario</h2>
            <div id="fg-preview" style="border: 1px solid #ccc; padding: 20px; background: #fff;"></div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.querySelectorAll('.fg-field-type').forEach(el => {
                el.addEventListener('click', () => {
                    const type = el.dataset.type;
                    const campo = document.createElement('div');
                    campo.classList.add('fg-field-instance');
                    campo.style.cssText = 'margin-bottom:10px; border:1px solid #ddd; padding:10px; display:flex; align-items:center; gap:10px;';
                    let extra = '';
                    if (type === 'select') {
                        extra = '<input type="text" name="opciones[]" placeholder="Opción1,Opción2" style="width:200px">';
                    } else {
                        extra = '<input type="hidden" name="opciones[]" value="">';
                    }
                    campo.innerHTML = `
                        <span class="fg-drag-handle" title="Arrastrar">☰</span>
                        <input type="hidden" name="tipo[]" value="${type}">
                        <input type="text" name="etiqueta[]" placeholder="Etiqueta">
                        <input type="text" name="nombre[]" placeholder="Nombre">
                        ${extra}
                        <label><input type="checkbox" name="requerido[]" value=""> Requerido</label>
                        <button type="button" class="fg-delete-field button">Eliminar</button>
                    `;
                    document.getElementById('fg-drop-area').appendChild(campo);
                    actualizarVistaPrevia();
                });
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('fg-delete-field')) {
                    e.preventDefault();
                    e.target.closest('.fg-field-instance').remove();
                    actualizarVistaPrevia();
                }
            });

            new Sortable(document.getElementById('fg-drop-area'), {
                animation: 150,
                ghostClass: 'fg-sortable-ghost',
                handle: '.fg-drag-handle',
                onSort: actualizarVistaPrevia
            });

            function actualizarVistaPrevia() {
                const campos = document.querySelectorAll('#fg-drop-area .fg-field-instance');
                const preview = document.getElementById('fg-preview');
                preview.innerHTML = '';

                campos.forEach(campo => {
                    const tipo = campo.querySelector('input[name="tipo[]"]').value;
                    const etiqueta = campo.querySelector('input[name="etiqueta[]"]').value;
                    const nombre = campo.querySelector('input[name="nombre[]"]').value;
                    const requerido = campo.querySelector('input[type="checkbox"]').checked;

                    const label = document.createElement('label');
                    label.textContent = etiqueta || nombre;
                    label.style.display = 'block';
                    label.style.marginTop = '10px';

                    let input;
                    switch (tipo) {
                        case 'textarea':
                            input = document.createElement('textarea');
                            break;
                        case 'select':
                            input = document.createElement('select');
                            const opciones = campo.querySelector('input[name="opciones[]"]').value.split(',');
                            opciones.forEach(opt => {
                                const option = document.createElement('option');
                                option.value = opt.trim();
                                option.textContent = opt.trim();
                                input.appendChild(option);
                            });
                            break;
                        case 'checkbox':
                        case 'radio':
                            input = document.createElement('input');
                            input.type = tipo;
                            input.value = '1';
                            break;
                        case 'file':
                        case 'date':
                        case 'number':
                        case 'email':
                        case 'text':
                        default:
                            input = document.createElement('input');
                            input.type = tipo || 'text';
                    }

                    input.name = nombre || '';
                    input.required = requerido;
                    input.style.width = '100%';

                    preview.appendChild(label);
                    preview.appendChild(input);
                });
            }

            document.addEventListener('input', actualizarVistaPrevia);
            document.addEventListener('change', actualizarVistaPrevia);
            document.addEventListener('DOMContentLoaded', actualizarVistaPrevia);
        </script>

        <style>
            .fg-sortable-ghost {
                opacity: 0.4;
                background: #e2e2e2;
            }

            .fg-drag-handle {
                cursor: move;
                font-size: 18px;
                user-select: none;
                margin-right: 5px;
            }
        </style>
    </div>
    <?php
}

add_action('admin_init', function () {
    if (
        isset($_POST['fg_form_id'], $_POST['etiqueta'], $_POST['nombre'], $_POST['tipo'])
        && current_user_can('manage_options')
    ) {
        global $wpdb;
        $form_id = intval($_POST['fg_form_id']);
        $wpdb->delete("{$wpdb->prefix}formulario_campos", ['formulario_id' => $form_id]);

        foreach ($_POST['etiqueta'] as $i => $etiqueta) {
            $tipo = sanitize_text_field($_POST['tipo'][$i]);
            $nombre = sanitize_text_field($_POST['nombre'][$i]);
            $requerido = in_array($nombre, $_POST['requerido'] ?? []) ? 1 : 0;

            $wpdb->insert("{$wpdb->prefix}formulario_campos", [
                'formulario_id' => $form_id,
                'tipo' => $tipo,
                'etiqueta' => sanitize_text_field($etiqueta),
                'nombre' => $nombre,
                'requerido' => $requerido,
                'orden' => $i
            ]);
        }

        wp_redirect(admin_url('edit.php?post_type=formulario&page=fg_form_builder_visual&form_id=' . $form_id . '&updated=1'));
        exit;
    }
});
