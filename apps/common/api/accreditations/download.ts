import { fetchAttachment } from '../interface';

export function download(id:number)
{
    return fetchAttachment('/accreditations/summary/' + id);
}
