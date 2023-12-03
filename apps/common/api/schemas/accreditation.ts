export interface Accreditation {
    id: number;
    eventId: number;
    templateId: number;
    fencerId: number;
    template?: string;
    hasFile?: string;
}

export interface AccreditationList extends Array<Accreditation>{};
