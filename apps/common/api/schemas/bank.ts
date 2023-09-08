export interface Bank {
    symbol:string|null;
    currency: string|null;
    bank: string|null;
    account: string|null;
    address: string|null;
    iban: string|null;
    swift: string|null;
    reference: string|null;
    baseFee: number|null;
    competitionFee: number|null;
}
