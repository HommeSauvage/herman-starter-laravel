import { Form, Head, Link, usePage } from '@inertiajs/react';
import { NotebookPen, Plus, Search } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
            <Head title="Notes" />

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

                <Form
                    action={teamSlug ? notesIndex.url(teamSlug) : '#'}
                    method="get"
                    className="flex max-w-md gap-2"
                >
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            name="search"
                            defaultValue={filters.search}
                            placeholder="Search notes…"
                            className="pl-9"
                            data-test="notes-search-input"
                        />
                    </div>
                    <Button type="submit" variant="secondary">
                        Search
                    </Button>
                </Form>

                {notes.data.length === 0 ? (
                    <div
                        className="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center"
                        data-test="notes-empty-state"
                    >
                        <NotebookPen className="size-10 text-muted-foreground" />
                        <div className="space-y-1">
                            <p className="font-medium">
                                {filters.search
                                    ? 'No notes match your search'
                                    : 'No notes yet'}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {filters.search
                                    ? 'Try a different search term.'
                                    : 'Write your first note to see list → detail flow.'}
                            </p>
                        </div>
                        {!filters.search && teamSlug ? (
                            <Button asChild>
                                <Link href={create(teamSlug)}>
                                    <Plus /> Create a note
                                </Link>
                            </Button>
                        ) : null}
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border" data-test="notes-list">
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

                {notes.last_page > 1 ? (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Page {notes.current_page} of {notes.last_page}
                        </span>
                        <div className="flex gap-2">
                            {notes.prev_page_url ? (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={notes.prev_page_url} preserveScroll>
                                        Previous
                                    </Link>
                                </Button>
                            ) : null}
                            {notes.next_page_url ? (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={notes.next_page_url} preserveScroll>
                                        Next
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </div>
                ) : null}
            </div>
        </>
    );
}

NotesIndex.layout = (props: {
    currentTeam?: { slug: string } | null;
}) => ({
    breadcrumbs: [
        {
            title: 'Notes',
            href: props.currentTeam ? notesIndex(props.currentTeam.slug) : '/',
        },
    ],
});
