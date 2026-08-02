import { usePage } from '@inertiajs/react';
import NoteController from '@/actions/App/Http/Controllers/Notes/NoteController';
import Heading from '@/components/heading';
import NoteForm from '@/components/notes/note-form';
import Seo from '@/components/seo';
import { create, index as notesIndex } from '@/routes/notes';

/**
 * REFERENCE MODULE — Notes create. Delete the notes feature when unused.
 */
export default function NotesCreate() {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;

    return (
        <>
            <Seo title="New note" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="New note"
                    description="Title plus a real markdown editor — not a bare textarea."
                />

                <NoteForm
                    form={
                        teamSlug
                            ? NoteController.store.form(teamSlug)
                            : { action: '#', method: 'post' as const }
                    }
                    testId="note-create-form"
                    submitLabel="Create note"
                    defaultBody="## New note\n\nStart writing in **markdown**."
                    cancelHref={teamSlug ? notesIndex(teamSlug).url : null}
                    autoFocusTitle
                />
            </div>
        </>
    );
}

NotesCreate.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Notes',
            href: props.currentTeam ? notesIndex(props.currentTeam.slug) : '/',
        },
        {
            title: 'New',
            href: props.currentTeam ? create(props.currentTeam.slug) : '/',
        },
    ],
});
