export function is_valid(id:string|number|object|undefined)
{
    if (!id) return false;
    if (id.id) return is_valid(id.id);
    if (parseInt(id) > 0) return true;
    return false;
}