import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import NoteController from '@/actions/App/Http/Controllers/Notes/NoteController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import MarkdownEditor from '@/components/notes/markdown-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index as notesIndex } from '@/routes/notes';

/**
 * REFERENCE MODULE — Notes create. Delete the notes feature when unused.
 */
export default function NotesCreate() {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;
    const [body, setBody] = useState(
        '## New note\n\nStart writing in **markdown**.',
    );

    return (
        <>
            <Head title="New note" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="New note"
                    description="Title plus a real markdown editor — not a bare textarea."
                />

                <Form
                    {...(teamSlug
                        ? NoteController.store.form(teamSlug)
                        : { action: '#', method: 'post' as const })}
                    className="space-y-6"
                    data-test="note-create-form"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
                                    autoFocus
                                    placeholder="Give this note a clear title"
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
                                    Create note
                                </Button>
                                {teamSlug ? (
                                    <Button variant="ghost" asChild>
                                        <Link href={notesIndex(teamSlug)}>
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

NotesCreate.layout = (props: {
    currentTeam?: { slug: string } | null;
}) => ({
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
