import type { Fencer } from './fencer';
import type { Registration } from './registration';
import type { AccreditationDocument } from './accreditationdocument';

export interface Registrations {
    registrations: Array<Registration>|null;
    fencers: Array<Fencer>|null;
    documents: Array<AccreditationDocument>|null;
}
