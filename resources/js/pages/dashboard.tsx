import { Link, usePage } from '@inertiajs/react';
import { MailPlus, NotebookPen, Plus, Users } from 'lucide-react';
import { useState } from 'react';
import EmptyState from '@/components/empty-state';
import NoteList from '@/components/notes/note-list';
import type { NoteListItem } from '@/components/notes/note-list';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import Seo from '@/components/seo';
import StatCard from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
// REFERENCE MODULE — Notes dashboard widgets. Remove with the Notes feature.
import { create, index as notesIndex } from '@/routes/notes';
import { edit as editTeam } from '@/routes/teams';
import type { DashboardInvitation } from '@/types';

type Props = {
    pendingInvitations?: DashboardInvitation[];
    stats: {
        members: number;
        notes: number;
        pendingInvitations: number;
    };
    recentNotes: NoteListItem[];
};

export default function Dashboard({
    pendingInvitations = [],
    stats,
    recentNotes,
}: Props) {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;

    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    return (
        <>
            <Seo title="Dashboard" />
            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Dashboard
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Your team at a glance.
                        </p>
                    </div>
                    {teamSlug ? (
                        <div className="flex flex-wrap gap-2">
                            {/* REFERENCE MODULE — Notes quick action. Remove with the Notes feature. */}
                            <Button asChild>
                                <Link href={create(teamSlug)}>
                                    <Plus /> New note
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={editTeam(teamSlug)}>
                                    <MailPlus /> Invite member
                                </Link>
                            </Button>
                        </div>
                    ) : null}
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <StatCard
                        icon={Users}
                        label="Team members"
                        value={stats.members}
                    />
                    {/* REFERENCE MODULE — Notes stat. Remove with the Notes feature. */}
                    <StatCard
                        icon={NotebookPen}
                        label="Notes"
                        value={stats.notes}
                    />
                    <StatCard
                        icon={MailPlus}
                        label="Pending invitations"
                        value={stats.pendingInvitations}
                    />
                </div>

                {/* REFERENCE MODULE — recent notes section. Remove with the Notes feature. */}
                <div className="flex flex-col gap-3">
                    <div className="flex items-center justify-between">
                        <h2 className="font-semibold">Recent notes</h2>
                        {teamSlug ? (
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={notesIndex(teamSlug)}>
                                    View all
                                </Link>
                            </Button>
                        ) : null}
                    </div>

                    {recentNotes.length === 0 ? (
                        <EmptyState
                            icon={NotebookPen}
                            title="No notes yet"
                            description="Write your first note to see it here."
                            action={
                                teamSlug ? (
                                    <Button asChild>
                                        <Link href={create(teamSlug)}>
                                            <Plus /> Create a note
                                        </Link>
                                    </Button>
                                ) : null
                            }
                        />
                    ) : (
                        <NoteList notes={recentNotes} teamSlug={teamSlug} />
                    )}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
