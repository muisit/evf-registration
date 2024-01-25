export interface EventRole {
    id: number;
    userId: number;
    role: string;
}

export interface EventUser {
    id:number;
    name:string;
}

export interface EventRoles {
    roles:EventRole[];
    users:EventUser[];
}
