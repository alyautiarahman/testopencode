/**
 * Komponen Alpine untuk toggle dark mode.
 * Kelas .dark dipasang di <html>; preferensi disimpan di localStorage.
 */
export const theme = () => ({
    dark: localStorage.getItem('theme') === 'dark',

    toggle() {
        this.dark = !this.dark;
        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },

    init() {
        this.apply();
    },
});
