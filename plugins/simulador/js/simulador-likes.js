document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-like-solucion').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const solId = this.dataset.solId;
            const likesSpan = this.querySelector('.likes-count');

            fetch(SimuladorLikes.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'dar_like_solucion',
                    post_id: postId,
                    sol_id: solId,
                    _ajax_nonce: SimuladorLikes.nonce
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    likesSpan.textContent = data.data.likes;
                    btn.classList.add('liked');
                    btn.disabled = true;
                    btn.innerHTML = `❤️ Ya agradeciste <span class="likes-count">${data.data.likes}</span>`;

                    // actualizar el span con clase "likes-count" con el nuevo número de likes
                    const allLikesSpans = document.querySelectorAll('.likes-count');
                    allLikesSpans.forEach(span => {
                        if (span !== likesSpan) {
                            span.textContent = data.data.likes;
                        }
                    });

                } else {
                    alert(data.data.mensaje || 'Ocurrió un error.');
                }
            });
        });
    });
    
    document.querySelectorAll('.toggle-comentarios').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = this.dataset.solId;
            console.log(index);
            const box = document.getElementById('comentarios-box-' + index);
            box.style.display = box.style.display === 'none' ? 'block' : 'none';
        });
    });
    
    document.querySelectorAll('.btn-enviar-comentario').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const solId = this.dataset.solId;
            const textarea = this.previousElementSibling;
            const texto = textarea.value.trim();
            if (!texto) return;
    
            fetch(SimuladorLikes.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'enviar_comentario_solucion',
                    post_id: postId,
                    sol_id: solId,
                    texto: texto,
                    _ajax_nonce: SimuladorLikes.nonce
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    textarea.value = '';
                    const lista = document.getElementById(`comentarios-${solId}`);
                    const nuevo = document.createElement('p');
                    nuevo.innerHTML = `<strong>${data.data.usuario}:</strong> ${data.data.texto}`;
                    lista.appendChild(nuevo);
                } else {
                    alert(data.data.mensaje || 'Error al comentar.');
                }
            });
        });
    });
    
});

