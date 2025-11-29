import { dashboard } from "@/routes";
import { NavItem } from "@/types";
import { LayoutGridIcon, FactoryIcon, ZapIcon, TruckIcon, UsersIcon, SettingsIcon } from "lucide-react";

export const menus: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGridIcon,
    },
    {
        title: 'Scope 1',
        href: '/scope-1',
        icon: FactoryIcon,
    },
    {
        title: 'Scope 2',
        href: '/scope-2',
        icon: ZapIcon,
    },
    {
        title: 'Scope 3',
        href: '/scope-3',
        icon: TruckIcon,
    },
    {
        title: 'Stakeholders',
        href: '/stakeholders',
        icon: UsersIcon,
    },
    {
        title: 'Konfigurasi',
        href: '/konfigurasi',
        icon: SettingsIcon,
    },
]
