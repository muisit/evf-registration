import { fetchAttachment } from '../interface';
import type { TemplateSchema } from '../schemas/template';

export function templateprint(template:TemplateSchema)
{
    return fetchAttachment('/templates/' + template.id + '/print');
}
