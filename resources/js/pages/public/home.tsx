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
import type { FaqItem } from '@/components/sections/faq';
import Faq from '@/components/sections/faq';
import type { Feature } from '@/components/sections/feature-grid';
import FeatureGrid from '@/components/sections/feature-grid';
import type { FeatureRow } from '@/components/sections/feature-rows';
import FeatureRows from '@/components/sections/feature-rows';
import Hero from '@/components/sections/hero';
import type { PricingTier } from '@/components/sections/pricing-table';
import PricingTable from '@/components/sections/pricing-table';
import type { Stat } from '@/components/sections/stats-band';
import StatsBand from '@/components/sections/stats-band';
import type { Testimonial } from '@/components/sections/testimonial-band';
import TestimonialBand from '@/components/sections/testimonial-band';
import Seo from '@/components/seo';
import { dashboard, login, register } from '@/routes';

/**
 * Public home page — the reference composition of the sections library.
 * Every block below is typed and token-only; copy is brand-neutral, driven by
 * the shared `name` prop.
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

    const features: Feature[] = [
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
                'Registration, email verification, password resets, and two-factor are ready on day one.',
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
    ];

    const featureRows: FeatureRow[] = [
        {
            eyebrow: 'Start here',
            title: 'Copy a working feature, not a blank file',
            description:
                'Notes and Posts are complete, tested modules: model, policy, requests, routes, pages, empty states and tests. A new feature starts as a rename of one of them, so the codebase stays one codebase.',
        },
        {
            eyebrow: 'Then trim',
            title: 'Drop what the product does not need',
            description:
                'One command removes an optional module whole — files, routes, tests, docs and dependencies. Hand-deleting leaves residue, and the module gate fails the suite until it is resolved.',
        },
        {
            eyebrow: 'And verify',
            title: 'Let the gate decide when it is done',
            description:
                'A single command runs linting, formatting, TypeScript, dead-code analysis, static analysis and the test suite. Green is the definition of done — not a second opinion.',
        },
    ];

    const stats: Stat[] = [
        { value: '100+', label: 'Passing Pest tests' },
        { value: '40+', label: 'UI building blocks' },
        { value: '3', label: 'Optional modules' },
        { value: '1', label: 'Command to verify it all' },
    ];

    const pricingTiers: PricingTier[] = [
        {
            name: 'Starter',
            price: '$0',
            period: '/month',
            description:
                'This repository, unmodified: teams, auth, and the gates, ready for a first feature.',
            features: [
                'Teams, roles and invitations',
                'Reference modules to copy from',
                'One-command quality gate',
            ],
            cta: { label: 'Get started', href: register() },
        },
        {
            name: 'Shipped',
            price: '$29',
            period: '/month',
            description:
                'What most products end up needing: a public site next to the app, and a deploy that survives a restart.',
            features: [
                'Everything in Starter',
                'Public pages built from the sections library',
                'Docker + SQLite replication contract',
            ],
            highlighted: true,
            badge: 'Most common',
            cta: {
                label: signedIn ? 'Open dashboard' : 'Log in',
                href: dashboardUrl ?? login(),
            },
        },
    ];

    const testimonials: Testimonial[] = [
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
    ];

    const faqs: FaqItem[] = [
        {
            question: `What is ${name}?`,
            answer: 'A Laravel starter kit with multi-tenant teams, Fortify authentication, and an Inertia + React + Tailwind frontend. It exists so new products start from working conventions instead of a blank folder.',
        },
        {
            question: 'What are the reference modules?',
            answer: 'Notes (team-scoped CRUD) and Posts (public content) are complete, tested examples you can copy for real features. They are optional like everything the starter adds beyond auth and teams: `composer run chisel` removes an unwanted module whole, and `composer run modules` proves nothing was left half-deleted.',
        },
        {
            question: 'How do I make it look like my brand?',
            answer: 'Edit the design token values in resources/css/app.css and the font in vite.config.ts. Components only read tokens, so nothing else needs to change.',
        },
        {
            question: 'Can I remove what I do not need?',
            answer: 'Yes, and you should. Optional modules are removed with one command rather than a checklist, and the suite fails if a removal leaves routes, tests or docs behind.',
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

            <FeatureGrid columns={3} features={features} />

            <FeatureRows rows={featureRows} />

            <StatsBand stats={stats} />

            <PricingTable tiers={pricingTiers} />

            <TestimonialBand testimonials={testimonials} />

            <Faq items={faqs} />

            <CtaBand
                title="Start building today"
                lede={`Create an account and explore ${name} — the dashboard, teams, and reference modules are waiting.`}
                actions={[primaryAction]}
            />
        </>
    );
}
