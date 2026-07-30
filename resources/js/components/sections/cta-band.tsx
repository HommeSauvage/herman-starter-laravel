import { Link } from '@inertiajs/react';
import Section from '@/components/sections/section';
import type { SectionAction } from '@/components/sections/section';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type Props = {
    title: string;
    lede?: string;
    actions?: SectionAction[];
    className?: string;
};

/** Closing call-to-action band, typically the last section on a page. */
export default function CtaBand({
    title,
    lede,
    actions = [],
    className,
}: Props) {
    return (
        <Section className={className}>
            <div className="flex flex-col items-center gap-6 rounded-2xl bg-primary px-6 py-16 text-center text-primary-foreground">
                <h2 className="max-w-2xl text-3xl font-semibold tracking-tight text-balance md:text-4xl">
                    {title}
                </h2>
                {lede ? (
                    <p className="max-w-xl text-lg text-primary-foreground/80">
                        {lede}
                    </p>
                ) : null}
                {actions.length > 0 ? (
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        {actions.map((action) => (
                            <Button
                                key={action.label}
                                size="lg"
                                variant={action.variant ?? 'secondary'}
                                asChild
                                className={cn(
                                    (action.variant ?? 'secondary') ===
                                        'secondary' &&
                                        'bg-primary-foreground text-primary hover:bg-primary-foreground/90',
                                )}
                            >
                                <Link href={action.href}>{action.label}</Link>
                            </Button>
                        ))}
                    </div>
                ) : null}
            </div>
        </Section>
    );
}
