<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoNotificacion: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Vigente = 1;
    case NoVigente = 2;

    case Info = 3;
    case Success = 4;
    case Warning = 5;
    case Error = 6;

    case ReservationCreated = 10;
    case ReservationCancelled = 11;
    case ReservationConfirmed = 12;
    case CheckInCompleted = 13;
    case CheckOutCompleted = 14;
    case ReservationReminder = 15;

    case MaintenanceDue = 20;
    case MaintenanceOverdue = 21;
    case MaintenanceCompleted = 22;

    case AssetAssigned = 30;
    case AssetUnassigned = 31;

    case StockLow = 40;
    case StockOut = 41;

    case PurchaseOrderCreated = 50;
    case PurchaseOrderApproved = 51;
    case PurchaseOrderReceived = 52;

    case UserRegistered = 60;
    case PasswordChanged = 61;

    case MaintenanceScheduled = 70;
    case MaintenanceOverdueNotification = 71;
    case MaintenanceInProgress = 72;
    case WarrantyExpiring = 73;

    case CleaningRequestCreated = 80;
    case CleaningStaffAssigned = 81;
    case CleaningSuppliesMissing = 82;
    case CleaningReminder = 83;
    case CleaningNewAssignments = 84;

    public function getLabel(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::NoVigente => 'No Vigente',
            self::Info => 'Informativo',
            self::Success => 'Éxito',
            self::Warning => 'Advertencia',
            self::Error => 'Error',
            self::ReservationCreated => 'Reserva Creada',
            self::ReservationCancelled => 'Reserva Cancelada',
            self::ReservationConfirmed => 'Reserva Confirmada',
            self::CheckInCompleted => 'Check-In Completado',
            self::CheckOutCompleted => 'Check-Out Completado',
            self::ReservationReminder => 'Reserva Próxima',
            self::MaintenanceDue => 'Mantenimiento Próximo',
            self::MaintenanceOverdue => 'Mantenimiento Vencido',
            self::MaintenanceCompleted => 'Mantenimiento Completado',
            self::AssetAssigned => 'Activo Asignado',
            self::AssetUnassigned => 'Activo Desasignado',
            self::StockLow => 'Stock Bajo',
            self::StockOut => 'Sin Stock',
            self::PurchaseOrderCreated => 'Orden de Compra Creada',
            self::PurchaseOrderApproved => 'Orden de Compra Aprobada',
            self::PurchaseOrderReceived => 'Compra Recibida',
            self::UserRegistered => 'Usuario Registrado',
            self::PasswordChanged => 'Contraseña Cambiada',
            self::MaintenanceScheduled => 'Mantenimiento Programado',
            self::MaintenanceOverdueNotification => 'Mantenimiento Atrasado',
            self::MaintenanceInProgress => 'Mantenimiento en Proceso',
            self::WarrantyExpiring => 'Garantía por Vencer',
            self::CleaningRequestCreated => 'Solicitud de Limpieza',
            self::CleaningStaffAssigned => 'Personal Asignado',
            self::CleaningSuppliesMissing => 'Faltante de Reposición',
            self::CleaningReminder => 'Recordatorio de Limpieza',
            self::CleaningNewAssignments => 'Nuevas Asignaciones',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::NoVigente => 'danger',
            self::Info => 'info',
            self::Success => 'success',
            self::Warning => 'warning',
            self::Error => 'danger',
            self::ReservationCreated => 'success',
            self::ReservationCancelled => 'danger',
            self::ReservationConfirmed => 'success',
            self::CheckInCompleted => 'success',
            self::CheckOutCompleted => 'info',
            self::ReservationReminder => 'warning',
            self::MaintenanceDue => 'warning',
            self::MaintenanceOverdue => 'danger',
            self::MaintenanceCompleted => 'success',
            self::AssetAssigned => 'info',
            self::AssetUnassigned => 'gray',
            self::StockLow => 'warning',
            self::StockOut => 'danger',
            self::PurchaseOrderCreated => 'info',
            self::PurchaseOrderApproved => 'success',
            self::PurchaseOrderReceived => 'success',
            self::UserRegistered => 'success',
            self::PasswordChanged => 'warning',
            self::MaintenanceScheduled => 'info',
            self::MaintenanceOverdueNotification => 'danger',
            self::MaintenanceInProgress => 'warning',
            self::WarrantyExpiring => 'warning',
            self::CleaningRequestCreated => 'info',
            self::CleaningStaffAssigned => 'success',
            self::CleaningSuppliesMissing => 'danger',
            self::CleaningReminder => 'warning',
            self::CleaningNewAssignments => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Vigente => Heroicon::CheckCircle,
            self::NoVigente => Heroicon::XCircle,
            self::Info => Heroicon::InformationCircle,
            self::Success => Heroicon::CheckCircle,
            self::Warning => Heroicon::ExclamationTriangle,
            self::Error => Heroicon::XCircle,
            self::ReservationCreated => Heroicon::CalendarDays,
            self::ReservationCancelled => Heroicon::CalendarDays,
            self::ReservationConfirmed => Heroicon::CalendarDays,
            self::CheckInCompleted => Heroicon::Key,
            self::CheckOutCompleted => Heroicon::ArrowRightEndOnRectangle,
            self::ReservationReminder => Heroicon::Clock,
            self::MaintenanceDue => Heroicon::Clock,
            self::MaintenanceOverdue => Heroicon::ExclamationCircle,
            self::MaintenanceCompleted => Heroicon::CheckBadge,
            self::AssetAssigned => Heroicon::UserGroup,
            self::AssetUnassigned => Heroicon::UserMinus,
            self::StockLow => Heroicon::ArchiveBoxXMark,
            self::StockOut => Heroicon::ArchiveBoxXMark,
            self::PurchaseOrderCreated => Heroicon::ShoppingCart,
            self::PurchaseOrderApproved => Heroicon::CheckBadge,
            self::PurchaseOrderReceived => Heroicon::Truck,
            self::UserRegistered => Heroicon::UserPlus,
            self::PasswordChanged => Heroicon::Key,
            self::MaintenanceScheduled => Heroicon::Clock,
            self::MaintenanceOverdueNotification => Heroicon::ExclamationCircle,
            self::MaintenanceInProgress => Heroicon::WrenchScrewdriver,
            self::WarrantyExpiring => Heroicon::ShieldCheck,
            self::CleaningRequestCreated => Heroicon::Sparkles,
            self::CleaningStaffAssigned => Heroicon::UserGroup,
            self::CleaningSuppliesMissing => Heroicon::ExclamationTriangle,
            self::CleaningReminder => Heroicon::Clock,
            self::CleaningNewAssignments => Heroicon::ClipboardDocumentList,
        };
    }
}
