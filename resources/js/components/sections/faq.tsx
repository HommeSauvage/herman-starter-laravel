import { CircleHelp } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Section from '@/components/sections/section';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';

export type FaqItem = {
    question: string;
    answer: string;
};

type Props = {
    items: FaqItem[];
    className?: string;
};

/** Frequently asked questions as an accordion. */
export default function Faq({ items, className }: Props) {
    if (items.length === 0) {
        return (
            <Section className={className}>
                <EmptyState
                    icon={CircleHelp}
                    title="No questions yet"
                    description="Add questions and answers to show them here."
                />
            </Section>
        );
    }

    return (
        <Section className={className} containerClassName="max-w-3xl">
            <Accordion type="single" collapsible className="w-full">
                {items.map((item, index) => (
                    <AccordionItem key={item.question} value={`item-${index}`}>
                        <AccordionTrigger className="text-left">
                            {item.question}
                        </AccordionTrigger>
                        <AccordionContent className="text-muted-foreground">
                            {item.answer}
                        </AccordionContent>
                    </AccordionItem>
                ))}
            </Accordion>
        </Section>
    );
}
