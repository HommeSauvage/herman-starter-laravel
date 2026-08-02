import ConfirmDialog from '@/components/confirm-dialog';
import { destroy as destroyMember } from '@/routes/teams/members';
import type { Team, TeamMember } from '@/types';

type Props = {
    team: Team;
    member: TeamMember | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function RemoveMemberModal({
    team,
    member,
    open,
    onOpenChange,
}: Props) {
    return (
        <ConfirmDialog
            open={open}
            onOpenChange={onOpenChange}
            title="Remove team member"
            description={
                <>
                    Are you sure you want to remove{' '}
                    <strong>{member?.name}</strong> from this team?
                </>
            }
            confirmLabel="Remove member"
            confirmDataTest="remove-member-confirm"
            href={member ? destroyMember([team.slug, member.id]).url : null}
        />
    );
}
