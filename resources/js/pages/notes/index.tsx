import { Link, usePage } from '@inertiajs/react';
import { NotebookPen, Plus } from 'lucide-react';
import EmptyState from '@/components/empty-state';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import SearchInput from '@/components/search-input';
import Seo from '@/components/seo';
import { Button } from '@/components/ui/button';
import { index as notesIndex, create, show } from '@/routes/notes';
import type { PaginatedData } from '@/types';

type NoteListItem = {
    id: number;
    title: string;
    excerpt: string;
    author: string | null;
    updated_at: string | null;
};

type Props = {
    notes: PaginatedData<NoteListItem>;
    filters: {
        search: string;
    };
};

/**
 * REFERENCE MODULE — Notes list. Delete the notes feature when unused.
 */
export default function NotesIndex({ notes, filters }: Props) {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;

    return (
        <>
            <Seo title="Notes" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title="Notes"
                        description="Capture ideas in markdown. List → open → edit."
                    />

                    {teamSlug ? (
                        <Button asChild data-test="notes-create-button">
                            <Link href={create(teamSlug)}>
                                <Plus /> New note
                            </Link>
                        </Button>
                    ) : null}
                </div>

                <SearchInput
                    action={teamSlug ? notesIndex.url(teamSlug) : '#'}
                    defaultValue={filters.search}
                    placeholder="Search notes…"
                    testId="notes-search-input"
                />

                {notes.data.length === 0 ? (
                    <EmptyState
                        icon={NotebookPen}
                        title={
                            filters.search
                                ? 'No notes match your search'
                                : 'No notes yet'
                        }
                        description={
                            filters.search
                                ? 'Try a different search term.'
                                : 'Write your first note to see list → detail flow.'
                        }
                        action={
                            !filters.search && teamSlug ? (
                                <Button asChild>
                                    <Link href={create(teamSlug)}>
                                        <Plus /> Create a note
                                    </Link>
                                </Button>
                            ) : null
                        }
                        testId="notes-empty-state"
                    />
                ) : (
                    <ul
                        className="divide-y rounded-xl border"
                        data-test="notes-list"
                    >
                        {notes.data.map((note) => (
                            <li key={note.id}>
                                <Link
                                    href={
                                        teamSlug
                                            ? show([teamSlug, note.id])
                                            : '#'
                                    }
                                    className="block px-4 py-4 transition-colors hover:bg-muted/50"
                                    data-test="note-row"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="min-w-0 space-y-1">
                                            <p className="truncate font-medium">
                                                {note.title}
                                            </p>
                                            <p className="line-clamp-2 text-sm text-muted-foreground">
                                                {note.excerpt}
                                            </p>
                                        </div>
                                        <div className="shrink-0 text-right text-xs text-muted-foreground">
                                            {note.author ? (
                                                <p>{note.author}</p>
                                            ) : null}
                                            {note.updated_at ? (
                                                <p>
                                                    {new Date(
                                                        note.updated_at,
                                                    ).toLocaleDateString()}
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <Pagination pagination={notes} />
            </div>
        </>
    );
}

NotesIndex.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Notes',
            href: props.currentTeam ? notesIndex(props.currentTeam.slug) : '/',
        },
    ],
});
