import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    icon?: LucideIcon;
    title: string;
    description?: string;
    action?: ReactNode;
    /** Rendered as data-test for browser tests. */
    testId?: string;
    className?: string;
};

/**
 * Shared empty state for lists, grids, and sections with no content.
 * Pass an optional action (usually a Button) to guide the next step.
 */
export default function EmptyState({
    icon: Icon,
    title,
    description,
    action,
    testId,
    className,
}: Props) {
    return (
        <div
            data-test={testId}
            className={cn(
                'flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center',
                className,
            )}
        >
            {Icon ? (
                <Icon className="size-10 text-muted-foreground" aria-hidden />
            ) : null}
            <div className="space-y-1">
                <p className="font-medium">{title}</p>
                {description ? (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                ) : null}
            </div>
            {action}
        </div>
    );
}
