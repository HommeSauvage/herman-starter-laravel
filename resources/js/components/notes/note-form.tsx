import { Form, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import MarkdownEditor from '@/components/notes/markdown-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type NoteFormProps = {
    form: { action: string; method: 'post' | 'get' };
    testId: string;
    submitLabel: string;
    defaultTitle?: string;
    defaultBody: string;
    cancelHref: string | null;
    autoFocusTitle?: boolean;
};

/**
 * Shared create/edit form for the Notes reference module. The pages stay
 * thin: Seo, heading, and this form.
 */
export default function NoteForm({
    form,
    testId,
    submitLabel,
    defaultTitle,
    defaultBody,
    cancelHref,
    autoFocusTitle = false,
}: NoteFormProps) {
    const [body, setBody] = useState(defaultBody);

    return (
        <Form {...form} className="space-y-6" data-test={testId}>
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            required
                            autoFocus={autoFocusTitle}
                            defaultValue={defaultTitle}
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
                            {submitLabel}
                        </Button>
                        {cancelHref ? (
                            <Button variant="ghost" asChild>
                                <Link href={cancelHref}>Cancel</Link>
                            </Button>
                        ) : null}
                    </div>
                </>
            )}
        </Form>
    );
}
