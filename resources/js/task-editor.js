/**
 * Komponen Alpine untuk modal edit task.
 * Menyimpan satu form di halaman, diisi lewat open(task) dari tiap baris task.
 *
 * Bentuk data task cocok dengan array yang dikirim Blade lewat attrs().
 */
export const editor = () => ({
    open: false,
    saving: false,
    errors: {},
    form: {
        id: null,
        title: '',
        description: '',
        priority: 'medium',
        due_date: '',
    },

    show(task) {
        this.form = {
            id: task.id,
            title: task.title ?? '',
            description: task.description ?? '',
            priority: task.priority ?? 'medium',
            due_date: task.due_date ?? '',
        };
        this.errors = {};
        this.open = true;

        this.$nextTick(() => this.$refs.titleInput?.focus());
    },

    close() {
        if (this.saving) return;
        this.open = false;
    },

    get action() {
        return `/tasks/${this.form.id}`;
    },

    async submit(event) {
        event.preventDefault();
        this.saving = true;
        this.errors = {};

        const body = new FormData(event.target);
        body.set('_method', 'put');

        try {
            const response = await fetch(event.target.action, {
                method: 'POST',
                body,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                this.open = false;
                // Muat ulang agar statistik, filter, dan urutan ikut tersinkron.
                window.location.reload();
                return;
            }

            if (response.status === 422) {
                const payload = await response.json();
                this.errors = payload.errors ?? {};
                return;
            }

            window.location.reload();
        } catch (error) {
            this.errors = { title: ['Terjadi kesalahan jaringan, coba lagi.'] };
        } finally {
            this.saving = false;
        }
    },
});
