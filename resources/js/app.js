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

// Analytics Tracking
document.addEventListener('alpine:init', () => {
    Alpine.data('analyticsTracking', () => ({
        startTime: Date.now(),
        url: window.location.href,

        init() {
            // Heartbeat every 20 seconds
            this.timer = setInterval(() => this.sendDuration(), 20000);

            // Send on visibility change (better than beforeunload in mobile)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    this.sendDuration(true);
                }
            });
        },

        destroy() {
            if (this.timer) clearInterval(this.timer);
        },

        sendDuration(isBeacon = false) {
            const duration = Math.floor((Date.now() - this.startTime) / 1000);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token) return;

            const data = {
                url: this.url,
                duration: duration
            };

            if (isBeacon && navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('url', data.url);
                formData.append('duration', data.duration);
                formData.append('_token', token);
                navigator.sendBeacon('/analytics/duration', formData);
            } else {
                fetch('/analytics/duration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                }).catch(() => {});
            }
        }
    }));
});

// Immediate cleanup for dashboard if any leak occurs
if (!document.documentElement.hasAttribute('data-site-theme')) {
    document.documentElement.classList.remove('dark');
}
