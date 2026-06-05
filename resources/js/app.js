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
    this.isLoading = true;

    _bridge = new CondoBridge();

    if (!isInsideShell()) {
      console.warn('[Superapp] Not in shell - skipping bridge');
      this.isLoading = false;
      return;
    }

    _bridge.getContext()
      .then((ctx) => {
        console.log('[Superapp] CTX:', ctx);

        this.user = ctx?.user ?? null;
        this.unit = ctx?.unit ?? null;
        this.units = ctx?.units ?? [];
        this.accounts = ctx?.accounts ?? [];
        this.token = ctx?.token ?? null;
      })
      .catch((err) => {
        console.error('[Superapp] Bridge error:', err);
      })
      .finally(() => {
        this.isLoading = false;
      });
  },
});

Alpine.start();
Alpine.store('superapp').init();