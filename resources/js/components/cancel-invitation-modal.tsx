import ConfirmDialog from '@/components/confirm-dialog';
import { destroy as destroyInvitation } from '@/routes/teams/invitations';
import type { Team, TeamInvitation } from '@/types';

type Props = {
    team: Team;
    invitation: TeamInvitation | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function CancelInvitationModal({
    team,
    invitation,
    open,
    onOpenChange,
}: Props) {
    return (
        <ConfirmDialog
            open={open}
            onOpenChange={onOpenChange}
            title="Cancel invitation"
            description={
                <>
                    Are you sure you want to cancel the invitation for{' '}
                    <strong>{invitation?.email}</strong>?
                </>
            }
            confirmLabel="Cancel invitation"
            cancelLabel="Keep invitation"
            confirmDataTest="cancel-invitation-confirm"
            href={
                invitation
                    ? destroyInvitation([team.slug, invitation.code]).url
                    : null
            }
        />
    );
}
