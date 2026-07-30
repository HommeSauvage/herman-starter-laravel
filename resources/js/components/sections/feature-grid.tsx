import { LayoutGrid } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';
import { cn } from '@/lib/utils';

export type Feature = {
    icon?: LucideIcon;
    title: string;
    description: string;
};

type Props = {
    features: Feature[];
    columns?: 2 | 3 | 4;
    className?: string;
};

const columnClasses = {
    2: 'sm:grid-cols-2',
    3: 'sm:grid-cols-2 lg:grid-cols-3',
    4: 'sm:grid-cols-2 lg:grid-cols-4',
} as const;

/** Icon + title + text cards in a responsive grid. */
export default function FeatureGrid({
    features,
    columns = 3,
    className,
}: Props) {
    if (features.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={LayoutGrid}
                    title="No features yet"
                    description="Add features to this grid to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className}>
            <div className={cn('grid gap-6', columnClasses[columns])}>
                {features.map((feature) => (
                    <div
                        key={feature.title}
                        className="flex flex-col gap-3 rounded-xl border bg-card p-6"
                    >
                        {feature.icon ? (
                            <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <feature.icon className="size-5" aria-hidden />
                            </div>
                        ) : null}
                        <h3 className="font-semibold">{feature.title}</h3>
                        <p className="text-sm text-muted-foreground">
                            {feature.description}
                        </p>
                    </div>
                ))}
            </div>
        </Section>
    );
}
