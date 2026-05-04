@extends('errors.layout')

@section('title', 'Página No Localizada')
@section('code', '404')

@section('icon')
    <svg class="w-12 h-12 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection

@section('message')
    {{ $exception->getMessage() ?: 'El recurso que está intentando acceder no se encuentra disponible en nuestros registros o el enlace ha caducado.' }}
@endsection
