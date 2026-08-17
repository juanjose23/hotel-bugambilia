<table class="data-table">
    <thead><tr>
        <th>Código</th><th>Estado</th><th>Departamento</th><th>Solicitante</th><th style="text-align:center;">Fecha</th><th>Motivo</th>
    </tr></thead>
    <tbody>
        @forelse($items as $row)
        <tr>
            <td><strong>{{ $row->codigo }}</strong></td>
            <td>{{ $row->estado?->label() }}</td>
            <td>{{ $row->departamentoSolicitante?->nombre ?? '—' }}</td>
            <td>{{ $row->colaborador?->persona?->nombre_completo }}</td>
            <td style="text-align:center;">{{ $row->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
            <td style="font-size:9px;color:#555;">{{ $row->motivo ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#888;padding:20px;">Sin solicitudes en el período seleccionado.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background:#f1f5f9;">
            <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:8px;">Total Solicitudes:</td>
            <td style="font-weight:bold;color:#711C37;font-size:13px;padding:8px;">{{ count($items) }}</td>
        </tr>
    </tfoot>
</table>
