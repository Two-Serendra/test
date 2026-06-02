const BridgeMessageType = Object.freeze({
    GET_CONTEXT: 'GET_CONTEXT',
    INIT_CONTEXT: 'INIT_CONTEXT',
    SET_HEADER: 'SET_HEADER',
    TOGGLE_HEADER: 'TOGGLE_HEADER',
});

class CondoBridge {
    #context = null;

    constructor() {
        if (typeof window !== 'undefined') {
            window.addEventListener('message', this.#handleMessage.bind(this));
        }
    }

    #handleMessage(event) {
        const message = event.data;
        if (!message?.type) return;

        if (message.type === BridgeMessageType.INIT_CONTEXT) {
            this.#context = message.payload;
        }
    }

    async getContext() {
        if (this.#context) return this.#context;

        if (typeof window !== 'undefined') {
            const request = {
                id: crypto.randomUUID(),
                type: BridgeMessageType.GET_CONTEXT,
                timestamp: Date.now(),
            };
            window.parent.postMessage(request, '*');
            if (window.top !== window.parent) {
                window.top?.postMessage(request, '*');
            }
        }

        return new Promise((resolve, reject) => {
            const interval = setInterval(() => {
                if (this.#context) {
                    clearInterval(interval);
                    resolve(this.#context);
                }
            }, 100);

            setTimeout(() => {
                if (!this.#context) {
                    clearInterval(interval);
                    reject(new Error('Timeout waiting for shell context'));
                }
            }, 5000);
        });
    }

    setHeader(payload) {
        if (typeof window === 'undefined') return;
        window.parent.postMessage({ type: BridgeMessageType.SET_HEADER, payload }, '*');
    }

    toggleHeader(visible) {
        if (typeof window === 'undefined') return;
        window.parent.postMessage(
            { type: BridgeMessageType.TOGGLE_HEADER, payload: { visible } },
            '*'
        );
    }
}

function isInsideShell() {
    return typeof window !== 'undefined' && window.self !== window.top;
}

export { CondoBridge, BridgeMessageType, isInsideShell };