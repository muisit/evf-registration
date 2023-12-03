export interface CategoryById {
    [key:string]: CategorySchema;
}

export interface CategorySchema {
    id: number|null;
    name: string|null;
    abbr: string|null;
    type: string|null;
    value: number|null;
};