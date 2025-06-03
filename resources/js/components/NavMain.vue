<script setup lang="ts">
import { computed, watch, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { WhenVisible } from '@inertiajs/vue3'
import { useLocalStorage } from '@vueuse/core'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import type { ChatHistory, SharedData, HistoryItem } from '@/types'

interface Props {
  chatHistory: ChatHistory
}

const props = withDefaults(defineProps<Props>(), {
  chatHistory: () => ({
    data: [],
    current_page: 1,
    next_page_url: null,
    path: '',
    per_page: 25,
    from: 0,
    to: 0,
    total: 0,
    first_page_url: '',
    last_page: 1,
    last_page_url: '',
    prev_page_url: null,
    links: []
  })
})

const page = usePage<SharedData>()

const allChatHistory = useLocalStorage<HistoryItem[]>('chat-history', [])
const lastLoadedPage = useLocalStorage<number>('chat-last-page', 0)
const maxLoadedPage = useLocalStorage<number>('chat-max-page', 0)



const hasMorePages = computed(() => props.chatHistory.next_page_url !== null)
const hasHistoryItems = computed(() => allChatHistory.value.length > 0)
const nextPageNumber = computed(() => maxLoadedPage.value + 1)

const isCurrentChat = (chatId: string | number): boolean => {
  return route('chats.show', chatId, false) === page.url
}


const deleteChat = (chatId: number) => {
    router.delete(route('chats.destroy', chatId), {
        preserveScroll: true,
        onSuccess: () => {
            if (route('chats.show', chatId, false) === page.url) {
                router.visit(route('chats.index'));
            }
        },
    });
};

const handleChatHistoryUpdate = (newChatHistory: ChatHistory) => {
  if (allChatHistory.value.length === 0) {
    allChatHistory.value = [...newChatHistory.data]
    lastLoadedPage.value = newChatHistory.current_page
    maxLoadedPage.value = newChatHistory.current_page
    return
  }

  if (newChatHistory.current_page > maxLoadedPage.value) {
    const newItems = newChatHistory.data.filter(
      item => !allChatHistory.value.some(existing => existing.id === item.id)
    )
    allChatHistory.value.push(...newItems)
    maxLoadedPage.value = newChatHistory.current_page
  }

  lastLoadedPage.value = newChatHistory.current_page
}

watch(() => props.chatHistory,
  handleChatHistoryUpdate,
  { deep: true, immediate: true }
)


</script>

<template>
  <SidebarGroup
    v-if="hasHistoryItems"
    ref="sidebarGroupRef"
    class="px-2 py-0"
    role="navigation"
    aria-label="Chat History Navigation"
  >
    <SidebarGroupLabel>
      Chat History
    </SidebarGroupLabel>

    <SidebarMenu>
      <SidebarMenuItem
        v-for="historyItem in allChatHistory"
        :key="`chat-${historyItem.id}`"
      >
        <SidebarMenuButton
          as-child
          :class="{
            'bg-secondary text-secondary-foreground': isCurrentChat(historyItem.id)
          }"
          :tooltip="historyItem.title"
        >
          <Link
            prefetch
            :preserve-scroll="true"
            :only="['chat']"
            :href="route('chats.show', historyItem.id)"
            :aria-label="`Open chat: ${historyItem.title}`"
            class="block w-full"
            @click="handleChatLinkClick()"
          >
            <span class="truncate">{{ historyItem.title }}</span>
          </Link>
        </SidebarMenuButton>
        <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button 
                                variant="ghost" 
                                size="sm" 
                                class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground hover:text-destructive"
                            >
                                <Icon icon="lucide:trash-2" class="h-4 w-4" />
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This action cannot be undone. This will permanently delete the chat "{{ historyItem.title }}" and remove all its messages from our servers.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="deleteChat(historyItem.id)" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                                    Delete Chat
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
      </SidebarMenuItem>
    </SidebarMenu>

    <WhenVisible
      v-if="hasMorePages"
      :params="{
        preserveUrl: true,
        data: { page: nextPageNumber },
        only: ['chatHistory']
      }"
      :always="hasMorePages"
    >
      <template #fallback>
        <SidebarGroupLabel
          class="mt-2"
          role="status"
          aria-live="polite"
        >
          <div>Loading more chats...</div>
        </SidebarGroupLabel>
      </template>
    </WhenVisible>

    <SidebarGroupLabel
      v-if="!hasMorePages && hasHistoryItems"
      class="mt-2 text-muted-foreground"
      role="status"
    >
      You have reached the end of your chat history.
    </SidebarGroupLabel>
  </SidebarGroup>
</template>
