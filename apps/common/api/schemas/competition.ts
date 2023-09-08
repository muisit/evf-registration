export interface Competition {
    id: number;
    categoryId: number;
    weaponId: number;
    starts: string;
    weaponsCheck: string;

    // front-end data
    category: object|null;
    weapon: object|null;
}
