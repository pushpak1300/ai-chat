export enum Visibility {
  PUBLIC = 'public',
  PRIVATE = 'private',
}

export enum StreamStatus {
  READY = 'ready',
  STREAMING = 'streaming',
  SUBMITTED = 'submitted',
}

export enum Role {
  USER = 'user',
  ASSISTANT = 'assistant',
}

export enum StreamEventType {
  TEXT_DELTA = 'text_delta',
  THINKING = 'thinking',
  ERROR = 'error',
}

export enum ContentType {
  TEXT = 'text',
  THINKING = 'thinking',
}
