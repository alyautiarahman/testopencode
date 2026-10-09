/**
 * Komponen Alpine untuk toast notifikasi.
 * Pesan datang dari session flash ('status') dan otomatis menghilang.
 */
export const toast = () => ({
    show: false,
    message: '',
    type: 'success',
    timer: null,

    init() {
        const initial = document.querySelector('[data-flash-message]');

        if (initial?.dataset.flashMessage) {
            this.fire(initial.dataset.flashMessage, initial.dataset.flashType || 'success');
        }

        // Dapat juga dipicu dari mana pun lewat window event 'toast'.
        window.addEventListener('toast', (e) => this.fire(e.detail.message, e.detail.type));
    },

    fire(message, type = 'success') {
        if (!message) return;

        clearTimeout(this.timer);
        this.message = message;
        this.type = type;
        this.show = true;

        this.timer = setTimeout(() => {
            this.show = false;
        }, 3200);
    },
});
