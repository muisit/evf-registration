import type { AccreditationStatistics } from '../../../../common/api/schemas/accreditationstatistics';
import type { Fencer } from '../../../../common/api/schemas/fencer';

export interface StatById {
    [key:string]: AccreditationStatistics;
}

export interface FencerData {
    fencer: Fencer;
    checkin: string[];
    checkout: string[];
}

export interface FencerDataById {
    [key:string]: FencerData;
}
