export interface AccreditationDocument {
    id ?: number;
    badge?: string;
    fencerId?: number;
    card ?: number;
    document ?: number;
    status ?: string;
    payload ?: any;
    dates?: Array<string>;
    checkin ?: string|null;
    processStart ?: string|null;
    processEnd ?: string|null;
    checkout ?: string|null;

    name ?: string;
    countryId ?: number;
}