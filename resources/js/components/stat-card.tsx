import type { LucideIcon } from 'lucide-react';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

type Props = {
    icon?: LucideIcon;
    label: string;
    value: string | number;
    description?: string;
    className?: string;
};

/** Single KPI card for admin dashboards. Compose in a responsive grid. */
export default function StatCard({
    icon: Icon,
    label,
    value,
    description,
    className,
}: Props) {
    return (
        <Card className={cn('gap-2', className)}>
            <CardHeader>
                <CardDescription className="flex items-center gap-2">
                    {Icon ? <Icon className="size-4" aria-hidden /> : null}
                    {label}
                </CardDescription>
                <CardTitle className="text-3xl font-semibold tracking-tight">
                    {value}
                </CardTitle>
                {description ? (
                    <CardDescription>{description}</CardDescription>
                ) : null}
            </CardHeader>
        </Card>
    );
}
