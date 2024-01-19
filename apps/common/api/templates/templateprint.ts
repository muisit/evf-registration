import { fetchAttachment } from '../interface.ts';
import type { TemplateSchema } from '../schemas/template.ts';

export function templateprint(template:TemplateSchema)
{
    return fetchAttachment('/templates/' + template.id + '/print');
}
