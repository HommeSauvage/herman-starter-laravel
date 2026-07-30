import { Link } from '@inertiajs/react';
import { show } from '@/routes/notes';

export type NoteListItem = {
    id: number;
    title: string;
    excerpt: string;
    author: string | null;
    updated_at: string | null;
};

/**
 * REFERENCE MODULE — shared notes list rows (notes index + dashboard).
 * Delete with the Notes feature when unused.
 */
export default function NoteList({
    notes,
    teamSlug,
}: {
    notes: NoteListItem[];
    teamSlug?: string | null;
}) {
    return (
        <ul className="divide-y rounded-xl border" data-test="notes-list">
            {notes.map((note) => (
                <li key={note.id}>
                    <Link
                        href={teamSlug ? show([teamSlug, note.id]) : '#'}
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
                                {note.author ? <p>{note.author}</p> : null}
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
    );
}
