import { router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Plus, Users } from 'lucide-react';
import CreateTeamModal from '@/components/create-team-modal';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useIsMobile } from '@/hooks/use-mobile';
import { switchMethod } from '@/routes/teams';
import type { Team } from '@/types';

/** Sidebar team switcher — one entry point for switching or creating a team. */
export function TeamSwitcher() {
    const page = usePage();
    const isMobile = useIsMobile();
    const currentTeam = page.props.currentTeam;
    const teams = page.props.teams ?? [];

    const switchTeam = (team: Team) => {
        const previousTeamSlug = currentTeam?.slug;

        router.visit(switchMethod(team.slug), {
            onFinish: () => {
                if (!previousTeamSlug || typeof window === 'undefined') {
                    router.reload();

                    return;
                }

                const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;
                const segment = `/${previousTeamSlug}`;

                if (currentUrl.includes(segment)) {
                    router.visit(currentUrl.replace(segment, `/${team.slug}`), {
                        replace: true,
                    });

                    return;
                }

                router.reload();
            },
        });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    data-test="team-switcher-trigger"
                    className="w-full justify-start px-2 has-[>svg]:px-2 data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                >
                    <Users className="hidden size-4 shrink-0 group-data-[collapsible=icon]:block" />
                    <div className="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                        <span className="truncate font-semibold">
                            {currentTeam?.name ?? 'Select team'}
                        </span>
                    </div>
                    <ChevronsUpDown className="ml-auto group-data-[collapsible=icon]:hidden" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                side={isMobile ? 'bottom' : 'right'}
                align="start"
                sideOffset={4}
            >
                <DropdownMenuLabel className="text-xs text-muted-foreground">
                    Teams
                </DropdownMenuLabel>
                {teams.map((team) => (
                    <DropdownMenuItem
                        key={team.id}
                        data-test="team-switcher-item"
                        className="cursor-pointer gap-2 p-2"
                        onSelect={() => switchTeam(team)}
                    >
                        {team.name}
                        {currentTeam?.id === team.id && (
                            <Check className="ml-auto h-4 w-4" />
                        )}
                    </DropdownMenuItem>
                ))}
                <DropdownMenuSeparator />
                <CreateTeamModal>
                    <DropdownMenuItem
                        data-test="team-switcher-new-team"
                        className="cursor-pointer gap-2 p-2"
                        onSelect={(event) => event.preventDefault()}
                    >
                        <Plus className="h-4 w-4" />
                        <span className="text-muted-foreground">New team</span>
                    </DropdownMenuItem>
                </CreateTeamModal>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
