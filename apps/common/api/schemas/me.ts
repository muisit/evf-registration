export interface MeSchema {
    status: boolean;
    username: string;
    token: string|null;
    credentials: Array<string>|null;
};