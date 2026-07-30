import { Form } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

type Props = {
    /** URL the GET form submits to (usually the current index route). */
    action: string;
    /** Query parameter name. Defaults to "search". */
    name?: string;
    defaultValue?: string;
    placeholder?: string;
    /** Rendered as data-test on the input for browser tests. */
    testId?: string;
    className?: string;
};

/**
 * Icon input + GET form pattern for filterable index pages. Submits the
 * query string so searches stay shareable and bookmarkable.
 */
export default function SearchInput({
    action,
    name = 'search',
    defaultValue = '',
    placeholder = 'Search…',
    testId,
    className,
}: Props) {
    return (
        <Form
            action={action}
            method="get"
            className={cn('flex max-w-md gap-2', className)}
        >
            <div className="relative flex-1">
                <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    name={name}
                    defaultValue={defaultValue}
                    placeholder={placeholder}
                    className="pl-9"
                    data-test={testId}
                />
            </div>
            <Button type="submit" variant="secondary">
                Search
            </Button>
        </Form>
    );
}
