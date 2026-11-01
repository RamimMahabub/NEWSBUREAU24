import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.data('ticker', (stories) => ({
    stories, index: 0, paused: false, timer: null,
    init() { this.paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches; this.timer = setInterval(() => { if (!this.paused && !document.hidden) this.next(); }, 7000); },
    destroy() { clearInterval(this.timer); },
    next() { this.index = (this.index + 1) % this.stories.length; },
    previous() { this.index = (this.index - 1 + this.stories.length) % this.stories.length; },
}));
Alpine.start();
