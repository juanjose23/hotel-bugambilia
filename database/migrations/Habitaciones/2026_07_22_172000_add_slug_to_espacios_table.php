<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('espacios', function (Blueprint $table) {
            $table->string('slug', 180)->nullable()->unique()->after('nombre')->comment('URL amigable única del espacio');
        });

        // Rellenar (backfill) slugs para registros existentes
        $espacios = DB::table('espacios')->whereNull('slug')->get();
        foreach ($espacios as $espacio) {
            $baseSlug = Str::slug((string) $espacio->nombre);
            if ($baseSlug === '') {
                $baseSlug = 'espacio-'.$espacio->id;
            }

            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('espacios')->where('slug', $slug)->where('id', '!=', $espacio->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            DB::table('espacios')->where('id', $espacio->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('espacios', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
