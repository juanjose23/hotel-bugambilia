<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDocumento\Schemas;

use App\Enums\EstadoCatalogo;
use App\UseCases\Colaboradores\ObtenerNombreCompleto;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ColaboradorDocumentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Documento Adjunto')
                ->description('Carga de archivos y documentos legales del colaborador.')
                ->columnSpanFull()
                ->schema([
                    Select::make('colaborador_id')
                        ->relationship('colaborador', 'id')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => app(ObtenerNombreCompleto::class)
                                ->nombreCompletoConCodigo($record)
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon(Heroicon::User)
                        ->helperText('Seleccione el colaborador.')
                        ->columnSpanFull(),

                    TextInput::make('tipo')
                        ->label('Tipo de Documento')
                        ->placeholder('Ej. Contrato, Cédula, Título')
                        ->required()
                        ->maxLength(100)
                        ->prefixIcon(Heroicon::DocumentText)
                        ->helperText('Nombre descriptivo del documento.'),

                    FileUpload::make('archivo')
                        ->label('Archivo')
                        ->placeholder('Subir archivo...')
                        ->required()
                        ->disk('public')
                        ->directory('colaboradores/documentos')
                        ->visibility('public')
                        ->downloadable()
                        ->openable()
                        ->helperText('Suba el archivo escaneado en formato PDF o imagen.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
