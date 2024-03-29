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
    countryId?: number|null;

    // front-end data
    errors?: string[];
    role?: string;
    saveState?: string;
}

export function defaultRegistration():Registration
{
    return {
        id: 0,
        fencerId: null,
        roleId: null,
        sideEventId: null,
        dateTime: null,
        payment: null,
        paid: null,
        paidHod: null,
        state: null,
        team: null
    };
}