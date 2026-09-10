import { describe, expect, it } from 'vitest'
import {
  ERROR_MESSAGES,
  requiredFieldMessage,
  TECHNICAL_API_MESSAGES,
  TECHNICAL_API_MESSAGE_PREFIXES,
} from '@/config/errorMessages'

describe('error message catalog', () => {
  it('exposes immutable, non-empty and unique messages', () => {
    const messages = Object.values(ERROR_MESSAGES)

    expect(Object.isFrozen(ERROR_MESSAGES)).toBe(true)
    expect(Object.isFrozen(TECHNICAL_API_MESSAGES)).toBe(true)
    expect(Object.isFrozen(TECHNICAL_API_MESSAGE_PREFIXES)).toBe(true)
    expect(messages.length).toBeGreaterThan(30)
    expect(messages.every((message) => message.trim().length > 0)).toBe(true)
    expect(new Set(messages).size).toBe(messages.length)
  })

  it('formats required field violations consistently', () => {
    expect(requiredFieldMessage('Spécialité')).toBe(
      'Le champ « Spécialité » est obligatoire.',
    )
  })
})
