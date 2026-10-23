import assert from 'node:assert/strict';
import test from 'node:test';
import { parseLatvianDate, formatLatvianDate } from './src/services/latvianValidation.ts';

test('Latvian dates round-trip to the API ISO date without swapping day/month', () => {
  for (const [input, iso] of [['23.10.2026', '2026-10-23'], ['02.03.2026', '2026-03-02'], ['29.02.2024', '2024-02-29'], ['01.01.0001', '0001-01-01']]) {
    assert.equal(parseLatvianDate(input), iso);
    assert.equal(formatLatvianDate(iso), input);
  }
  assert.equal(parseLatvianDate('23.10.2026.'), '2026-10-23');
  assert.equal(formatLatvianDate(''), '');
});

test('impossible dates, partial input and non-Latvian formats are rejected', () => {
  for (const input of ['', '23.10.', '29.02.2025', '31.04.2026', '00.10.2026', '23.13.2026', '01.01.0000', '2026-10-23', '10/23/2026', 'text']) {
    assert.equal(parseLatvianDate(input), null, input);
  }
});
