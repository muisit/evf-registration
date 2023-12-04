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

export function defaultCategory(): CategorySchema
{
    return {
        id: 0,
        name: 'Cat',
        abbr: 'C',
        type: 'I',
        value: 1
    };
}