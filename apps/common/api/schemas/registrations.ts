import type { Fencer } from './fencer';
import type { Registration } from './registration';

export interface Registrations {
    registrations: Array<Registration>|null;
    fencers: Array<Fencer>|null;
}
