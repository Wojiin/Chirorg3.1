import { describe, expect, it } from 'vitest'
import {
  evaluateQualityGate,
  qualityGateError,
} from '../../../lighthouse/qualityGate.mjs'

const optimalScores = {
  performance: { median: 99, minimum: 98 },
  accessibility: { median: 100, minimum: 100 },
  'best-practices': { median: 100, minimum: 100 },
}

describe('Lighthouse quality gate', () => {
  it('accepts scores meeting the default budgets', () => {
    expect(evaluateQualityGate(optimalScores)).toEqual({
      passed: true,
      thresholds: {
        performanceMedian: 98,
        performanceMinimum: 95,
        accessibilityMinimum: 100,
        bestPracticesMinimum: 100,
      },
      failures: [],
    })
  })

  it('reports every score below its configured budget', () => {
    const result = evaluateQualityGate(
      {
        performance: { median: 96, minimum: 90 },
        accessibility: { median: 99, minimum: 99 },
        'best-practices': { median: 100, minimum: 100 },
      },
      { LH_MIN_PERFORMANCE_MEDIAN: '97' },
    )

    expect(result.passed).toBe(false)
    expect(result.failures.map(({ metric }) => metric)).toEqual([
      'performanceMedian',
      'performanceMinimum',
      'accessibilityMinimum',
    ])
    expect(qualityGateError(result.failures)).toContain(
      'médiane de performance: 96/100, minimum 97/100',
    )
  })

  it('rejects an invalid environment threshold', () => {
    expect(() =>
      evaluateQualityGate(optimalScores, {
        LH_MIN_ACCESSIBILITY: '101',
      }),
    ).toThrow('LH_MIN_ACCESSIBILITY doit être un score compris entre 0 et 100.')
  })
})
