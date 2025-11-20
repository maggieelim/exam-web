// public/js/score-picker.js
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.score-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            const value = this.dataset.value;

            target.value = value;

            // Ganti style button aktif
            const group = this.parentElement.querySelectorAll('.score-btn');
            group.forEach(b => {
                b.classList.remove('btn-info');
                b.classList.add('btn-outline-secondary');
            });

            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-info');
        });
    });
});