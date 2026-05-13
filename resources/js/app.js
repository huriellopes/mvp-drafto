import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        darkMode: localStorage.getItem('theme') === 'dark' || 
                 (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),

        init() {
            this.apply();
        },

        toggle() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            this.apply();
        },

        apply() {
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
});

document.addEventListener('livewire:navigated', () => {
    if (typeof Alpine !== 'undefined' && Alpine.store('theme')) {
        Alpine.store('theme').apply();
    }
});
