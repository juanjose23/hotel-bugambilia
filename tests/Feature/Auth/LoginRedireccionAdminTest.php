<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Repository\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

describe('Redirección de Login para Administradores y Clientes', function (): void {
    test('un usuario administrador es redirigido directamente a /admin tras iniciar sesión', function (): void {
        $admin = User::factory()->create([
            'email' => 'admin_test@hotel.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'admin_test@hotel.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    });

    test('un administrador es redirigido a /admin incluso si existía un url.intended público en la sesión', function (): void {
        $admin = User::factory()->create([
            'email' => 'admin_intended@hotel.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $response = $this->withSession(['url.intended' => 'http://localhost/habitaciones/estandar-16'])
            ->post('/auth/login', [
                'email' => 'admin_intended@hotel.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    });

    test('un usuario con rol super_admin es redirigido a /admin', function (): void {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'email' => 'superadmin_test@hotel.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
        $admin->assignRole('super_admin');

        $response = $this->post('/auth/login', [
            'email' => 'superadmin_test@hotel.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    });

    test('un cliente normal es redirigido al home tras iniciar sesión', function (): void {
        $cliente = User::factory()->create([
            'email' => 'cliente_test@hotel.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'cliente_test@hotel.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($cliente);
    });

    test('si un admin autenticado visita /auth/login es redirigido a /admin', function (): void {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/auth/login');

        $response->assertRedirect('/admin');
    });

    test('si un cliente autenticado visita /auth/login es redirigido al home', function (): void {
        $cliente = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($cliente)->get('/auth/login');

        $response->assertRedirect('/');
    });
});
