import { DayPicker, type DateRange } from 'react-day-picker';
import { es } from 'date-fns/locale';
import { cn } from '@/modules/shared/utils/clases';
import 'react-day-picker/style.css';

type CalendarMode = 'single' | 'range';

interface CalendarProps {
    mode?: CalendarMode;
    selected?: Date | DateRange | undefined;
    onSelect?:
        | ((date: Date | undefined) => void)
        | ((range: DateRange | undefined) => void);
    disabled?: (date: Date) => boolean;
    locale?: object;
    numberOfMonths?: number;
    className?: string;
}

const calendarClassNames = {
    months: 'flex flex-col sm:flex-row gap-2',
    month: 'flex flex-col gap-4',
    month_caption:
        'flex justify-center items-center pt-1 relative font-semibold text-sm',
    caption_label: 'text-sm font-semibold',
    nav: 'flex items-center gap-1',
    button_previous:
        'absolute left-1 top-1 p-1 rounded-md hover:bg-accent hover:text-accent-foreground transition-colors',
    button_next:
        'absolute right-1 top-1 p-1 rounded-md hover:bg-accent hover:text-accent-foreground transition-colors',
    month_grid: 'w-full border-collapse gap-1',
    weekdays: 'flex',
    weekday: 'w-9 text-[11px] font-medium text-muted-foreground text-center',
    week: 'flex w-full mt-1',
    day: 'relative w-9 h-9 p-0 text-center text-sm',
    day_button:
        'w-9 h-9 p-0 rounded-md flex items-center justify-center hover:bg-accent hover:text-accent-foreground transition-colors cursor-pointer',
    selected:
        'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
    today: 'font-bold text-primary',
    outside: 'text-muted-foreground/50 hover:text-muted-foreground',
    disabled: 'text-muted-foreground/30 cursor-not-allowed',
    range_start: 'rounded-l-md',
    range_end: 'rounded-r-md',
    range_middle: 'rounded-none',
    hidden: 'invisible',
};

const Calendar = ({
    mode = 'single',
    selected,
    onSelect,
    disabled,
    locale = es,
    numberOfMonths,
    className,
}: CalendarProps) => {
    const sharedProps = {
        disabled,
        locale,
        numberOfMonths,
        showOutsideDays: true,
        fixedWeeks: true,
        className: cn('p-3', className),
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
