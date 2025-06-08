import type { PageProps } from '@inertiajs/core'
import type { LucideIcon } from 'lucide-vue-next'
import type { Config } from 'ziggy-js'
import type { ContentType, Role, Visibility } from './enum'

export interface Auth {
  user?: User
}

export interface BreadcrumbItem {
  title: string
  href: string
}

export interface NavItem {
  title: string
  href: string
  icon?: LucideIcon
  isActive?: boolean
}

export interface SharedData extends PageProps {
  name: string
  auth: Auth
  ziggy: Config & { location: string }
  sidebarOpen: boolean
  availableModels: Model[]
}

export interface User {
  id: number
  name: string
  email: string
  avatar?: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface HistoryItem {
  id: number
  title: string
  created_at: string
  updated_at: string
  visibility: Visibility
}

export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

export interface ChatHistory {
  data: HistoryItem[]
  path: string
  per_page: number
  from: number
  to: number
  total: number
  first_page_url: string
  last_page: number
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  links: PaginationLink[]
  current_page: number
}

export interface PartType {
  type: ContentType
  content: string
}

export interface Chunk {
  chunkType: ChunkType
  content: string
}

export type MessageChunks = Record<ChunkType, string>

export interface Message {
  id?: string
  chat_id?: string
  role: Role
  parts: MessageChunks
  attachments?: string[]
  is_upvoted?: boolean
  created_at?: string
  updated_at?: string
}

export type BreadcrumbItemType = BreadcrumbItem

export interface Chat {
  id: string
  user_id: number
  title: string
  visibility: Visibility
  created_at: string
  updated_at: string
  messages?: Message[]
}

export interface Model {
  id: string
  name: string
  description: string
  provider: string
}
