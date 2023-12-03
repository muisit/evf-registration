export interface RoleSchema {
    id: number|null;
    name: string|null;
    type: string|null;
};

export interface RoleById {
    [key:string]: RoleSchema;
}