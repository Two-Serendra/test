const BridgeMessageType = Object.freeze({
  GET_CONTEXT: 'GET_CONTEXT',
  INIT_CONTEXT: 'INIT_CONTEXT',
  SET_HEADER: 'SET_HEADER',
  TOGGLE_UI: 'TOGGLE_UI',
});

class CondoBridge {
  // Regular property instead of private field — Alpine/Vue reactive proxies
  // wrap class instances and private fields (#field) are inaccessible through
  // a Proxy, which would silently break getContext().
  _context = null;

  constructor() {
    if (typeof window !== 'undefined') {
      window.addEventListener('message', this._handleMessage.bind(this));
    }
  }

  _handleMessage(event) {
    const message = event.data;
    if (!message?.type) return;

    if (message.type === BridgeMessageType.INIT_CONTEXT) {
      this._context = message.payload;
    }
  }

  async getContext() {
    if (this._context) return this._context;

    if (typeof window !== 'undefined') {
      window.parent.postMessage(
        {
          id: crypto.randomUUID(),
          type: BridgeMessageType.GET_CONTEXT,
          timestamp: Date.now(),
        },
        '*'
      );
    }

    return new Promise((resolve, reject) => {
      const interval = setInterval(() => {
        if (this._context) {
          clearInterval(interval);
          resolve(this._context);
        }
      }, 100);

      setTimeout(() => {
        if (!this._context) {
          clearInterval(interval);
          reject(new Error('Timeout waiting for shell context'));
        }
      }, 5000);
    });
  }

  // Shell schema requires id + timestamp on every message (BaseMessageSchema).
  setHeader(payload) {
    if (typeof window === 'undefined') return;
    window.parent.postMessage(
      {
        id: crypto.randomUUID(),
        type: BridgeMessageType.SET_HEADER,
        payload,
        timestamp: Date.now(),
      },
      '*'
    );
  }

  // Shell type is TOGGLE_UI with { isVisible }, not TOGGLE_HEADER / { visible }.
  toggleHeader(visible) {
    if (typeof window === 'undefined') return;
    window.parent.postMessage(
      {
        id: crypto.randomUUID(),
        type: BridgeMessageType.TOGGLE_UI,
        payload: { isVisible: visible },
        timestamp: Date.now(),
      },
      '*'
    );
  }
}

function isInsideShell() {
  return typeof window !== 'undefined' && window.self !== window.top;
}

export { CondoBridge, BridgeMessageType, isInsideShell };
