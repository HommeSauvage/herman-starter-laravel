import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
// import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { appFooterNav, appNav } from '@/lib/nav';
import { dashboard } from '@/routes';

export function AppSidebar() {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug ?? null;
    const dashboardUrl = teamSlug ? dashboard(teamSlug) : '/';

    const mainNavItems = appNav(teamSlug);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardUrl} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    {/* Uncomment to enable teams in the sidebar
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem> */}
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={appFooterNav} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
