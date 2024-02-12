import type { Code } from '../../../../common/api/schemas/codes';

function calculateValidation(lst:string[])
{
    let result = 0;
    lst.map((s:string) => {
        result += parseInt(s);
    });
    return (10 - (result % 10)) % 10;
}

function calculateUPCChecksum(lst:string[])
{
    let result = 0;
    lst.map((s:string, index:number) => {
        result += parseInt(s);
        if ((index % 2) == 1) {
            result += 2 * parseInt(s);
        }
    });
    return result % 10;
}

export function extractCode(code:string): Code
{
    let retval:Code = { original: code };
    if (code.length > 13) {
        let index = 0;
        let baseFunc = code[index];
        while (baseFunc == '0') {
            index += 1;
            baseFunc = code[index];
        }

        if (code[index + 1] == baseFunc && code.length > (index + 13)) {
            retval.baseFunction = parseInt(baseFunc);
            retval.addFunction = parseInt(code[index + 2]);

            retval.id1 = parseInt(code[index + 3] + code[index + 4] + code[index + 5]);
            retval.id2 = parseInt(code[index + 6] + code[index + 7] + code[index + 8]);

            retval.validation = parseInt(code[index + 9]);
            let check = calculateValidation(code.split('').slice(index + 2, index + 9));
            if (check != retval.validation) {
                console.log('invalid code detected, validation says ', check, retval.validation);
                return { original: code };
            }

            retval.payload = code[index + 10] + code[index + 11] + code[index + 12] + code[index + 13]

            if (code.length > (index + 14)) {
                let finalValue = parseInt(code[index + 14]);
                let upcCheck = calculateUPCChecksum(code.split('').slice(0, code.length - 1));
                if (! ((upcCheck === 0 && finalValue === 0) || (finalValue == (10 - upcCheck)))) {
                    return {original: code};
                }
            }
        }
    }
    return retval;
}