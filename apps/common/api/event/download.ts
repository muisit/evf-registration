import { fetchAttachment } from '../interface';
import type { SideEvent } from '../schemas/sideevent.ts';

export function downloadCSV(event:SideEvent)
{
    return fetchAttachment('/events/csv/' + event.id);
}

export function downloadXML(event:SideEvent)
{
    return fetchAttachment('/events/xml/' + event.id);
}
