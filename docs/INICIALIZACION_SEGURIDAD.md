<?php
/*
|--------------------------------------------------------------------------
| Procurement Initialization Guide
|--------------------------------------------------------------------------
|
| This guide details the steps to initialize the procurement system and 
| manage roles/permissions in the Hotel Bugambilias project.
|
*/

# Quick Initialization Steps

1. Install Shield Tables:
   php artisan shield:install

2. Generate Global Permissions:
   php artisan shield:generate --all

3. Create First Super Admin:
   php artisan shield:super-admin

# Managing Procurement Permissions

To enable the comparison dashboard and reporting features, ensure the 
following custom permissions are checked in the 'Custom Permissions' 
tab of the Role editor:

- view_comparativa_cotizaciones: Access the comparison dashboard.
- imprimir_solicitud: Permission to generate Request PDFs.
- imprimir_orden_compra: Permission to generate Purchase Order PDFs.
- imprimir_recepcion: Permission to generate Goods Received PDFs.
- exportar_compras_excel: Permission to download Excel reports.

# Troubleshooting

If buttons or pages don't appear after assigning permissions:
- Run 'php artisan cache:forget spatie.permission.cache'
- Ensure the user has the 'panel_user' role in addition to their specific role.
