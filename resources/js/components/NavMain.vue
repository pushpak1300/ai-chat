<script setup lang="ts">
import type { ChatHistory, SharedData } from '@/types'
import { Icon } from '@iconify/vue'
import { Link, usePage, WhenVisible } from '@inertiajs/vue3'
import { computed } from 'vue'
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar'

const props = withDefaults(defineProps<{
  chatHistory?: ChatHistory
}>(), {
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
    links: [],
  }),
})
const hasMorePages = computed(() => props.chatHistory.next_page_url !== null)

const mainMenuItems = [
  {
    label: 'New Chat',
    icon: 'lucide:notebook-pen',
    href: route('chats.index'),
  },
]

const page = usePage<SharedData>()
</script>

<template>
  <div>
    <SidebarGroup>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            v-for="item in mainMenuItems" :key="item.label" class="flex items-center font-bold"
            :as="Link" :href="item.href" :aria-label="item.label"
          >
            <Icon :icon="item.icon" class="w-4 h-4" />
            <span class="ml-2">
              {{ item.label }}
            </span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup
      v-if="chatHistory.data.length > 0" class="px-2 py-0" role="navigation"
      aria-label="Chat History Navigation"
    >
      <SidebarGroupLabel>
        Chat History
      </SidebarGroupLabel>

      <SidebarMenu>
        <SidebarMenuItem v-for="historyItem in chatHistory.data" :key="`chat-${historyItem.id}`">
          <SidebarMenuButton
            as-child :class="{
              'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
            }" :tooltip="historyItem.title"
          >
            <Link
              prefetch :href="route('chats.show', historyItem.id)"
              :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
            >
              <span class="truncate">{{ historyItem.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>

      <WhenVisible
        v-if="hasMorePages" :params="{
          preserveUrl: true,
          data: { page: props.chatHistory.current_page + 1 },
          only: ['chatHistory'],
        }" :always="hasMorePages"
      >
        <template #fallback>
          <SidebarGroupLabel class="mt-2" role="status" aria-live="polite">
            <div>Loading more chats...</div>
          </SidebarGroupLabel>
        </template>
      </WhenVisible>

      <SidebarGroupLabel class="mt-2 text-muted-foreground" role="status">
        You have reached the end of your chat history.
      </SidebarGroupLabel>
    </SidebarGroup>
  </div>
</template>
