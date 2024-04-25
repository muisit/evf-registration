import { extractCode } from "./extractCode";
import type { CodeDispatcher } from "./codedispatcher";
import { useBasicStore } from "../../../../common/stores/basic";
import dayjs from "dayjs";

export function processCode(code:string, dispatcher:CodeDispatcher)
{
    let codeObject = extractCode(code);
    codeObject.scannedTime = dayjs().format('MM-DD HH:mm:ss');
    switch (codeObject.baseFunction || 0) {
        default:
        case 0:
            if (dispatcher.fail) dispatcher.fail(code, codeObject);
            break;
        case 1:
            if (dispatcher.success) dispatcher.success(code, codeObject);           
            if (dispatcher.badge) {
                dispatcher.badge(code, codeObject);
            }
            break;
        case 2:
            codeObject.data = codeObject.id2 || 0;
            if (dispatcher.success) dispatcher.success(code, codeObject);
            if (dispatcher.card) dispatcher.card(code, codeObject);
            break;
        case 3:
            const basicStore = useBasicStore();
            codeObject.data = (((codeObject.id1 || 0) % 10)*1000) + (codeObject.id2 || 0);
            if (basicStore.event && !(((basicStore.event?.id || 0) == (codeObject.payload || 0)) || (parseInt(codeObject.payload || '0') == 37))) {
                if (dispatcher.fail) dispatcher.fail(code, codeObject);
            }
            else {
                if (dispatcher.success) dispatcher.success(code, codeObject);
                if (dispatcher.document) dispatcher.document(code, codeObject);
            }
            break;
        case 9:
            if (dispatcher.success) dispatcher.success(code, codeObject);
            if (dispatcher.admin) dispatcher.admin(code, codeObject);
            break;
    }
    if (dispatcher.complete) dispatcher.complete(code, codeObject);

    return codeObject;
}