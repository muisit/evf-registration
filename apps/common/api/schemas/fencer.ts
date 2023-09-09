import { CountrySchema } from "./country";
import { Registration } from "./registration";

export interface Fencer {
    id: number|null;
    firstName: string|null;
    lastName: string|null;
    countryId: number|null;
    gender: string|null;
    dateOfBirth: string|null;
    photoStatus: string|null;

    // frontend data
    fullName: string;
    country: CountrySchema;
    category: string;
    categoryNum: number;
    birthYear: string;
    registrations: Array<Registration>;
}
