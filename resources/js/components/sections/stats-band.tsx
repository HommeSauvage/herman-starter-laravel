import { ChartColumn } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';
import { cn } from '@/lib/utils';

export type Stat = {
    value: string;
    label: string;
    description?: string;
};

type Props = {
    stats: Stat[];
    className?: string;
};

/** Band of key numbers (value + label) in a responsive grid. */
export default function StatsBand({ stats, className }: Props) {
    if (stats.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={ChartColumn}
                    title="No stats yet"
                    description="Add stats to this band to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={cn('border-y bg-muted/50', className)}>
            <dl className="grid [grid-template-columns:repeat(auto-fit,minmax(min(12rem,100%),1fr))] gap-8">
                {stats.map((stat) => (
                    <div key={stat.label} className="flex flex-col gap-1">
                        <dt className="order-2 text-sm text-muted-foreground">
                            {stat.label}
                        </dt>
                        <dd className="order-1 text-4xl font-semibold tracking-tight">
                            {stat.value}
                        </dd>
                        {stat.description ? (
                            <dd className="order-3 text-sm text-muted-foreground">
                                {stat.description}
                            </dd>
                        ) : null}
                    </div>
                ))}
            </dl>
        </Section>
    );
}
