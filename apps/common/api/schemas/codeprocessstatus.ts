import type { Fencer } from "./fencer";
import type { Accreditation } from './accreditation';

export interface CodeProcessStatus {
    eventId: number;
    status: string;
    action: string;
    message?: string;
    fencer?: Fencer;
    accreditations ?: Accreditation[];
};