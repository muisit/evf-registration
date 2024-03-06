export interface Code {
    original: string;
    baseFunction?: number;
    addFunction?: number;
    id1?: number;
    id2?: number;
    validation?: number;
    payload?: string;

    scannedTime?:string;
    data: number;
}

export interface CodeUser {
    id: number;
    eventId: number;
    fencerId: number;
    badge: string;
    type: string;
}