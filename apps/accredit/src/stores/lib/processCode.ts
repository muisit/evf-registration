import { extractCode } from "./extractCode";
import type { CodeDispatcher } from "./codedispatcher";
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
            if (dispatcher.badge) dispatcher.badge(code, codeObject);
            break;
        case 2:
            if (dispatcher.success) dispatcher.success(code, codeObject);
            if (dispatcher.card) dispatcher.card(code, codeObject);
            break;
        case 3:
            if (dispatcher.success) dispatcher.success(code, codeObject);
            if (dispatcher.document) dispatcher.document(code, codeObject);
            break;
        case 9:
            if (dispatcher.success) dispatcher.success(code, codeObject);
            if (dispatcher.admin) dispatcher.admin(code, codeObject);
            break;
    }
    if (dispatcher.complete) dispatcher.complete(code, codeObject);

    return codeObject;
}