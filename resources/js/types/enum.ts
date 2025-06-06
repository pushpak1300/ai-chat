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

export enum ChunkType {
  TEXT = 'text',
  THINKING = 'thinking',
  META = 'meta',
}
