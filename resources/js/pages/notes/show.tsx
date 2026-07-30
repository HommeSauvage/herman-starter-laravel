import { Link, usePage } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import MarkdownBody from '@/components/markdown-body';
import DeleteNoteDialog from '@/components/notes/delete-note-dialog';
import Seo from '@/components/seo';
import { Button } from '@/components/ui/button';
import { edit, index as notesIndex, show } from '@/routes/notes';

type Note = {
    id: number;
    title: string;
    body: string;
    author: string | null;
    created_at: string | null;
    updated_at: string | null;
};

/**
 * REFERENCE MODULE — Notes detail. Delete the notes feature when unused.
 */
export default function NotesShow({ note }: { note: Note }) {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;

    return (
        <>
            <Seo title={note.title} />

            <article className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-semibold tracking-tight">
                            {note.title}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {note.author ? `${note.author} · ` : null}
                            {note.updated_at
                                ? `Updated ${new Date(note.updated_at).toLocaleString()}`
                                : null}
                        </p>
                    </div>

                    <div className="flex gap-2">
                        {teamSlug ? (
                            <Button variant="outline" asChild>
                                <Link href={edit([teamSlug, note.id])}>
                                    <Pencil /> Edit
                                </Link>
                            </Button>
                        ) : null}
                        <DeleteNoteDialog noteId={note.id} title={note.title} />
                    </div>
                </div>

                <div className="rounded-xl border bg-card p-6">
                    <MarkdownBody content={note.body} />
                </div>

                {teamSlug ? (
                    <div>
                        <Button variant="ghost" asChild>
                            <Link href={notesIndex(teamSlug)}>
                                ← Back to notes
                            </Link>
                        </Button>
                    </div>
                ) : null}
            </article>
        </>
    );
}

NotesShow.layout = (props: {
    currentTeam?: { slug: string } | null;
    note?: Note;
}) => ({
    breadcrumbs: [
        {
            title: 'Notes',
            href: props.currentTeam ? notesIndex(props.currentTeam.slug) : '/',
        },
        {
            title: props.note?.title ?? 'Note',
            href:
                props.currentTeam && props.note
                    ? show([props.currentTeam.slug, props.note.id])
                    : '/',
        },
    ],
});
