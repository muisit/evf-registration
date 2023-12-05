import type { FencerById } from "../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../common/api/schemas/registration";

export function updateRegistration(fencerData:FencerById, registration:Registration, callback?:Function): FencerById
{
    let newData:FencerById = {};
    Object.keys(fencerData).map((key:string) => {
        let fencer = fencerData[key];
        if (fencer.id == registration.fencerId) {
            fencer.registrations = fencer.registrations?.map((reg) => {
                if (registration.id == reg.id) {
                    if (callback) {
                        reg = callback(reg, registration);
                    }
                    else {
                        reg = registration;
                    }
                }
                return reg;
            });
        }
        newData[key] = fencer;
    });
    return newData;
}
