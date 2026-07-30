import { LayoutList } from 'lucide-react';
import type { ReactNode } from 'react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';
import { cn } from '@/lib/utils';

export type FeatureRow = {
    eyebrow?: string;
    title: string;
    description: string;
    /** Media slot (compose with MediaImage). */
    media?: ReactNode;
};

type Props = {
    rows: FeatureRow[];
    className?: string;
};

/** Alternating media/text rows for longer-form feature storytelling. */
export default function FeatureRows({ rows, className }: Props) {
    if (rows.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={LayoutList}
                    title="Nothing to show yet"
                    description="Add rows to this section to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className}>
            <div className="flex flex-col gap-16">
                {rows.map((row, index) => (
                    <div
                        key={row.title}
                        className="grid items-center gap-8 lg:grid-cols-2 lg:gap-12"
                    >
                        <div
                            className={cn(
                                'flex flex-col gap-4',
                                index % 2 === 1 && 'lg:order-2',
                            )}
                        >
                            {row.eyebrow ? (
                                <p className="text-sm font-semibold tracking-wide text-primary uppercase">
                                    {row.eyebrow}
                                </p>
                            ) : null}
                            <h3 className="text-2xl font-semibold tracking-tight text-balance">
                                {row.title}
                            </h3>
                            <p className="text-pretty text-muted-foreground">
                                {row.description}
                            </p>
                        </div>
                        <div className={cn(index % 2 === 1 && 'lg:order-1')}>
                            {row.media}
                        </div>
                    </div>
                ))}
            </div>
        </Section>
    );
}
