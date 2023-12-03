export interface CountryById {
    [key:string]: CountrySchema;
}

export interface CountrySchema {
    id: number;
    name: string;
    abbr: string;
    path: string|null;
};