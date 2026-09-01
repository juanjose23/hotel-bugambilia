import { Radio as RadioPrimitive } from '@base-ui/react/radio';
import { RadioGroup as RadioGroupPrimitive } from '@base-ui/react/radio-group';
import { CircleIcon } from 'lucide-react';
import type * as React from 'react';

import { cn } from '@/modules/shared/utils/clases';

interface RadioGroupProps extends Omit<
    React.ComponentProps<typeof RadioGroupPrimitive>,
    'onValueChange'
> {
    onValueChange?: (value: string) => void;
}

function RadioGroup({ className, onValueChange, ...props }: RadioGroupProps) {
    return (
        <RadioGroupPrimitive
            data-slot="radio-group"
            className={cn('grid gap-3', className)}
            onValueChange={
                onValueChange ? (val) => onValueChange(String(val)) : undefined
            }
            {...props}
        />
    );
}

function RadioGroupItem({ className, ...props }: RadioPrimitive.Root.Props) {
    return (
        <RadioPrimitive.Root
            data-slot="radio-group-item"
            className={cn(
                'aspect-square size-4 shrink-0 rounded-full border border-input text-primary shadow-xs transition-shadow outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 data-checked:border-primary data-checked:bg-primary data-checked:text-primary-foreground dark:bg-input/30 dark:data-checked:bg-primary',
                className,
            )}
            {...props}
        >
            <RadioPrimitive.Indicator
                data-slot="radio-group-indicator"
                className="flex items-center justify-center text-current [&>svg]:size-2 [&>svg]:fill-current"
            >
                <CircleIcon />
            </RadioPrimitive.Indicator>
        </RadioPrimitive.Root>
    );
}

export { RadioGroup, RadioGroupItem };
