<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurante\CrearPedidoPublicoRequest;
use App\Interactors\Restaurante\Pedidos\CrearPedidoPublico;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class CrearPedidoPublicoController extends Controller
{
    public function __invoke(
        CrearPedidoPublicoRequest $request,
        CrearPedidoPublico $interactor,
    ): JsonResponse {
        $validated = $request->validated();

        try {
            /** @var array{nombre: string, email?: ?string, telefono: string, departamento?: ?string, municipio?: ?string, direccion: string, metodo_pago: string, monto_paga_con?: ?string, notas?: ?string, items: array<int, array{plato_id: int, cantidad: int, observaciones?: ?string}>} $validated */
            $resultado = $interactor->ejecutar($validated);

            $anteriores = $request->session()->get('pedidos_publicos', []);
            $request->session()->put('pedidos_publicos', array_values(array_slice(array_unique([...array_filter(is_array($anteriores) ? $anteriores : [], is_int(...)), $resultado['pedido_id']]), -30)));
            $resultado['url_seguimiento'] = route('restaurante.pedido.seguimiento', ['pedido' => $resultado['pedido_id']]);

            return response()->json($resultado, 201);
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'items' => [$e->getMessage()],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar el pedido. Intenta nuevamente más tarde.',
            ], 500);
        }
    }
}
