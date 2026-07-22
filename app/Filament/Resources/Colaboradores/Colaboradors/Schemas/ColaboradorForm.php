<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Schemas;

use App\BusinessLogic\Colaboradores\ValidCodigoColaborador;
use App\BusinessLogic\Personas\PersonaNatural\ValidCedulaNicaragua;
use App\Enums\Personas\Sexo;
use App\Enums\Personas\TipoIdentificacion;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Interactors\Colaboradores\GenerarCodigoColaborador;
use App\Repository\Models\Personas\Persona;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ColaboradorForm
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly GenerarCodigoColaborador $generarCodigo,
    ) {}

    public static function configure(Schema $schema): Schema
    {
        return static::make()->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
    {
        return $schema->components($this->getSchema());
    }

    /** @return array<int, Htmlable|string> */
    private function getSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Información personal')
                    ->description('Nombres y datos básicos')
                    ->icon(Heroicon::User)
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('primer_nombre')
                                    ->label('Primer Nombre')
                                    ->required()
                                    ->prefixIcon(Heroicon::User),
                                TextInput::make('segundo_nombre')
                                    ->label('Segundo Nombre')
                                    ->prefixIcon(Heroicon::User),

                            ]),
                        Group::make([
                            Grid::make()
                                ->schema([
                                    TextInput::make('primer_apellido')
                                        ->label('Primer Apellido')
                                        ->required()
                                        ->prefixIcon(Heroicon::UserCircle),
                                    TextInput::make('segundo_apellido')
                                        ->label('Segundo Apellido')
                                        ->prefixIcon(Heroicon::UserCircle),
                                ]),
                        ])
                            ->relationship('personaNatural'),
                        Grid::make()
                            ->schema([
                                Select::make('pais_id')
                                    ->label('País')
                                    ->relationship('pais', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon(Heroicon::Flag),
                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->prefixIcon(Heroicon::Phone),

                            ]),
                        Group::make([
                            Grid::make()
                                ->schema([
                                    Select::make('sexo')
                                        ->label('Género')
                                        ->options(Sexo::options())
                                        ->required()
                                        ->prefixIcon('heroicon-m-users'),
                                    DatePicker::make('fecha_nacimiento')
                                        ->label('Fecha de Nacimiento')
                                        ->required()
                                        ->prefixIcon(Heroicon::Calendar),
                                ]),
                        ])
                            ->relationship('personaNatural'),
                    ]),

                Step::make('Datos de identificación')
                    ->description('Documentos y domicilio')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Group::make([
                            Grid::make()
                                ->schema([
                                    Select::make('tipo_identificacion')
                                        ->label('Tipo de Documento')
                                        ->options(TipoIdentificacion::options())
                                        ->required()
                                        ->live()
                                        ->searchable()
                                        ->prefixIcon(Heroicon::Document),
                                    TextInput::make('numero_identificacion')
                                        ->label('Número de Documento')
                                        ->required()
                                        ->maxLength(30)
                                        ->rules([
                                            fn (Get $get) => $get('tipo_identificacion') === 'cedula' ? new ValidCedulaNicaragua : null,
                                        ])
                                        ->scopedUnique(modifyQueryUsing: function ($query, Get $get) {
                                            return $query->where('tipo_identificacion', $get('tipo_identificacion'));
                                        })
                                        ->validationMessages([
                                            'unique' => 'Ya existe un registro con este número y tipo de identificación.',
                                        ])
                                        ->prefixIcon(Heroicon::CreditCard),
                                ]),
                        ])
                            ->relationship('personaNatural'),

                        Textarea::make('direccion')
                            ->label('Dirección de Domicilio')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Escriba la dirección exacta...'),
                    ]),

                Step::make('Datos laborales')
                    ->description('Información del expediente laboral')
                    ->icon(Heroicon::Briefcase)
                    ->schema(components: [
                        Group::make(schema: [
                            Grid::make()
                                ->schema(components: [
                                    TextInput::make('codigo')
                                        ->label('Código Interno')
                                        ->default(fn (?Persona $record) => $record?->colaborador->codigo ?? $this->generarCodigo->execute())
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->scopedUnique()
                                        ->rule(new ValidCodigoColaborador)
                                        ->prefixIcon(Heroicon::Hashtag),
                                    TextInput::make('nss')
                                        ->label('Seguro Social (NSS)')
                                        ->placeholder('NSS del colaborador')
                                        ->maxLength(30)
                                        ->prefixIcon(Heroicon::ShieldCheck),
                                    DatePicker::make('fecha_ingreso')
                                        ->label('Fecha de Ingreso')
                                        ->default(now())
                                        ->required()
                                        ->prefixIcon(Heroicon::CalendarDays),
                                    Select::make('estado')
                                        ->label('Estatus Laboral')
                                        ->options(EstadoGeneral::options())
                                        ->default(EstadoGeneral::Activo->value)
                                        ->required()
                                        ->selectablePlaceholder(false)
                                        ->prefixIcon(Heroicon::Check),
                                ]),
                        ])
                            ->relationship('colaborador'),
                    ]),

                Step::make('Fotografía')
                    ->description('Fotografía oficial del colaborador')
                    ->icon(Heroicon::Camera)
                    ->schema([
                        Section::make('Foto de perfil')
                            ->columns(2)
                            ->schema([
                                ImageEntry::make('colaborador.imagen.url')
                                    ->label('Foto actual')
                                    ->disk('public')
                                    ->circular()
                                    ->defaultImageUrl(fn ($state) => 'https://ui-avatars.com/api/?name=Usuario&size=200&background=711c37&color=fff')
                                    ->visibleOn('edit'),
                                FileUpload::make('foto_upload')
                                    ->label('Cambiar fotografía')
                                    ->image()
                                    ->disk('public')
                                    ->directory('colaboradores/fotos')
                                    ->imagePreviewHeight('200')
                                    ->panelAspectRatio('1:1')
                                    ->panelLayout('circle')
                                    ->removeUploadedFileButtonPosition('center-bottom')
                                    ->maxSize(2048),
                            ]),
                    ]),
            ])
                ->columnSpanFull()
                ->persistStepInQueryString()
                ->submitAction(view('filament.resources.colaboradores.colaboradors.wizard-actions')),
        ];
    }
}
