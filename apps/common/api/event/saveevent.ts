import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Event } from '../schemas/event';

export const saveevent = function(event:Event) {
    return new Promise<Event|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data:any = {
            id: event.id,
            name: event.name,
            opens: event.opens,
            year: event.year,
            duration: event.duration,
            countryId: event.countryId,
            payments: event.payments,
            config: JSON.stringify(event.config)
        };

        if (event.reg_open) data.reg_open = event.reg_open;
        if (event.reg_close) data.reg_close = event.reg_close;
        if (event.email) data.email = event.email;
        if (event.web) data.web = event.web;
        if (event.location) data.location = event.location;
        if (event.bank?.symbol) data.symbol = event.bank.symbol;
        if (event.bank?.currency) data.currency = event.bank.currency;
        if (event.bank?.bank) data.bank = event.bank.bank;
        if (event.bank?.account) data.account = event.bank.account;
        if (event.bank?.address) data.address = event.bank.address;
        if (event.bank?.iban) data.iban = event.bank.iban;
        if (event.bank?.swift) data.swift = event.bank.swift;
        if (event.bank?.reference) data.reference = event.bank.reference;
        if (event.bank?.competitionFee) data.competitionFee = event.bank.competitionFee;
        if (event.bank?.baseFee) data.baseFee = event.bank.baseFee;

        return fetchJson('POST', '/events', { event: data })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
