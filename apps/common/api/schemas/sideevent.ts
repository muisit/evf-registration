import type { Competition } from "./competition";

export interface SideEventById {
    [key:string]: SideEvent;
}

export interface SideEvent {
    id: number;
    title: string;
    description: string;
    starts: string;
    costs: number;
    competitionId: number;

    // front-end data
    abbr: string|null;
    competition: Competition|null;
    isAthleteEvent?: boolean;
    isTeamEvent?: boolean;
    isNonCompetitionEvent?: boolean;
    isRegistered?: boolean;
    defaultRole?: string|null;
}
