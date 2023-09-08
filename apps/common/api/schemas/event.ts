import type { Competition } from './competition';
import type { Bank } from './bank';
import type { EventType } from './eventtype';
import type { SideEvent } from './sideevent';

export interface Event {
    id: number|null;
    name: string|null;
    opens: string|null;
    reg_open: string|null;
    reg_close: string|null;
    year: number|null;
    duration: number|null;
    email: string|null;
    web: string|null;
    location: string|null;
    countryId: number|null;
    type: EventType|null;
    bank: Bank|null;
    payments: string|null;
    feed: string|null;
    config: string|object|null;
    sideEvents: Array<SideEvent>|null;
    competitions: Array<Competition>|null;
}

