import { format_date, parse_date } from "../../functions";
import { useAuthStore } from "../../stores/auth";
import type { CountrySchema } from "./country";
import type { Registration } from "./registration";

export interface Fencer {
    id: number;
    firstName: string;
    lastName: string;
    countryId: number|null;
    gender?: string;
    dateOfBirth: string|null;
    photoStatus?: string|null;

    // frontend data
    fullName?: string;
    country?: CountrySchema;
    category?: string;
    categoryNum?: number;
    birthYear?: string;
    registrations?: Array<Registration>;
    fullGender?: string;
}

export interface FencerList extends Array<Fencer>{};
export interface FencerById {
    [index: string]: Fencer;
}

export function defaultFencer()
{
    const auth = useAuthStore();

    var fencer:Fencer = {
        id: 0,
        firstName: '',
        lastName: '',
        countryId: auth.countryId || 0,
        gender: 'M',
        dateOfBirth: format_date(parse_date().subtract(40, 'years')),
        photoStatus: null,
        fullName: '',
        birthYear: parse_date().subtract(40, 'years').format('YYYY'),
        registrations: [],
        fullGender: 'M',
        country: undefined,
        category: '1',
        categoryNum: 1,
    };
    return fencer;
}