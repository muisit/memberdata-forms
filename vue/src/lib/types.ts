export interface FieldDefinition {
    field: string;
    value: string;
}

export interface Sheet {
    id: number;
    name: string;
}

export interface Configuration {
    sheet?: number;
}

export interface BasicSettings {
    attributes?: Array<string>;
    sheets?: Array<Sheet>;
}

export interface APIResult {
    success?: boolean;
    data?: any;
}

export interface Form {
    id: number;
    name: string;
    sheet_id: number;
    settings: any;
}

export interface Field {
    label: string;
    type: string;
    attribute: string;
    description: string;
    defaultValue: string;
    options: string;
    rules: RuleSet;
    token?: string;
}

export interface RuleSet {
    [key:string]: string;
}

export interface WPPost {
    id: number;
    title: string;
    slug: string;
}