/**
 * Light/dark appearance preference, persisted to localStorage. Defaults to
 * the OS preference on first visit (before any explicit choice is stored),
 * but there is no selectable "auto/system" mode — once set, it stays put.
 */
export default function appearance(Alpine) {
    const apply = (value) => {
        document.documentElement.classList.toggle('dark', value === 'dark');
    };

    const initial = localStorage.getItem('appearance')
        ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    Alpine.store('appearance', {
        value: initial,

        set(value) {
            this.value = value;
            localStorage.setItem('appearance', value);
            apply(value);
        },
    });
}
