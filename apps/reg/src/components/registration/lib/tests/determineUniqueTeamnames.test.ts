import { expect, expectTypeOf, test } from 'vitest';
import { determineUniqueTeamNames } from '../determineUniqueTeamNames';

import type { Fencer, FencerById } from '../../../../../../common/api/schemas/fencer';
import type { SideEvent } from '../../../../../../common/api/schemas/sideevent';
import type { WeaponSchema } from '../../../../../../common/api/schemas/weapon';
import type { CategorySchema } from '../../../../../../common/api/schemas/category';
import type { Competition } from '../../../../../../common/api/schemas/competition';
import type { Registration } from '../../../../../../common/api/schemas/registration';

import { defaultFencer } from '../../../../../../common/api/schemas/fencer';
import { defaultSideEvent } from '../../../../../../common/api/schemas/sideevent';
import { defaultCategory } from '../../../../../../common/api/schemas/category';
import { defaultCompetition } from '../../../../../../common/api/schemas/competition';
import { defaultWeapon } from '../../../../../../common/api/schemas/weapon';

function generateFencers(): FencerById
{
    let fencerById:FencerById = {};
    for (let i = 1;i < 4; i++) {
        let fencer = defaultFencer();
        fencer.id = i;
        fencer.lastName = fencer.lastName + ':' + i;
        fencerById['f' + i] = fencer;
    }
    return fencerById;
}

function registerForEvents(fencer:Fencer, events:SideEvent[])
{
    fencer.registrations = [];
    events.map((se:SideEvent) => {
        if (fencer.registrations) {
            fencer.registrations.push({
                id: (fencer.id || 0) * 1000 + (se.id || 0),
                roleId: null,
                sideEventId: se.id || 0,
                fencerId: fencer.id || 0,
                paid: 'N',
                paidHod: 'N',
                dateTime: '',
                payment: 'G',
                state: null,
                team: null
            });
        }
    });
}

function setTeam(fencer:Fencer, sideEventId:number, team:string)
{
    if (fencer.registrations) {
        fencer.registrations = fencer.registrations.map((reg:Registration) => {
            if (reg.sideEventId == sideEventId) {
                reg.team = team;
            }
            return reg;
        });
    }
}

function generateWeapon()
{
    let wpn = defaultWeapon();
    wpn.id = 1;
    return wpn;
}

function generateCategory()
{
    let cat = defaultCategory();
    cat.id = 1;
    return cat;
}

function generateCompetition(wpn:WeaponSchema, cat:CategorySchema)
{
    let cmp = defaultCompetition();
    cmp.weaponId = wpn.id || 0;
    cmp.weapon = wpn;
    cmp.categoryId = cat.id || 0;
    cmp.category = cat;
    cmp.id = 1;
    return cmp;
}

function generateCompetitionEvent(cmp:Competition): SideEvent
{
    let se = defaultSideEvent();
    se.competitionId = cmp.id;
    se.competition = cmp;
    se.id = 1;
    return se;
}

function generateEvents(): SideEvent[]
{
    let wpn = generateWeapon();
    let cat = generateCategory();
    let cmp = generateCompetition(wpn, cat);
    let se1 = generateCompetitionEvent(cmp);
    let se2 = defaultSideEvent();
    se2.id = 2;
    se2.title = 'NonComp';
    se2.abbr = 'NC';
    return [se1, se2];
}

test('no teams', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    expect(determineUniqueTeamNames(fencerById, events)).toStrictEqual({});
})

test('1 team', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    expect(fencerById['f2']).toBeDefined();
    expect(fencerById['f3']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    registerForEvents(fencerById['f2'], events);
    registerForEvents(fencerById['f3'], events);
    setTeam(fencerById['f1'], 1, 'Team1');
    expect(determineUniqueTeamNames(fencerById, events)).toStrictEqual({Weapon: ['Team1']});
})

test('2 teams', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    expect(fencerById['f2']).toBeDefined();
    expect(fencerById['f3']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    registerForEvents(fencerById['f2'], events);
    registerForEvents(fencerById['f3'], events);
    setTeam(fencerById['f1'], 1, 'Team1');
    setTeam(fencerById['f2'], 1, 'Team2');
    expect(determineUniqueTeamNames(fencerById, events)).toStrictEqual({Weapon: ['Team1', 'Team2']});
})

test('1 team, 2 regs', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    expect(fencerById['f2']).toBeDefined();
    expect(fencerById['f3']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    registerForEvents(fencerById['f2'], events);
    registerForEvents(fencerById['f3'], events);
    setTeam(fencerById['f1'], 1, 'Team1');
    setTeam(fencerById['f2'], 1, 'Team1');
    expect(determineUniqueTeamNames(fencerById, events)).toStrictEqual({Weapon: ['Team1']});
})

test ('no events', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    expect(fencerById['f2']).toBeDefined();
    expect(fencerById['f3']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    registerForEvents(fencerById['f2'], events);
    registerForEvents(fencerById['f3'], events);
    setTeam(fencerById['f1'], 1, 'Team1');
    setTeam(fencerById['f2'], 1, 'Team1');
    expect(determineUniqueTeamNames(fencerById, [])).toStrictEqual({});
})


test('no fencers', () => {
    let fencerById = generateFencers();
    let events:SideEvent[] = generateEvents();
    expect(fencerById['f1']).toBeDefined();
    expect(fencerById['f2']).toBeDefined();
    expect(fencerById['f3']).toBeDefined();
    registerForEvents(fencerById['f1'], events);
    registerForEvents(fencerById['f2'], events);
    registerForEvents(fencerById['f3'], events);
    setTeam(fencerById['f1'], 1, 'Team1');
    setTeam(fencerById['f2'], 1, 'Team1');
    expect(determineUniqueTeamNames({}, events)).toStrictEqual({});
})
