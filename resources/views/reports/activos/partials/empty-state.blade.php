@if(($type ?? 'table') === 'table')
    <tr>
        <td colspan="{{ $colspan ?? 6 }}" style="text-align: center; color: #666; padding: 20px;">
            {{ $mensaje ?? 'No se encontraron registros.' }}
        </td>
    </tr>
@else
    <div style="text-align:center;color:#666;padding:40px;">
        {{ $mensaje ?? 'No se encontraron registros.' }}
    </div>
@endif
