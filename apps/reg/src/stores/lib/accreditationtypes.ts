import type { AccreditationDocument } from "../../../../common/api/schemas/accreditation";
import type { CountrySchema } from "../../../../common/api/schemas/country";
import type { RoleSchema } from "../../../../common/api/schemas/role";
import type { SideEvent } from "../../../../common/api/schemas/sideevent";
import type { TemplateSchema } from "../../../../common/api/schemas/template";

export interface CountPerEvent
{
    sideEvent: SideEvent;
    registrations: number;
    accreditations: number;
    dirty: number;
    generated: number;
    documents: AccreditationDocument[];
}

export interface CountPerCountry
{
    country: CountrySchema;
    registrations: number;
    accreditations: number;
    dirty: number;
    generated: number;
    documents: AccreditationDocument[];
}

export interface CountPerRole
{
    role: RoleSchema;
    registrations: number;
    accreditations: number;
    dirty: number;
    generated: number;
    documents: AccreditationDocument[];
}

export interface CountPerTemplate
{
    template: TemplateSchema;
    registrations: number;
    accreditations: number;
    dirty: number;
    generated: number;
    documents: AccreditationDocument[];
}

export interface BadgeOverview
{
    events: CountPerEvent[];
    countries: CountPerCountry[];
    roles: CountPerRole[];
    templates: CountPerTemplate[];
}