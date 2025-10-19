import type { MaybeRef } from 'vue'
import type { ChatHistory, HistoryItem } from '@/types'
import { computed, unref } from 'vue'

interface GroupedChatHistory {
  today: HistoryItem[]
  yesterday: HistoryItem[]
  lastSevenDays: HistoryItem[]
  lastThirtyDays: HistoryItem[]
  older: HistoryItem[]
}

export function useChatHistory(chatHistory: MaybeRef<ChatHistory | null>) {
  const groupedChatHistory = computed((): GroupedChatHistory => {
    const history = unref(chatHistory)
    if (!history) {
      return {
        today: [],
        yesterday: [],
        lastSevenDays: [],
        lastThirtyDays: [],
        older: [],
      }
    }

    const now = new Date()
    const today = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate(),
    )
    const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000)
    const sevenDaysAgo = new Date(
      today.getTime() - 7 * 24 * 60 * 60 * 1000,
    )
    const thirtyDaysAgo = new Date(
      today.getTime() - 30 * 24 * 60 * 60 * 1000,
    )

    const groups: GroupedChatHistory = {
      today: [],
      yesterday: [],
      lastSevenDays: [],
      lastThirtyDays: [],
      older: [],
    }

    history.data.forEach((chat) => {
      const chatDate = new Date(chat.updated_at)
      const chatDateOnly = new Date(
        chatDate.getFullYear(),
        chatDate.getMonth(),
        chatDate.getDate(),
      )

      if (chatDateOnly.getTime() === today.getTime()) {
        groups.today.push(chat)
      }
      else if (chatDateOnly.getTime() === yesterday.getTime()) {
        groups.yesterday.push(chat)
      }
      else if (chatDate >= sevenDaysAgo) {
        groups.lastSevenDays.push(chat)
      }
      else if (chatDate >= thirtyDaysAgo) {
        groups.lastThirtyDays.push(chat)
      }
      else {
        groups.older.push(chat)
      }
    })

    return groups
  })

  const hasMorePages = computed(() => {
    const history = unref(chatHistory)
    return history?.next_page_url !== null
  })

  const hasAnyHistory = computed(() => {
    const history = unref(chatHistory)
    return (history?.data?.length ?? 0) > 0
  })

  return {
    groupedChatHistory,
    hasMorePages,
    hasAnyHistory,
  }
}
