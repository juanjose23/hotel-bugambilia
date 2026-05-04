@extends('errors.layout')

@section('title', 'Mantenimiento del Nodo')
@section('code', '503')

@section('icon')
    <svg class="w-12 h-12 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
@endsection

@section('message')
    Estamos optimizando la plataforma para garantizar un rendimiento óptimo. El servicio se restablecerá a la brevedad posible.
@endsection
