import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Section from '@/components/sections/section';
import type { SectionAction } from '@/components/sections/section';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type Props = {
    eyebrow?: string;
    title: ReactNode;
    lede?: ReactNode;
    actions?: SectionAction[];
    /** `centered` stacks content; `split` puts text next to the media slot. */
    variant?: 'centered' | 'split';
    /** Media slot rendered beside the copy when variant="split". */
    media?: ReactNode;
    className?: string;
};

/** Top-of-page hero. Compose with MediaImage for the split media slot. */
export default function Hero({
    eyebrow,
    title,
    lede,
    actions = [],
    variant = 'centered',
    media,
    className,
}: Props) {
    const copy = (
        <div
            className={cn(
                'flex flex-col gap-6',
                variant === 'centered'
                    ? 'mx-auto max-w-3xl items-center text-center'
                    : 'max-w-xl items-start',
            )}
        >
            {eyebrow ? (
                <p className="text-sm font-semibold tracking-wide text-primary uppercase">
                    {eyebrow}
                </p>
            ) : null}
            <h1 className="text-4xl font-semibold tracking-tight text-balance md:text-5xl">
                {title}
            </h1>
            {lede ? (
                <p className="text-lg text-pretty text-muted-foreground">
                    {lede}
                </p>
            ) : null}
            {actions.length > 0 ? (
                <div className="flex flex-wrap items-center gap-3">
                    {actions.map((action) => (
                        <Button
                            key={action.label}
                            size="lg"
                            variant={action.variant ?? 'default'}
                            asChild
                        >
                            <Link href={action.href}>{action.label}</Link>
                        </Button>
                    ))}
                </div>
            ) : null}
        </div>
    );

    return (
        <Section
            className={cn('py-20 md:py-28', className)}
            containerClassName={
                variant === 'split'
                    ? 'grid items-center gap-12 lg:grid-cols-2'
                    : undefined
            }
        >
            {copy}
            {variant === 'split' && media ? <div>{media}</div> : null}
        </Section>
    );
}
