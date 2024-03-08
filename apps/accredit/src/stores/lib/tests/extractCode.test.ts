import { expect, expectTypeOf, test } from 'vitest';
import { extractCode } from '../extractCode';

test('badge', () => {
    expect(extractCode('11153540390000')).toStrictEqual({
        original: '11153540390000',
        baseFunction: 1,
        addFunction: 1,
        id1: 535,
        id2: 403,
        payload: '0000',
        data: -1,
        validation: 9
    });
});

test('card', () => {
    expect(extractCode('22493800510000')).toStrictEqual({
        original: '22493800510000',
        baseFunction: 2,
        addFunction: 4,
        id1: 938,
        id2: 5,
        payload: '0000',
        data: -1,
        validation: 1
    });
});

test('doc', () => {
    expect(extractCode('33266246400037')).toStrictEqual({
        original: '33266246400037',
        baseFunction: 3,
        addFunction: 2,
        id1: 662,
        id2: 464,
        payload: '0037',
        data: -1,
        validation: 0
    });
});

test('longbadge', () => {
    expect(extractCode('0111535403900004')).toStrictEqual({
        original: '0111535403900004',
        baseFunction: 1,
        addFunction: 1,
        id1: 535,
        id2: 403,
        payload: '0000',
        data: -1,
        validation: 9
    });
});

test('error in badge', () => {
    expect(extractCode('11153540380000')).toStrictEqual({
        original: '11153540380000',
        data: -1
    });
});

test('too short', () => {
    expect(extractCode('1153540390000')).toStrictEqual({
        original: '1153540390000',
        data: -1
    });
});

// extended misses the upc checksum
test('extended', () => {
    expect(extractCode('011153540390000400')).toStrictEqual({
        original: '011153540390000400',
        data: -1
    });
});
// prefixed misses the upc checksum
test('prefixed', () => {
    expect(extractCode('000000111535403900004')).toStrictEqual({
        original: '000000111535403900004',
        data: -1,
    });
});

// with an odd number of zeroes, the upcChecksum does not change
test('prefixed 2', () => {
    expect(extractCode('00000111535403900004')).toStrictEqual({
        original: '00000111535403900004',
        baseFunction: 1,
        addFunction: 1,
        id1: 535,
        id2: 403,
        payload: '0000',
        data: -1,
        validation: 9
    });
});
