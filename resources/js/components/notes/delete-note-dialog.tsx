import { Form, usePage } from '@inertiajs/react';
import NoteController from '@/actions/App/Http/Controllers/Notes/NoteController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

/**
 * REFERENCE MODULE — delete with Notes when unused.
 */
export default function DeleteNoteDialog({
    noteId,
    title,
}: {
    noteId: number;
    title: string;
}) {
    const teamSlug = usePage().props.currentTeam?.slug;

    if (!teamSlug) {
        return null;
    }

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="destructive" data-test="delete-note-button">
                    Delete
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete this note?</DialogTitle>
                <DialogDescription>
                    “{title}” will be permanently deleted. This cannot be
                    undone.
                </DialogDescription>
                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Form {...NoteController.destroy.form([teamSlug, noteId])}>
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                                data-test="confirm-delete-note-button"
                            >
                                Delete note
                            </Button>
                        )}
                    </Form>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
