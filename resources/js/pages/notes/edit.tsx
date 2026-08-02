import { usePage } from '@inertiajs/react';
import NoteController from '@/actions/App/Http/Controllers/Notes/NoteController';
import Heading from '@/components/heading';
import { noteBreadcrumbs } from '@/components/notes/note-breadcrumbs';
import NoteForm from '@/components/notes/note-form';
import Seo from '@/components/seo';
import { edit, show } from '@/routes/notes';

type Note = {
    id: number;
    title: string;
    body: string;
};

/**
 * REFERENCE MODULE — Notes edit. Delete the notes feature when unused.
 */
export default function NotesEdit({ note }: { note: Note }) {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;

    return (
        <>
            <Seo title={`Edit ${note.title}`} />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Edit note"
                    description="Update title and markdown body."
                />

                <NoteForm
                    form={
                        teamSlug
                            ? NoteController.update.form([teamSlug, note.id])
                            : { action: '#', method: 'post' as const }
                    }
                    testId="note-edit-form"
                    submitLabel="Save changes"
                    defaultTitle={note.title}
                    defaultBody={note.body}
                    cancelHref={teamSlug ? show([teamSlug, note.id]).url : null}
                />
            </div>
        </>
    );
}

NotesEdit.layout = (props: {
    currentTeam?: { slug: string } | null;
    note?: Note;
}) => ({
    breadcrumbs: [
        ...noteBreadcrumbs(props),
        {
            title: 'Edit',
            href:
                props.currentTeam && props.note
                    ? edit([props.currentTeam.slug, props.note.id])
                    : '/',
        },
    ],
});
