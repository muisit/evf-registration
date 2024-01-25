import type { ElementContent, PictureContent, TemplateSchema } from "../../../../../common/api/schemas/template";
export function elementFactory(x:number, y:number, index:number, type:string, template?:TemplateSchema, picture?:PictureContent): ElementContent
{
    let retval:ElementContent = {
        type: type,
        style: {
            top: y,
            left: x,
            zIndex: 1
        },
        index: index
    };

    switch (type) {
        case 'accid':
            retval.style.width = 150;
            retval.style.height = 50;
            retval.style.fontSize = 18;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            retval.hasRatio = true;
            retval.ratio = 3;
            break;
        case 'country':
            retval.text = 'TST';
            retval.style.width = 420;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;
        case 'cntflag':
            retval.style.width = 400;
            retval.style.height = 300;
            retval.hasRatio = true;
            retval.ratio = 4/3;
            break;
        case 'org':
            retval.text = 'ORG';
            retval.style.width = 420;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;            
        case 'photo':
            retval.hasRatio = true;
            retval.ratio = 413.0 / 531.0;
            retval.style.width = 100;
            retval.style.height = 100 / retval.ratio;
            retval.style.zIndex = 1;
            break;
        case 'text':
            retval.text = 'Change Me';
            retval.style.fontSize = 20;
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;
        case 'name':
            retval.text = 'NOSUCHNAME, nosuchperson';
            retval.style.width = 420;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;
        case 'category':
            retval.text = '2';
            retval.style.width = 100;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;
        case 'roles':
            retval.style.width = 420;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            break;
        case 'dates':
            retval.style.width = 420;
            retval.style.height = 60;
            retval.style.fontSize = 30;
            retval.style.fontStyle = 'bold';
            retval.style.fontFamily = 'Helvetica';
            retval.style.color = '#000';
            retval.hasFontSize = true;
            retval.hasColour = true;
            retval.resizeable = true;
            retval.onedateonly = false;
            break;
        case 'box':
            retval.style.width = 420;
            retval.style.height = 200;
            retval.style.backgroundColor = '#a44';
            retval.hasBackgroundColour = true;
            retval.resizeable = true;
            break;
        case 'qr':
            retval.style.width = 100;
            retval.style.height = 100;
            retval.hasRatio = true;
            retval.ratio = 1.0;
            retval.resizeable = true;
            break;
        case 'img':
            let width = picture?.width || 0;
            let height = picture?.height || 1;
            let ratio = width / height;
            if (width > 420) {
                width = 420;
                height = width / ratio;
            }
            if (height > 594) {
                height = 594;
                width = ratio * height;
            }
            retval.style.width = width;
            retval.style.height = height;
            retval.hasRatio = true;
            retval.ratio = ratio;
            retval.file_id = picture?.file_id;
            break;
    }

    return retval;
}