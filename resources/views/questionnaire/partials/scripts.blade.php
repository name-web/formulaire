<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('qmToggle');
    var sidebar = document.getElementById('qmSidebar');
    var overlay = document.getElementById('qmOverlay');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            toggle.classList.toggle('active');
            if (overlay) { overlay.classList.toggle('visible'); }
        });
    }

    if (overlay && sidebar) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            if (toggle) { toggle.classList.remove('active'); }
            overlay.classList.remove('visible');
        });
    }

    /* Les cartes de choix reflètent l'état réel des champs : le formulaire
       reste utilisable sans JavaScript. */
    document.querySelectorAll('.opt-card input').forEach(function (input) {
        function sync() {
            var card = input.closest('.opt-card');
            if (!card) { return; }

            if (input.type === 'radio') {
                document.querySelectorAll('input[name="' + input.name + '"]').forEach(function (other) {
                    var otherCard = other.closest('.opt-card');
                    if (otherCard) { otherCard.classList.toggle('selected', other.checked); }
                });
            } else {
                card.classList.toggle('selected', input.checked);
            }
        }

        input.addEventListener('change', sync);
        sync();
    });
});
</script>
