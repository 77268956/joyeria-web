/*
 * Digital scale reader built on the Web Serial API.
 *
 * The scale is treated as a generic ASCII stream: every line is handed to a
 * configurable parser that extracts the first signed decimal reading. Scales
 * that speak a proprietary protocol can register a custom parser through
 * `setParser()` without touching the rest of the application.
 *
 * The module wires itself up to any element marked with `[data-scale-panel]`
 * and broadcasts readings/status through window events so views stay free of
 * hardware knowledge:
 *   - `scale:reading` -> CustomEvent with detail { value, raw }
 *   - `scale:status`  -> CustomEvent with detail { status }
 */

const BAUD_RATE_SELECTOR = '[data-scale-baud]';

export const ScaleStatus = {
    Unsupported: 'unsupported',
    Disconnected: 'disconnected',
    Connecting: 'connecting',
    Connected: 'connected',
    Error: 'error',
};

export function parseGrams(line) {
    const match = String(line).match(/[-+]?\d+(?:[.,]\d+)?/);

    if (!match) {
        return null;
    }

    const value = Number.parseFloat(match[0].replace(',', '.'));

    return Number.isFinite(value) ? value : null;
}

export class DigitalScale {
    constructor({ parser = parseGrams, baudRate = 9600 } = {}) {
        this.parser = parser;
        this.baudRate = baudRate;
        this.port = null;
        this.reader = null;
        this.decoder = new TextDecoder();
        this.buffer = '';
        this.reading = null;
        this.status = DigitalScale.isSupported()
            ? ScaleStatus.Disconnected
            : ScaleStatus.Unsupported;
    }

    static isSupported() {
        return typeof navigator !== 'undefined' && 'serial' in navigator;
    }

    get lastReading() {
        return this.reading;
    }

    getStatus() {
        return this.status;
    }

    setParser(parser) {
        this.parser = parser;
    }

    onReading(callback) {
        this.readingCallback = callback;

        return this;
    }

    onStatus(callback) {
        this.statusCallback = callback;

        return this;
    }

    async connect(requestPort = true) {
        if (!DigitalScale.isSupported()) {
            this.emitStatus(ScaleStatus.Unsupported);
            throw new Error('Este navegador no soporta la conexión con balanzas (usa Chrome o Edge).');
        }

        if (this.port) {
            return;
        }

        this.emitStatus(ScaleStatus.Connecting);

        try {
            this.port = requestPort ? await navigator.serial.requestPort() : this.port;
            await this.port.open({ baudRate: this.baudRate });
            this.emitStatus(ScaleStatus.Connected);
            this.readLoop();
        } catch (error) {
            this.port = null;
            this.emitStatus(ScaleStatus.Error);
            throw error;
        }
    }

    async disconnect() {
        this.keepReading = false;

        if (this.reader) {
            try {
                await this.reader.cancel();
            } catch (error) {
                // The reader is already closed; nothing to cancel.
            }
        }

        if (this.port) {
            try {
                await this.port.close();
            } catch (error) {
                // Port already released by the browser.
            }
        }

        this.port = null;
        this.reader = null;
        this.buffer = '';
        this.emitStatus(ScaleStatus.Disconnected);
    }

    async readLoop() {
        this.keepReading = true;

        while (this.keepReading && this.port && this.port.readable) {
            this.reader = this.port.readable.getReader();

            try {
                while (this.keepReading) {
                    const { value, done } = await this.reader.read();

                    if (done) {
                        break;
                    }

                    if (value) {
                        this.consume(this.decoder.decode(value));
                    }
                }
            } catch (error) {
                this.emitStatus(ScaleStatus.Error);
                break;
            } finally {
                this.reader.releaseLock();
                this.reader = null;
            }
        }
    }

    consume(chunk) {
        this.buffer += chunk;

        const lines = this.buffer.split(/\r\n|\r|\n/);
        this.buffer = lines.pop() ?? '';

        lines.forEach((line) => {
            const value = this.parser(line.trim());

            if (value === null) {
                return;
            }

            this.reading = value;
            this.readingCallback?.(value, line);
            window.dispatchEvent(new CustomEvent('scale:reading', { detail: { value, raw: line } }));
        });
    }

    emitStatus(status) {
        this.status = status;
        this.statusCallback?.(status);
        window.dispatchEvent(new CustomEvent('scale:status', { detail: { status } }));
    }
}

function mountPanel(scale, panel) {
    const statusEl = panel.querySelector('[data-scale-status]');
    const statusText = panel.querySelector('[data-scale-status-text]');
    const readingEl = panel.querySelector('[data-scale-reading]');
    const connectBtn = panel.querySelector('[data-scale-connect]');
    const disconnectBtn = panel.querySelector('[data-scale-disconnect]');
    const baudSelect = panel.querySelector(BAUD_RATE_SELECTOR);
    const hint = panel.querySelector('[data-scale-hint]');

    const statusLabels = {
        [ScaleStatus.Unsupported]: 'Balanza no disponible',
        [ScaleStatus.Disconnected]: 'Balanza desconectada',
        [ScaleStatus.Connecting]: 'Conectando…',
        [ScaleStatus.Connected]: 'Balanza conectada',
        [ScaleStatus.Error]: 'Error de conexión',
    };

    const renderStatus = (status) => {
        if (statusEl) {
            statusEl.dataset.scaleStatus = status;
            statusText.textContent = statusLabels[status] ?? status;
        }

        if (connectBtn) {
            connectBtn.hidden = status === ScaleStatus.Connected;
            connectBtn.disabled = status === ScaleStatus.Unsupported || status === ScaleStatus.Connecting;
        }

        if (disconnectBtn) {
            disconnectBtn.hidden = status !== ScaleStatus.Connected;
        }

        if (readingEl && status !== ScaleStatus.Connected) {
            readingEl.classList.remove('is-live');
        }
    };

    const renderReading = (value) => {
        if (readingEl) {
            readingEl.textContent = value.toFixed(3);
            readingEl.classList.add('is-live');
        }
    };

    scale.onStatus(renderStatus).onReading(renderReading);

    readingEl && (readingEl.textContent = '—');
    renderStatus(scale.getStatus());

    connectBtn?.addEventListener('click', async () => {
        scale.baudRate = Number(baudSelect?.value) || 9600;

        try {
            await scale.connect();
            hint && (hint.textContent = 'Balanza conectada. Pulsa "Tomar peso de la balanza" en la pieza activa.');
        } catch (error) {
            hint && (hint.textContent = error.message);
        }
    });

    disconnectBtn?.addEventListener('click', async () => {
        await scale.disconnect();
        hint && (hint.textContent = 'Balanza desconectada. Escribe el peso a mano: quedará marcado como captura manual.');
    });

    if (scale.getStatus() === ScaleStatus.Unsupported && hint) {
        hint.textContent = 'Este navegador no soporta Web Serial. Escribe el peso a mano: quedará marcado como captura manual.';
    }
}

export function bootScale() {
    const panels = document.querySelectorAll('[data-scale-panel]');

    if (panels.length === 0) {
        return null;
    }

    const scale = new DigitalScale();

    panels.forEach((panel) => mountPanel(scale, panel));

    window.JoyeriaScale = scale;

    return scale;
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootScale);
    } else {
        bootScale();
    }
}

export default DigitalScale;
