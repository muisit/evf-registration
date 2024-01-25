import type { StringKeyedValues } from '../../types';

export interface TemplateSchema {
    id: number;
    name: string;
    content: TemplateContent;
    isDefault: string;
    eventId?: number;
}

export interface ElementContent {
    type: string;
    text ?: string;
    link ?: string;
    side ?: string;
    style: StringKeyedValues;
    ratio?: number;
    hasRatio?: boolean;
    hasFontSize ?:boolean;
    hasColour ?: boolean;
    hasBackgroundColour ?: boolean;
    resizeable?: boolean;
    onedateonly?: boolean;
    fitText?: boolean;
    name?: string;
    color2?: string;
    backgroundColor2?: string;
    file_id?: string;
    index: number;
}

export interface PictureContent {
    width: number;
    height: number;
    file_ext: string;
    file_id: string;
    file_name: string;
    file_mimetype?: string;
    type?: string;
}

export interface TemplateContent {
    print ?: string;
    roles?: number[];
    elements?: ElementContent[];
    pictures?: PictureContent[];
}
