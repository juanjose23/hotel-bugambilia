{{-- Escucha global de eventos Livewire para descargas de reportes generados.
    - 'report-download-smart': fetch la URL; si el servidor responde 202 (segundo plano)
      muestra una notificación, si responde 200 abre el PDF en una pestaña nueva.
    - 'open-new-tab': abre la URL directamente (formato Excel / preview). --}}
<div
    x-data="{
        pendingTab: null,
        prepararPestana() {
            try {
                this.pendingTab = window.open('', '_blank');
            } catch (e) {
                this.pendingTab = null;
            }
        },
        abrirReporte(url) {
            if (!url) {
                if (this.pendingTab && !this.pendingTab.closed) {
                    this.pendingTab.close();
                }
                this.pendingTab = null;
                return;
            }
            if (this.pendingTab && !this.pendingTab.closed) {
                this.pendingTab.location.href = url;
                this.pendingTab = null;
                return;
            }
            try {
                const win = window.open(url, '_blank');
                if (!win || win.closed || typeof win.closed === 'undefined') {
                    const a = document.createElement('a');
                    a.href = url;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(() => a.remove(), 200);
                }
            } catch (e) {
                window.location.assign(url);
            }
        }
    }"
    x-on:open-new-tab.window="abrirReporte($event.detail?.url || $event.detail?.[0]?.url || (typeof $event.detail === 'string' ? $event.detail : null))"
    x-on:close-pending-tab.window="if (pendingTab && !pendingTab.closed) { pendingTab.close(); } pendingTab = null;"
    x-on:report-download-smart.window="
        (async function(url) {
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json, application/pdf, */*' },
                    credentials: 'same-origin'
                });
                if (res.status === 202) {
                    const data = await res.json();
                    $dispatch('filament-notification', {
                        id: 'reporte-bg-' + Date.now(),
                        type: 'warning',
                        title: 'Reporte en segundo plano',
                        body: data.mensaje ?? 'El reporte se está generando. Recibirás una notificación cuando esté listo para descargar.',
                        duration: 8000,
                    });
                } else {
                    window.open(url, '_blank');
                }
            } catch(e) {
                window.open(url, '_blank');
            }
        })($event.detail.url || $event.detail[0]?.url)
    "
    aria-hidden="true"
    class="hidden"
></div>