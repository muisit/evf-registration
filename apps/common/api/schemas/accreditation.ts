import { format_date, parse_date } from "../../functions";
import { useAuthStore } from "../../stores/auth";
import { CountrySchema } from "./country";
import { Registration } from "./registration";

export interface Accreditation {
    id: number;
    eventId: number;
    templateId: number;
    fencerId: number;
    template?: string;
    hasFile?: string;
}

export interface AccreditationList extends Array<Accreditation>{};
