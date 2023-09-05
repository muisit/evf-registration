import type { CategorySchema } from './category';
import type { WeaponSchema } from './weapon';
import type { RoleSchema } from './role';
import type { CountrySchema } from './country';

export interface BasicDataSchema {
    categories: Array<CategorySchema>,
    weapons: Array<WeaponSchema>,
    roles: Array<RoleSchema>,
    countries: Array<CountrySchema>,
};