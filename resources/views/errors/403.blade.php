@extends('errors.layout')

@section('title', 'Acceso Denegado')
@section('code', '403')

@section('icon')
    <svg class="w-12 h-12 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
@endsection

@section('message')
    Usted no posee los privilegios de seguridad necesarios para acceder a esta sección del sistema. Por favor, contacte con su supervisor.
@endsection
