import type { CountrySchema } from "./country";

export interface MeSchema {
    status: boolean;
    username: string;
    token: string|null;
    credentials: Array<string>|null;
    countryId: number|null;

    // front end data
    country?: CountrySchema;
};