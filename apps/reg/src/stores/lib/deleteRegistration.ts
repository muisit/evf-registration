import type { FencerById } from "../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../common/api/schemas/registration";

export function deleteRegistration(fencerData:FencerById, registration:Registration): FencerById
{
    let newData:FencerById = {};
    Object.keys(fencerData).map((key:string) => {
        let fencer = fencerData[key];
        if (fencer.id == registration.fencerId) {
            if (fencer.registrations) {
                fencer.registrations = fencer.registrations.filter((reg) => reg.id != registration.id);
            }
        }
        newData[key] = fencer;
    });
    return newData;
}
