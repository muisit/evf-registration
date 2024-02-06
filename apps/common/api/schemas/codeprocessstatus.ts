import type { Fencer } from "./fencer";

export interface CodeProcessStatus {
    eventId: number;
    status: string;
    action: string;
    message?: string;
    fencer?: Fencer;
};