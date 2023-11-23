export interface Registration {
    id: number|null;
    fencerId: number|null;
    roleId: number|null;
    sideEventId: number|null;
    dateTime: string|null;
    payment: string|null;
    paid: string|null;
    paidHod: string|null;
    state: string|null;
    team: string|null;

    // front-end data
    errors?: string[];
}
