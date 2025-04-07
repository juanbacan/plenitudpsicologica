document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-like-solucion').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const solIndex = this.dataset.solIndex;
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
                    sol_index: solIndex,
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

                } else {
                    alert(data.data.mensaje || 'Ocurrió un error.');
                }
            });
        });
    });
});
