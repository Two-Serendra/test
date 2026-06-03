import Alpine from 'alpinejs';
import { CondoBridge, isInsideShell } from './superapp-bridge/index.js';

window.Alpine = Alpine;

// Held outside the reactive store so Alpine never wraps the instance in a
// Proxy. Private/regular fields on the instance stay accessible normally.
let _bridge = null;

Alpine.store('superapp', {
  user: null,
  unit: null,
  units: [],
  accounts: [],
  token: null,
  isLoading: false,

  // Getter returns the raw bridge instance, not a reactive proxy of it.
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
