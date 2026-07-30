import { Link } from '@inertiajs/react';
import { LayoutGrid } from 'lucide-react';
import type { ReactNode } from 'react';
import EmptyState from '@/components/empty-state';
import MediaImage from '@/components/media-image';
import Section from '@/components/sections/section';
import type { SectionHref } from '@/components/sections/section';

export type CardGridItem = {
    title: string;
    excerpt?: string;
    href?: SectionHref;
    image?: string | null;
    /** Small line above the title (date, author, tag…). */
    meta?: ReactNode;
    /** Footer slot at the bottom of the card. */
    footer?: ReactNode;
};

type Props = {
    items: CardGridItem[];
    columns?: 2 | 3;
    imageRatio?: 'square' | 'video' | 'wide';
    className?: string;
};

/** Media + title + excerpt cards (blog grids, product grids, galleries). */
export default function CardGrid({
    items,
    columns = 3,
    imageRatio = 'video',
    className,
}: Props) {
    if (items.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={LayoutGrid}
                    title="Nothing here yet"
                    description="Add items to this grid to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className}>
            <div
                className={
                    columns === 2
                        ? 'grid gap-6 sm:grid-cols-2'
                        : 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'
                }
            >
                {items.map((item) => {
                    const card = (
                        <>
                            <MediaImage
                                src={item.image}
                                alt={item.title}
                                ratio={imageRatio}
                            />
                            <div className="flex flex-1 flex-col gap-2 p-5">
                                {item.meta ? (
                                    <div className="text-xs text-muted-foreground">
                                        {item.meta}
                                    </div>
                                ) : null}
                                <h3 className="font-semibold text-balance">
                                    {item.title}
                                </h3>
                                {item.excerpt ? (
                                    <p className="line-clamp-3 text-sm text-muted-foreground">
                                        {item.excerpt}
                                    </p>
                                ) : null}
                                {item.footer ? (
                                    <div className="mt-auto pt-2">
                                        {item.footer}
                                    </div>
                                ) : null}
                            </div>
                        </>
                    );

                    return item.href ? (
                        <Link
                            key={item.title}
                            href={item.href}
                            className="group flex flex-col overflow-hidden rounded-xl border bg-card transition-colors hover:bg-accent/50"
                        >
                            {card}
                        </Link>
                    ) : (
                        <div
                            key={item.title}
                            className="flex flex-col overflow-hidden rounded-xl border bg-card"
                        >
                            {card}
                        </div>
                    );
                })}
            </div>
        </Section>
    );
}
