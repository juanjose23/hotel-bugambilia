<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ComandoGenerarModulo extends Command
{
    protected $signature = 'generar:modulo
        {modulo : Nombre del modulo (Clientes, Productos, etc)}
        {--force : Sobrescribe archivos existentes}
        {--cases= : Casos de uso personalizados separados por coma}
        {--filament : Genera recurso de Filament}';

    protected $description = 'Generador basado en UseCases + Filament (nivel 5)';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $modulo = Str::studly($this->argument('modulo'));
        $entidad = Str::studly(Str::singular($modulo));
        $force = (bool) $this->option('force');

        $customCases = $this->parseCustomCases();

        /**
         * ESTRUCTURA
         */
        $dirs = [
            app_path('Models'),
            app_path("UseCases/$modulo"),
            app_path('Http/Controllers'),
            app_path('Http/Requests'),
            app_path('Filament/Resources'),
        ];

        foreach ($dirs as $dir) {
            $this->files->ensureDirectoryExists($dir);
        }

        /**
         * ARCHIVOS BASE
         */
        $files = [
            app_path("Models/$entidad.php") => $this->model($entidad),
            app_path("UseCases/$modulo/Crear$entidad.php") => $this->useCaseCrear($entidad),
            app_path("UseCases/$modulo/Actualizar$entidad.php") => $this->useCaseActualizar($entidad),
            app_path("UseCases/$modulo/Eliminar$entidad.php") => $this->useCaseEliminar($entidad),
            app_path("Http/Controllers/{$entidad}Controller.php") => $this->controller($entidad),
        ];

        /**
         * USECASES PERSONALIZADOS
         */
        foreach ($customCases as $case) {
            $class = $case.$entidad;

            $files[app_path("UseCases/$modulo/$class.php")] =
                $this->customUseCase($class, $entidad);
        }

        /**
         * FILAMENT (OPCIONAL)
         */
        if ($this->option('filament')) {

            Artisan::call('make:filament-resource', [
                'name' => $entidad,
            ]);

            $this->info("Filament Resource creado para $entidad");
        }

        /**
         * CREAR ARCHIVOS
         */
        foreach ($files as $path => $content) {
            if ($this->files->exists($path) && ! $force) {
                $this->warn('Existe: '.basename($path));

                continue;
            }

            $this->files->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $content);

            $this->info('Creado: '.basename($path));
        }

        $this->info("Modulo $modulo generado correctamente.");

        return CommandAlias::SUCCESS;
    }

    /**
     * MODEL
     */
    private function model(string $entidad): string
    {
        $table = Str::snake(Str::pluralStudly($entidad));

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class $entidad extends Model
{
    protected \$table = '$table';
    protected \$guarded = [];
}
PHP;
    }

    /**
     *  CRUD USECASES
     */
    private function useCaseCrear(string $entidad): string
    {
        return <<<PHP
<?php

namespace App\UseCases\\$entidad;

use App\Models\\$entidad;

class Crear{$entidad}
{
    public function execute(array \$data)
    {
        return $entidad::create(\$data);
    }
}
PHP;
    }

    private function useCaseActualizar(string $entidad): string
    {
        return <<<PHP
<?php

namespace App\UseCases\\$entidad;

use App\Models\\$entidad;

class Actualizar{$entidad}
{
    public function execute($entidad \$model, array \$data)
    {
        \$model->update(\$data);
        return \$model;
    }
}
PHP;
    }

    private function useCaseEliminar(string $entidad): string
    {
        return <<<PHP
<?php

namespace App\UseCases;

use App\Models\\$entidad\\$entidad;

class Eliminar{$entidad}
{
    public function execute($entidad \$model): bool
    {
        return \$model->delete();
    }
}
PHP;
    }

    /**
     * USECASE PERSONALIZADO
     */
    private function customUseCase(string $class, string $entidad): string
    {
        return <<<PHP
<?php

namespace App\UseCases;

use App\Models\\$entidad\\$entidad;

class {$class}
{
    public function execute(array \$data)
    {
        // Lógica de negocio personalizada
    }
}
PHP;
    }

    /**
     * CONTROLLER LIMPIO
     */
    private function controller(string $entidad): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$entidad;
use App\UseCases\Crear$entidad;
use App\UseCases\Actualizar$entidad;
use App\UseCases\Eliminar$entidad;
use Illuminate\Http\Request;

class {$entidad}Controller extends Controller
{
    public function store(Request \$request, Crear$entidad \$uc)
    {
        return \$uc->execute(\$request->all());
    }

    public function update(Request \$request, $entidad \$model, Actualizar$entidad \$uc)
    {
        return \$uc->execute(\$model, \$request->all());
    }

    public function destroy($entidad \$model, Eliminar$entidad \$uc)
    {
        \$uc->execute(\$model);
        return response()->noContent();
    }
}
PHP;
    }

    /**
     * FILAMENT RESOURCE
     */


    /**
     * @return Collection<int, string>
     */
    private function parseCustomCases(): Collection
    {
        return collect(explode(',', (string) $this->option('cases')))
            ->map(fn (string $c): string => trim($c))
            ->filter(fn (string $c): bool => $c !== '')
            ->map(fn (string $c): string => Str::studly($c))
            ->values();
    }
}
