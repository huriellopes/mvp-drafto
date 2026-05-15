import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';

const applyTheme = () => {
    if (!document.documentElement.hasAttribute('data-site-theme')) {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark'); // Extra safety
        return;
    }

    const theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

document.addEventListener('livewire:navigated', applyTheme);
applyTheme();

// Immediate cleanup for dashboard if any leak occurs
if (!document.documentElement.hasAttribute('data-site-theme')) {
    document.documentElement.classList.remove('dark');
}
