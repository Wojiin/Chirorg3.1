const definitions = [
  {
    key: 'performanceMedian',
    environment: 'LH_MIN_PERFORMANCE_MEDIAN',
    category: 'performance',
    statistic: 'median',
    fallback: 98,
    label: 'médiane de performance',
  },
  {
    key: 'performanceMinimum',
    environment: 'LH_MIN_PERFORMANCE_MINIMUM',
    category: 'performance',
    statistic: 'minimum',
    fallback: 95,
    label: 'minimum de performance',
  },
  {
    key: 'accessibilityMinimum',
    environment: 'LH_MIN_ACCESSIBILITY',
    category: 'accessibility',
    statistic: 'minimum',
    fallback: 100,
    label: "minimum d'accessibilité",
  },
  {
    key: 'bestPracticesMinimum',
    environment: 'LH_MIN_BEST_PRACTICES',
    category: 'best-practices',
    statistic: 'minimum',
    fallback: 100,
    label: 'minimum de bonnes pratiques',
  },
]

function thresholdFromEnvironment(name, fallback, environment) {
  const value = Number(environment[name] ?? fallback)
  if (!Number.isFinite(value) || value < 0 || value > 100) {
    throw new Error(`${name} doit être un score compris entre 0 et 100.`)
  }
  return value
}

/** Compare les médianes/minimums aux budgets stables retenus pour la CI. */
export function evaluateQualityGate(globalScores, environment = {}) {
  const thresholds = {}
  const failures = []

  for (const definition of definitions) {
    const expected = thresholdFromEnvironment(
      definition.environment,
      definition.fallback,
      environment,
    )
    const actual = globalScores[definition.category]?.[definition.statistic]
    thresholds[definition.key] = expected

    if (!Number.isFinite(actual) || actual < expected) {
      failures.push({
        metric: definition.key,
        label: definition.label,
        actual: Number.isFinite(actual) ? actual : null,
        expected,
      })
    }
  }

  return { passed: failures.length === 0, thresholds, failures }
}

export function qualityGateError(failures) {
  return failures
    .map(
      ({ label, actual, expected }) =>
        `${label}: ${actual ?? 'indisponible'}/100, minimum ${expected}/100`,
    )
    .join(' ; ')
}
