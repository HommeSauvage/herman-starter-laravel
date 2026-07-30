import { MessageSquareQuote } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';

export type Testimonial = {
    quote: string;
    name: string;
    role?: string;
};

type Props = {
    testimonials: Testimonial[];
    className?: string;
};

/** Quotes with attribution in a responsive card grid. */
export default function TestimonialBand({ testimonials, className }: Props) {
    if (testimonials.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={MessageSquareQuote}
                    title="No testimonials yet"
                    description="Add testimonials to this band to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className}>
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {testimonials.map((testimonial) => (
                    <figure
                        key={testimonial.name}
                        className="flex flex-col justify-between gap-6 rounded-xl border bg-card p-6"
                    >
                        <blockquote className="text-pretty text-muted-foreground">
                            “{testimonial.quote}”
                        </blockquote>
                        <figcaption>
                            <p className="font-semibold">{testimonial.name}</p>
                            {testimonial.role ? (
                                <p className="text-sm text-muted-foreground">
                                    {testimonial.role}
                                </p>
                            ) : null}
                        </figcaption>
                    </figure>
                ))}
            </div>
        </Section>
    );
}
