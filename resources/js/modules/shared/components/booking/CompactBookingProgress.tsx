import { Check } from 'lucide-react';

export interface StepItem {
    num: number;
    title: string;
    subtitle?: string;
}

export interface CompactBookingProgressProps {
    steps: StepItem[];
    currentStep: number;
    onStepClick?: (stepNumber: number) => void;
    className?: string;
}

export const CompactBookingProgress = ({
    steps,
    currentStep,
    onStepClick,
    className = '',
}: CompactBookingProgressProps) => {
    const totalSteps = steps.length;
    const progressPercent = Math.round(
        ((currentStep - 1) / (totalSteps - 1 || 1)) * 100,
    );

    const activeStepData = steps.find((s) => s.num === currentStep) || steps[0];

    return (
        <div
            className={`rounded-2xl border border-border/70 bg-card/60 p-3.5 backdrop-blur-md ${className}`}
        >
            {/* Header compacto con Paso actual y porcentaje */}
            <div className="flex items-center justify-between text-xs">
                <div className="flex items-center gap-2">
                    <span className="flex size-5 items-center justify-center rounded-full bg-primary text-[10px] font-black text-primary-foreground">
                        {currentStep}
                    </span>
                    <span className="font-bold text-foreground">
                        {activeStepData?.title}
                    </span>
                    {activeStepData?.subtitle && (
                        <span className="hidden text-muted-foreground sm:inline">
                            · {activeStepData.subtitle}
                        </span>
                    )}
                </div>
                <span className="text-[11px] font-bold text-muted-foreground">
                    Paso {currentStep} de {totalSteps} ({progressPercent}%)
                </span>
            </div>

            {/* Barra de progreso visual delgada */}
            <div
                className="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-muted"
                role="progressbar"
                aria-valuenow={progressPercent}
                aria-valuemin={0}
                aria-valuemax={100}
                aria-label={`Progreso del flujo de reserva: ${progressPercent}%`}
            >
                <div
                    className="h-full rounded-full bg-primary transition-all duration-300 ease-out"
                    style={{ width: `${progressPercent}%` }}
                />
            </div>

            {/* Navegación sutil de pasos interactivos en desktop */}
            <ol
                role="list"
                className="mt-2.5 hidden grid-flow-col justify-between gap-1 sm:grid"
            >
                {steps.map((step) => {
                    const isPassed = step.num < currentStep;
                    const isCurrent = step.num === currentStep;
                    const isClickable = onStepClick && step.num <= currentStep;

                    return (
                        <li
                            key={step.num}
                            className="flex items-center gap-1.5"
                        >
                            <button
                                type="button"
                                disabled={!isClickable}
                                onClick={() =>
                                    isClickable && onStepClick?.(step.num)
                                }
                                aria-current={isCurrent ? 'step' : undefined}
                                className={`flex items-center gap-1.5 rounded-lg px-2 py-1 text-[11px] font-bold transition-colors ${
                                    isCurrent
                                        ? 'bg-primary/10 text-primary dark:text-rose-300'
                                        : isPassed
                                          ? 'cursor-pointer text-foreground hover:text-primary'
                                          : 'cursor-default text-muted-foreground/60'
                                }`}
                            >
                                <span
                                    className={`flex size-4 items-center justify-center rounded-full text-[9px] font-black ${
                                        isCurrent
                                            ? 'bg-primary text-primary-foreground'
                                            : isPassed
                                              ? 'bg-muted-foreground/20 text-foreground'
                                              : 'bg-muted text-muted-foreground/50'
                                    }`}
                                >
                                    {isPassed ? (
                                        <Check className="size-2.5 stroke-[3]" />
                                    ) : (
                                        step.num
                                    )}
                                </span>
                                <span>{step.title}</span>
                            </button>
                        </li>
                    );
                })}
            </ol>
        </div>
    );
};

export default CompactBookingProgress;
