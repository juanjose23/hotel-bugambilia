import { es } from 'date-fns/locale';
import { DayPicker } from 'react-day-picker';
import type { DateRange, Matcher } from 'react-day-picker';
import { cn } from '@/modulos/compartido/utilidades/clases';
import 'react-day-picker/style.css';

type CalendarMode = 'single' | 'range';

interface CalendarProps {
    mode?: CalendarMode;
    selected?: Date | DateRange | undefined;
    onSelect?:
        | ((date: Date | undefined) => void)
        | ((range: DateRange | undefined) => void);
    disabled?: (date: Date) => boolean;
    modifiers?: Record<string, Matcher | Matcher[]>;
    modifiersClassNames?: Record<string, string>;
    locale?: object;
    numberOfMonths?: number;
    className?: string;
}

const calendarClassNames = {
    months: 'flex flex-col sm:flex-row gap-6 justify-center items-start w-full',
    month: 'flex flex-col gap-3 min-w-[280px] w-full flex-1',

    month_caption:
        'relative flex h-10 items-center justify-center rounded-2xl border border-border/80 bg-muted/40 font-bold shadow-xs',
    caption_label:
        'text-sm font-black tracking-wide text-foreground capitalize',

    nav: 'flex items-center gap-1',
    button_previous:
        'absolute left-2 top-2 flex h-6 w-6 cursor-pointer items-center justify-center rounded-lg border border-border bg-background text-foreground shadow-xs transition hover:bg-primary/10 hover:text-primary',
    button_next:
        'absolute right-2 top-2 flex h-6 w-6 cursor-pointer items-center justify-center rounded-lg border border-border bg-background text-foreground shadow-xs transition hover:bg-primary/10 hover:text-primary',

    month_grid: 'w-full table-fixed border-separate border-spacing-y-1 mt-1',
    weekdays: '',
    weekday:
        'h-8 text-center text-[11px] font-black uppercase tracking-wider text-muted-foreground',
    week: '',

    day: 'relative h-10 p-0 text-center text-sm',
    day_button:
        'mx-auto flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-transparent font-bold text-foreground transition-all duration-150 hover:bg-primary/15 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-95 disabled:!opacity-25 disabled:!pointer-events-none disabled:!line-through disabled:!text-muted-foreground/50 disabled:!bg-muted/30 aria-disabled:!opacity-25 aria-disabled:!pointer-events-none aria-disabled:!line-through aria-disabled:!text-muted-foreground/50 aria-disabled:!bg-muted/30',

    selected:
        'bg-primary text-primary-foreground font-black hover:bg-primary/90 hover:text-primary-foreground',
    today: 'font-black text-primary underline underline-offset-4 decoration-primary decoration-2',
    outside: 'opacity-30 text-muted-foreground',

    disabled:
        '!cursor-not-allowed !pointer-events-none !opacity-25 !text-muted-foreground/40 !line-through decoration-muted-foreground/50',
    day_disabled:
        '!cursor-not-allowed !pointer-events-none !opacity-25 !text-muted-foreground/40 !line-through decoration-muted-foreground/50',
    button_disabled:
        '!cursor-not-allowed !pointer-events-none !opacity-25 !text-muted-foreground/40 !line-through decoration-muted-foreground/50',

    range_start:
        '!rounded-l-2xl !rounded-r-none bg-primary !text-primary-foreground font-black shadow-sm',
    range_end:
        '!rounded-r-2xl !rounded-l-none bg-primary !text-primary-foreground font-black shadow-sm',
    range_middle: '!rounded-none bg-primary/20 !text-foreground font-extrabold',

    hidden: 'invisible',
};

const Calendar = ({
    mode = 'single',
    selected,
    onSelect,
    disabled,
    modifiers,
    modifiersClassNames,
    locale = es,
    numberOfMonths = 1,
    className,
}: CalendarProps) => {
    const sharedProps = {
        disabled,
        locale,
        numberOfMonths,
        showOutsideDays: false,
        fixedWeeks: true,
        modifiers,
        modifiersClassNames,
        className: cn('p-4', className),
        classNames: calendarClassNames,
    };

    if (mode === 'range') {
        return (
            <DayPicker
                mode="range"
                required={false}
                selected={selected as DateRange | undefined}
                onSelect={onSelect as (range: DateRange | undefined) => void}
                {...sharedProps}
            />
        );
    }

    return (
        <DayPicker
            mode="single"
            selected={selected as Date | undefined}
            onSelect={onSelect as (date: Date | undefined) => void}
            {...sharedProps}
        />
    );
};

Calendar.displayName = 'Calendar';

export { Calendar };
