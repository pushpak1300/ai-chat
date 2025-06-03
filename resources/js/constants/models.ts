export const AVAILABLE_MODELS = [
  {
    id: 'gemini-2.0-flash',
    name: 'Gemini 2.0 Flash',
    description: 'Cheapest model, best for smarter tasks',
  },
  {
    id: 'gemini-2.0-flash-lite',
    name: 'Gemini 2.0 Flash Lite',
    description: 'Cheapest model, best for simpler tasks',
  },
]

export interface Model {
  id: string
  name: string
  description: string
}

export const MODEL_KEY = 'selected-model'
