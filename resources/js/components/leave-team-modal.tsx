import ConfirmDialog from '@/components/confirm-dialog';
import { leave as leaveTeamAction } from '@/routes/teams';
import type { Team } from '@/types';

type Props = {
    team: Team | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function LeaveTeamModal({ team, open, onOpenChange }: Props) {
    return (
        <ConfirmDialog
            open={open}
            onOpenChange={onOpenChange}
            title="Leave team"
            description={
                <>
                    Are you sure you want to leave <strong>{team?.name}</strong>
                    ?
                </>
            }
            confirmLabel="Leave team"
            confirmDataTest="leave-team-confirm"
            href={team ? leaveTeamAction(team.slug).url : null}
        />
    );
}
