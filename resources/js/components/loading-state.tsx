import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

type Props = {
    /**
     * `spinner` for short waits (form submits, small fetches), `skeleton`
     * for content-shaped placeholders (deferred props).
     */
    variant?: 'spinner' | 'skeleton';
    label?: string;
    /** Number of skeleton rows when variant="skeleton". */
    rows?: number;
    className?: string;
};

/**
 * Loading convention: deferred props render skeletons, form submissions
 * rely on the form's `processing` state, and everything else gets a spinner.
 */
export default function LoadingState({
    variant = 'spinner',
    label = 'Loading…',
    rows = 3,
    className,
}: Props) {
    if (variant === 'skeleton') {
        return (
            <div className={cn('flex flex-col gap-3', className)}>
                {Array.from({ length: rows }, (_, index) => (
                    <Skeleton
                        key={index}
                        className={cn('h-16 w-full', index === 0 && 'h-20')}
                    />
                ))}
                <span className="sr-only">{label}</span>
            </div>
        );
    }

    return (
        <div
            className={cn(
                'flex items-center justify-center gap-2 py-10 text-sm text-muted-foreground',
                className,
            )}
            role="status"
        >
            <Spinner className="size-5" />
            <span>{label}</span>
        </div>
    );
}
