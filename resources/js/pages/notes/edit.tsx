import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import NoteController from '@/actions/App/Http/Controllers/Notes/NoteController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import MarkdownEditor from '@/components/notes/markdown-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit, index as notesIndex, show } from '@/routes/notes';

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
    const [body, setBody] = useState(note.body);

    return (
        <>
            <Head title={`Edit ${note.title}`} />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Edit note"
                    description="Update title and markdown body."
                />

                <Form
                    {...(teamSlug
                        ? NoteController.update.form([teamSlug, note.id])
                        : { action: '#', method: 'post' as const })}
                    className="space-y-6"
                    data-test="note-edit-form"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
                                    defaultValue={note.title}
                                    data-test="note-title-input"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="body">Body</Label>
                                <MarkdownEditor
                                    id="body"
                                    value={body}
                                    onChange={setBody}
                                />
                                <InputError message={errors.body} />
                            </div>

                            <div className="flex items-center gap-2">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="note-save-button"
                                >
                                    Save changes
                                </Button>
                                {teamSlug ? (
                                    <Button variant="ghost" asChild>
                                        <Link
                                            href={show([teamSlug, note.id])}
                                        >
                                            Cancel
                                        </Link>
                                    </Button>
                                ) : null}
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

NotesEdit.layout = (props: {
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
        {
            title: 'Edit',
            href:
                props.currentTeam && props.note
                    ? edit([props.currentTeam.slug, props.note.id])
                    : '/',
        },
    ],
});
