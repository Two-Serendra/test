import Alpine from 'alpinejs';
import { CondoBridge, isInsideShell } from './superapp-bridge/index.js';

window.Alpine = Alpine;

Alpine.store('superapp', {
    user: null,
    unit: null,
    token: null,
    isLoading: false,
    bridge: null,

    init() {
        if (!isInsideShell()) return;  // no-op in local dev

        this.isLoading = true;
        this.bridge = new CondoBridge();

        this.bridge
            .getContext()
            .then((ctx) => {
                this.user = ctx.user;
                this.unit = ctx.unit ?? null;
                this.token = ctx.token;
            })
            .catch((err) => {
                console.error('[SuperappStore] Failed to get shell context:', err);
            })
            .finally(() => {
                this.isLoading = false;
            });
    },
});

Alpine.start();