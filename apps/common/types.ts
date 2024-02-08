export interface StringKeyedStringList {
    [key:string]: string[];
}

export interface StringKeyedString {
    [key:string]: string;
}

export interface StringKeyedNumber {
    [key:string]: number;
}

export interface StringKeyedValues {
    [key:string]: any;
}

export interface StringKeyedBoolean {
    [key:string]: boolean;
}

export interface SelectOption {
    value: string;
    label: string;
}

export interface ValidationResult {
    [key:string]: string[];
}
