import type { CategorySchema } from "./category";
import type { WeaponSchema } from "./weapon";

export interface CompetitionById {
    [key:string]: Competition;
}

export interface Competition {
    id: number;
    categoryId: number;
    weaponId: number;
    starts: string;
    weaponsCheck: string;

    // front-end data
    category: CategorySchema|null;
    weapon: WeaponSchema|null;
}

export function defaultCompetition(): Competition
{
    return {
        id: 0,
        categoryId: 0,
        weaponId: 0,
        starts: '',
        weaponsCheck: '',
        category: null,
        weapon: null
    };
}