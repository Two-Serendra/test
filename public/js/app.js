import Alpine from 'https://cdn.jsdelivr.net/npm/alpinejs@3/dist/module.esm.js';
import { CondoBridge, isInsideShell } from '/js/superapp-bridge/index.js';

window.Alpine = Alpine;

let _bridge = null;

Alpine.store('superapp', {
  user: null,
  unit: null,
  units: [],
  accounts: [],
  token: null,
  isLoading: false,

  get bridge() { return _bridge; },

  init() {
    if (!isInsideShell()) return;

    this.isLoading = true;
    _bridge = new CondoBridge();

    _bridge
      .getContext()
      .then((ctx) => {
        this.user     = ctx.user;
        this.unit     = ctx.unit     ?? null;
        this.units    = ctx.units    ?? [];
        this.accounts = ctx.accounts ?? [];
        this.token    = ctx.token;
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
