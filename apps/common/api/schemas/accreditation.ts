export interface Accreditation {
    id: number;
    eventId: number;
    templateId: number;
    fencerId: number;
    template?: string;
    hasFile?: string;
}

export interface AccreditationList extends Array<Accreditation>{};

export interface AccreditationDocument {
    id: number;
    size: string;
    available: string;   
}

export interface AccreditationOverviewLine {
    type: string;
    id: number;
    counts: number[];
    documents: AccreditationDocument[];
}
