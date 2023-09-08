export interface OverviewLine {
    country: string;
    counts: {
        [key:string]: Array<number>
    };
};