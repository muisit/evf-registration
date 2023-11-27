import { Fencer } from "../../../../../common/api/schemas/fencer";
import { Registration } from "../../../../../common/api/schemas/registration";
import { SideEvent } from "../../../../../common/api/schemas/sideevent";

export interface Team {
    name: string;
    fencers: Fencer[];
    registrations: Registration[];
    sideEvent: SideEvent;
    paidToHod: boolean;
    paidToOrg: boolean;
}

export interface FencerPayment {
    fencer: Fencer;
    registrations: Registration[];
    paidToHod: boolean;
    paidToOrg: boolean;
    payment: string;
}