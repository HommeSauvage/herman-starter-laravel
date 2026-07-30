import type { ReactNode } from 'react';
import Section from '@/components/sections/section';
import { cn } from '@/lib/utils';

type Props = {
    eyebrow?: string;
    title: ReactNode;
    lede?: ReactNode;
    align?: 'left' | 'center';
    className?: string;
};

/** Page/section heading: eyebrow, title, and optional lede paragraph. */
export default function PageHeader({
    eyebrow,
    title,
    lede,
    align = 'left',
    className,
}: Props) {
    return (
        <Section className={className}>
            <div
                className={cn(
                    'flex max-w-2xl flex-col gap-4',
                    align === 'center' && 'mx-auto items-center text-center',
                )}
            >
                {eyebrow ? (
                    <p className="text-sm font-semibold tracking-wide text-primary uppercase">
                        {eyebrow}
                    </p>
                ) : null}
                <h1 className="text-3xl font-semibold tracking-tight text-balance md:text-4xl">
                    {title}
                </h1>
                {lede ? (
                    <p className="text-lg text-pretty text-muted-foreground">
                        {lede}
                    </p>
                ) : null}
            </div>
        </Section>
    );
}
