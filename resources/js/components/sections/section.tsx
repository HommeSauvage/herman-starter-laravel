import type { InertiaLinkProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/**
 * Shared wrapper for page sections: vertical rhythm + centered container.
 * Compose the blocks in `components/sections/` on top of this.
 */
export default function Section({
    children,
    className,
    containerClassName,
}: {
    children: ReactNode;
    className?: string;
    containerClassName?: string;
}) {
    return (
        <section className={cn('py-16 md:py-24', className)}>
            <div
                className={cn(
                    'mx-auto w-full max-w-6xl px-4 md:px-6',
                    containerClassName,
                )}
            >
                {children}
            </div>
        </section>
    );
}

/** Link target accepted by section actions (Wayfinder objects or strings). */
export type SectionHref = NonNullable<InertiaLinkProps['href']>;

/** Call-to-action rendered as a Button + Link inside sections. */
export type SectionAction = {
    label: string;
    href: SectionHref;
    variant?: 'default' | 'secondary' | 'outline';
};
