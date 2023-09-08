export interface SideEvent {
    id: number;
    title: string;
    description: string;
    starts: string;
    costs: number;
    competitionId: number;

    // front-end data
    abbr: string|null;
    competition: object|null;
}
