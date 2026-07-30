import { Globe, LayoutGrid, NotebookPen } from 'lucide-react';
import { dashboard, home } from '@/routes';
import { index as notesIndex } from '@/routes/notes';
import type { NavItem } from '@/types';

/**
 * Navigation registry — the single place to add or remove nav entries.
 *
 * `publicNav` is consumed by PublicLayout (desktop header, mobile menu,
 * footer). `appNav` / `appFooterNav` are consumed by the app sidebar.
 */

export const publicNav: NavItem[] = [
    {
        title: 'Home',
        href: home(),
    },
];

/**
 * Main sidebar entries for the authenticated app. Resolved with the current
 * team slug because app URLs are team-scoped.
 */
export function appNav(currentTeamSlug?: string | null): NavItem[] {
    if (!currentTeamSlug) {
        return [];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboard(currentTeamSlug),
            icon: LayoutGrid,
        },
        // REFERENCE MODULE — Notes nav entry. Remove with the Notes feature when unused.
        {
            title: 'Notes',
            href: notesIndex(currentTeamSlug),
            icon: NotebookPen,
        },
    ];
}

/**
 * Sidebar footer entries (rendered as external links by NavFooter).
 */
export const appFooterNav: NavItem[] = [
    {
        title: 'Open website',
        href: home(),
        icon: Globe,
    },
];
