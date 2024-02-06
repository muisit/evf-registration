import type { Competition } from './competition';
import type { Bank } from './bank';
import type { EventType } from './eventtype';
import type { SideEvent } from './sideevent';
import type { TemplateSchema } from './template';
import { StringKeyedStringList } from '../../types';

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
    config: any;
    sideEvents: Array<SideEvent>|null;
    competitions: Array<Competition>|null;
    templates?: Array<TemplateSchema>;
    codes?: StringKeyedStringList;
}

export function defaultEvent(): Event
{
    return {
        id: -1,
        name: 'Please wait while loading',
        competitions: [],
        sideEvents: [],
        opens: '',
        reg_open: '',
        reg_close: '',
        year: 0,
        duration: 0,
        email: '',
        web: '',
        location: '',
        countryId: 0,
        type: { name: ''},
        bank: {symbol: '', currency:'', bank: '', account: '', address:'', iban: '', swift: '', reference: '', baseFee: 0, competitionFee: 0},
        payments: 'group',
        feed: '',
        config: {}
    };
}