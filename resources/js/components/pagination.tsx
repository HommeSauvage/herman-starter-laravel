import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { PaginatedData } from '@/types';

type Props = {
    pagination: PaginatedData<unknown>;
    className?: string;
};

/**
 * Prev/next pager driven by Laravel's paginator shape. Renders nothing when
 * there is only one page.
 */
export default function Pagination({ pagination, className }: Props) {
    if (pagination.last_page <= 1) {
        return null;
    }

    return (
        <div
            className={cn(
                'flex items-center justify-between text-sm text-muted-foreground',
                className,
            )}
        >
            <span>
                Page {pagination.current_page} of {pagination.last_page}
            </span>
            <div className="flex gap-2">
                {pagination.prev_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={pagination.prev_page_url} preserveScroll>
                            Previous
                        </Link>
                    </Button>
                ) : null}
                {pagination.next_page_url ? (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={pagination.next_page_url} preserveScroll>
                            Next
                        </Link>
                    </Button>
                ) : null}
            </div>
        </div>
    );
}
