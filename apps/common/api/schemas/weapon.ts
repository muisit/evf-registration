export interface WeaponSchema {
    id: number|null;
    name: string|null;
    abbr: string|null;
    gender: string|null;
};

export interface WeaponById {
    [key:string]: WeaponSchema;
}