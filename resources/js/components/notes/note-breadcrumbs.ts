import { index as notesIndex, show } from '@/routes/notes';
import type { BreadcrumbItem } from '@/types';

type Note = {
    id: number;
    title: string;
};

type NoteBreadcrumbProps = {
    currentTeam?: { slug: string } | null;
    note?: Note;
};

/**
 * Shared breadcrumb prefix (Notes → note) for the Notes reference module's
 * detail/edit pages. Edit appends its own trailing entry.
 */
export function noteBreadcrumbs({
    currentTeam,
    note,
}: NoteBreadcrumbProps): BreadcrumbItem[] {
    if (!currentTeam) {
        return [
            { title: 'Notes', href: '/' },
            { title: note?.title ?? 'Note', href: '/' },
        ];
    }

    return [
        { title: 'Notes', href: notesIndex(currentTeam.slug) },
        {
            title: note?.title ?? 'Note',
            href: note ? show([currentTeam.slug, note.id]) : '/',
        },
    ];
}
