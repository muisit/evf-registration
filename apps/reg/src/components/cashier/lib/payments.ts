import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../../common/api/schemas/registration";
import type { SideEvent } from "../../../../../common/api/schemas/sideevent";

export interface Team {
    name: string;
    fencers: Fencer[];
    registrations: Registration[];
    sideEvent: SideEvent;
    paidToHod: boolean;
    paidToOrg: boolean;
}

export interface StringKeyedTeam {
    [key:string]: Team;
}

export interface FencerPayment {
    fencer: Fencer;
    registrations: Registration[];
    paidToHod: boolean;
    paidToOrg: boolean;
    payment: string;
}

export interface StringKeyedFenderPayment {
    [key:string]: FencerPayment;
}