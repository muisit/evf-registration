import { Fencer } from "../../../../../common/api/schemas/fencer";
import { RoleSchema } from "../../../../../common/api/schemas/role";
import { useDataStore } from "../../../stores/data";
import { useAuthStore } from "../../../../../common/stores/auth";

export function selectRolesForFencer(fencer:Fencer) {
    const data = useDataStore();
    const auth = useAuthStore();

    // filter out valid roles for the capabilities
    let roles = data.roles.filter((itm:RoleSchema) => {
        if (auth.isHodFor() && itm.type == 'Country')  return true;
        if (auth.isOrganisation() && (itm.type == 'Org' || itm.type == 'Country')) return true;
        if (auth.isSysop()) return true; // allow all roles for system administrators
        return false;
    });

    roles.sort((a:RoleSchema, b:RoleSchema) => {
        if (a.type == 'Country' && b.org != 'Country') return -1;
        if (b.type =='Country' && a.type != 'Country') return 1;
        if (a.type == 'Org' && b.type != 'Org') return -1;
        if (b.type == 'Org' && a.type != 'Org') return 1;

        // role name could be null, but never is in practice
        if (!a.name && b.name) return -1;
        else if(a.name && !b.name) return 1;
        else if (a.name && b.name) {
            return  (a.name < b.name) ? -1 : 1;
        }
        return 0;
    });
    return roles;
}
