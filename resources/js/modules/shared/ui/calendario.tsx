import * as React from 'react';
import { cn } from '@/modules/shared/utils/clases';
interface CalendarProps {
    mode?: 'single' | 'range';
    selected?: Date | undefined;
    onSelect?: (date: Date | undefined) => void;
    disabled?: (date: Date) => boolean;
    locale?: object;
    initialFocus?: boolean;
    numberOfMonths?: number;
    className?: string;
    [key: string]: unknown;
}
const Calendar = ({ className, selected, onSelect }: CalendarProps) => {
    const dateValue =
        selected instanceof Date ? selected.toISOString().split('T')[0] : '';
    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const val = e.target.value;

        if (val && onSelect) {
            onSelect(new Date(val + 'T12:00:00'));
        }
    };

    return (
        <div className={cn('p-3', className)}>
            <input
                type="date"
                value={dateValue}
                onChange={handleChange}
                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            />
        </div>
    );
};
Calendar.displayName = 'Calendar';
export { Calendar };
