export interface AccreditationDocument {
    id ?: number;
    badge: string;
    fencerId: number;
    card ?: number;
    document ?: number;
    payload ?: any;
    entered ?: string;
}