import Sortable from 'sortablejs';
export function mount(root, wire) {
    function initialize() {
        root.querySelectorAll('[data-library], [data-layout-zone]').forEach(el => {
            if (Sortable.get(el)) return;
            const library = el.hasAttribute('data-library');
            Sortable.create(el, {
                group: {name: 'frontpage', pull: library ? 'clone' : true, put: !library},
                sort: !library, draggable: '[data-id]', handle: '.nb-handle', animation: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 160,
                forceFallback: true, fallbackOnBody: true, fallbackTolerance: 3,
                ghostClass: 'nb-drag-ghost', chosenClass: 'nb-drag-chosen', delayOnTouchOnly: true, delay: 150,
                onEnd: async evt => {
                    if (evt.to.hasAttribute('data-library')) return;
                    let lead = [...root.querySelectorAll('[data-layout-zone="lead"] > [data-id]')].map(el => Number(el.dataset.id));
                    let top = [...root.querySelectorAll('[data-layout-zone="top"] > [data-id]')].map(el => Number(el.dataset.id));
                    if (!lead.length && top.length) {
                        const moved = Number(evt.item.dataset.id);
                        lead = [top.find(id => id !== moved) ?? moved];
                    }
                    if (lead.length > 1) { const moved = Number(evt.item.dataset.id); top = [...lead.filter(id => id !== moved), ...top]; lead = [moved]; }
                    top = [...new Set(top)].filter(id => !lead.includes(id)).slice(0, 4);
                    try { await wire.updateLayout({lead, top}); }
                    catch {
                        const message = document.createElement('p');
                        message.className = 'nb-error';
                        message.setAttribute('role', 'alert');
                        message.textContent = 'The connection was interrupted. Keep this page open and save the draft after reconnecting.';
                        root.prepend(message);
                    }
                },
            });
        });
    }
    initialize();
    const observer = new MutationObserver(initialize);
    observer.observe(root, {childList:true,subtree:true});
    // Alpine calls this when navigating away from a component.
    root.addEventListener('destroy', () => observer.disconnect(), {once:true});
}
