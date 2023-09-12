import { CategorySchema } from "./category";
import { WeaponSchema } from "./weapon";

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
