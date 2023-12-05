import type { FencerById } from "../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../common/api/schemas/registration";

export function addRegistration(fencerData:FencerById, registration:Registration)
{
    let newData:FencerById = {};
    Object.keys(fencerData).map((key:string) => {
        let fencer = fencerData[key];
        if (fencer.id == registration.fencerId) {
            let found = false;
            fencer.registrations = fencer.registrations?.map((reg) => {
                if (registration.id == reg.id) {
                    found = true;
                }
                return reg;
            });
            if (!found) {
                if (!fencer.registrations) {
                    fencer.registrations = [];
                }
                fencer.registrations.push(registration);
            }
        }
        newData[key] = fencer;
    });
    return newData;
}