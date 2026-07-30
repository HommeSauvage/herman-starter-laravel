import { usePage } from '@inertiajs/react';
import {
    LayoutGrid,
    LockKeyhole,
    Palette,
    Rocket,
    Users,
    Workflow,
} from 'lucide-react';
import CtaBand from '@/components/sections/cta-band';
import Faq from '@/components/sections/faq';
import FeatureGrid from '@/components/sections/feature-grid';
import Hero from '@/components/sections/hero';
import StatsBand from '@/components/sections/stats-band';
import TestimonialBand from '@/components/sections/testimonial-band';
import Seo from '@/components/seo';
import { dashboard, login, register } from '@/routes';

/**
 * Public home page — a reference composition of the sections library.
 * All copy is brand-neutral and driven by the shared `name` prop.
 */
export default function Home() {
    const { name, auth, currentTeam } = usePage().props;

    const dashboardUrl = currentTeam ? dashboard(currentTeam.slug) : null;
    const signedIn = auth.user !== null && dashboardUrl !== null;

    const primaryAction = signedIn
        ? { label: 'Open dashboard', href: dashboardUrl }
        : { label: 'Get started', href: register() };
    const heroActions = signedIn
        ? [primaryAction]
        : [
              primaryAction,
              {
                  label: 'Log in',
                  href: login(),
                  variant: 'outline' as const,
              },
          ];

    return (
        <>
            <Seo
                title="Welcome"
                description={`${name} — a Laravel starter with teams, auth, and a polished React UI, ready from the first commit.`}
            />

            <Hero
                eyebrow="Laravel + Inertia + React"
                title={`Ship your next product on ${name}`}
                lede="Teams, authentication, and a polished React UI are already wired up — spend your first commit on what makes your product different."
                actions={heroActions}
            />

            <FeatureGrid
                columns={3}
                features={[
                    {
                        icon: Users,
                        title: 'Teams built in',
                        description:
                            'Personal teams, invitations, and roles ship with the starter — no tenancy detour before your first feature.',
                    },
                    {
                        icon: LockKeyhole,
                        title: 'Modern authentication',
                        description:
                            'Registration, email verification, password resets, two-factor, and passkeys are ready on day one.',
                    },
                    {
                        icon: LayoutGrid,
                        title: 'Composable sections',
                        description:
                            'Heroes, feature grids, pricing, and FAQs are typed building blocks — compose pages instead of hand-rolling layouts.',
                    },
                    {
                        icon: Palette,
                        title: 'Design tokens',
                        description:
                            'Every component reads from one token file, so a full retheme is a handful of variable edits — not a refactor.',
                    },
                    {
                        icon: Workflow,
                        title: 'Typed routes',
                        description:
                            'Wayfinder generates TypeScript helpers for every route, keeping links and forms honest as the backend evolves.',
                    },
                    {
                        icon: Rocket,
                        title: 'Quality gates',
                        description:
                            'Pest tests, static analysis, linting, and formatting run as one command — green before every merge.',
                    },
                ]}
            />

            <StatsBand
                stats={[
                    { value: '100+', label: 'Passing Pest tests' },
                    { value: '40+', label: 'UI building blocks' },
                    { value: '2', label: 'Reference modules' },
                    { value: '1', label: 'Command to verify it all' },
                ]}
            />

            <TestimonialBand
                testimonials={[
                    {
                        quote: 'We skipped two weeks of scaffolding and went straight to the feature that won our first customer.',
                        name: 'Alex Rivera',
                        role: 'Founder, Northwind Labs',
                    },
                    {
                        quote: 'The reference modules taught our whole team the conventions. New pages look right on the first try.',
                        name: 'Priya Shah',
                        role: 'Engineering Lead, Meridian',
                    },
                    {
                        quote: 'Retheming took an afternoon. Change the tokens, rebuild, and every page follows — that is the whole trick.',
                        name: 'Sam Okafor',
                        role: 'Designer, Fieldnotes',
                    },
                ]}
            />

            <Faq
                items={[
                    {
                        question: `What is ${name}?`,
                        answer: 'A Laravel starter kit with multi-tenant teams, Fortify authentication, and an Inertia + React + Tailwind frontend. It exists so new products start from working conventions instead of a blank folder.',
                    },
                    {
                        question: 'What are the reference modules?',
                        answer: 'Notes (team-scoped CRUD) and Posts (public content) are complete, tested examples you can copy for real features — or delete in one pass when you do not need them.',
                    },
                    {
                        question: 'How do I make it look like my brand?',
                        answer: 'Edit the design token values in resources/css/app.css and the font in vite.config.ts. Components only read tokens, so nothing else needs to change.',
                    },
                    {
                        question: 'Can I remove what I do not need?',
                        answer: 'Yes. Reference modules are marked with REFERENCE MODULE comments and come with a deletion guide, so removing them is a checklist, not archaeology.',
                    },
                ]}
            />

            <CtaBand
                title="Start building today"
                lede={`Create an account and explore ${name} — the dashboard, teams, and reference modules are waiting.`}
                actions={[primaryAction]}
            />
        </>
    );
}
