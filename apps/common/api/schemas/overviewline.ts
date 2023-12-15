import type { CountrySchema } from "./country";
import type { SideEvent } from "./sideevent";

export interface OverviewLine {
    country: string;
    counts: {
        [key:string]: Array<number>
    };
};

export interface EventObject {
    sideEvent: SideEvent|null;
    participants: number;
    teams: number;
}

export interface EventsObject {
    [key:string]: EventObject;
}

export interface OverviewObject {
    country: CountrySchema;
    events: EventsObject;
}

export interface OverviewObjects {
    [key:string]: OverviewObject;
}
