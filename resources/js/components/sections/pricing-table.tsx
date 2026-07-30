import { Link } from '@inertiajs/react';
import { Check, Tag } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';
import type { SectionAction } from '@/components/sections/section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type PricingTier = {
    name: string;
    price: string;
    /** Billing cadence shown next to the price, e.g. "/month". */
    period?: string;
    description?: string;
    features: string[];
    cta?: SectionAction;
    /** Visually emphasized tier (e.g. the recommended plan). */
    highlighted?: boolean;
    /** Small badge on the highlighted tier, e.g. "Most popular". */
    badge?: string;
};

type Props = {
    tiers: PricingTier[];
    className?: string;
};

/** Pricing tiers in a responsive grid; one tier can be highlighted. */
export default function PricingTable({ tiers, className }: Props) {
    if (tiers.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={Tag}
                    title="No plans yet"
                    description="Add pricing tiers to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className}>
            <div
                className={cn(
                    'grid gap-6',
                    tiers.length > 1 && 'md:grid-cols-2',
                    tiers.length > 2 && 'lg:grid-cols-3',
                )}
            >
                {tiers.map((tier) => (
                    <div
                        key={tier.name}
                        className={cn(
                            'relative flex flex-col gap-6 rounded-xl border bg-card p-6',
                            tier.highlighted &&
                                'border-primary shadow-md ring-1 ring-primary',
                        )}
                    >
                        {tier.badge ? (
                            <Badge className="absolute -top-3 left-6">
                                {tier.badge}
                            </Badge>
                        ) : null}
                        <div className="flex flex-col gap-1">
                            <h3 className="font-semibold">{tier.name}</h3>
                            {tier.description ? (
                                <p className="text-sm text-muted-foreground">
                                    {tier.description}
                                </p>
                            ) : null}
                        </div>
                        <p className="flex items-baseline gap-1">
                            <span className="text-4xl font-semibold tracking-tight">
                                {tier.price}
                            </span>
                            {tier.period ? (
                                <span className="text-sm text-muted-foreground">
                                    {tier.period}
                                </span>
                            ) : null}
                        </p>
                        <ul className="flex flex-1 flex-col gap-2 text-sm">
                            {tier.features.map((feature) => (
                                <li
                                    key={feature}
                                    className="flex items-start gap-2"
                                >
                                    <Check
                                        className="mt-0.5 size-4 shrink-0 text-primary"
                                        aria-hidden
                                    />
                                    <span>{feature}</span>
                                </li>
                            ))}
                        </ul>
                        {tier.cta ? (
                            <Button
                                variant={
                                    tier.highlighted
                                        ? (tier.cta.variant ?? 'default')
                                        : (tier.cta.variant ?? 'outline')
                                }
                                asChild
                            >
                                <Link href={tier.cta.href}>
                                    {tier.cta.label}
                                </Link>
                            </Button>
                        ) : null}
                    </div>
                ))}
            </div>
        </Section>
    );
}
