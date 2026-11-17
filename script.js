document.querySelectorAll('.lien-suppr').forEach(lien => {
    lien.addEventListener('click', e => {
        if (!confirm('Supprimer définitivement cet étudiant ?')) {
            e.preventDefault();
        }
    });
});
