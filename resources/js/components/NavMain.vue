<script setup lang="ts">
import type { ChatHistory, SharedData } from '@/types'
import { Icon } from '@iconify/vue'
import { Link, usePage, WhenVisible } from '@inertiajs/vue3'
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar'
import { useChatHistory } from '@/composables/useChatHistory'

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

const { groupedChatHistory, hasMorePages, hasAnyHistory } = useChatHistory(props.chatHistory)

const mainMenuItems = [
  {
    label: 'New Chat',
    icon: 'lucide:notebook-pen',
    href: route('chats.index'),
  },
  {
    label: 'View on GitHub',
    icon: 'lucide:github',
    href: 'https://github.com/pushpak1300/ai-chat',
    target: '_blank',
    external: true,
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
            v-for="item in mainMenuItems" :key="item.label"
            class="flex items-center font-bold"
          >
            <a :href="item.href" :target="item.target" :aria-label="item.label" class="flex items-center">
              <Icon :icon="item.icon" class="w-4 h-4" />
              <span class="ml-2">
                {{ item.label }}
              </span>
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>

    <div v-if="hasAnyHistory" role="navigation" aria-label="Chat History Navigation">
      <SidebarGroup v-if="groupedChatHistory.today.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Today</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="historyItem in groupedChatHistory.today" :key="`chat-${historyItem.id}`">
            <SidebarMenuButton
              as-child :class="{
                'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
              }" :tooltip="historyItem.title"
            >
              <Link
                :prefetch="['mount']" :cache-for="['30s', '1m']" :href="route('chats.show', historyItem.id)"
                :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
              >
                <span class="truncate">{{ historyItem.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <SidebarGroup v-if="groupedChatHistory.yesterday.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Yesterday</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem
            v-for="historyItem in groupedChatHistory.yesterday"
            :key="`chat-${historyItem.id}`"
          >
            <SidebarMenuButton
              as-child :class="{
                'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
              }" :tooltip="historyItem.title"
            >
              <Link
                :prefetch="['mount', 'hover']" cache-for="1m"
                :href="route('chats.show', historyItem.id)"
                :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
              >
                <span class="truncate">{{ historyItem.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <SidebarGroup v-if="groupedChatHistory.lastSevenDays.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Last 7 Days</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem
            v-for="historyItem in groupedChatHistory.lastSevenDays"
            :key="`chat-${historyItem.id}`"
          >
            <SidebarMenuButton
              as-child :class="{
                'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
              }" :tooltip="historyItem.title"
            >
              <Link
                :prefetch="['mount', 'hover']" cache-for="1m"
                :href="route('chats.show', historyItem.id)"
                :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
              >
                <span class="truncate">{{ historyItem.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <SidebarGroup v-if="groupedChatHistory.lastThirtyDays.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Last 30 Days</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem
            v-for="historyItem in groupedChatHistory.lastThirtyDays"
            :key="`chat-${historyItem.id}`"
          >
            <SidebarMenuButton
              as-child :class="{
                'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
              }" :tooltip="historyItem.title"
            >
              <Link
                :prefetch="['mount', 'hover']" cache-for="1m"
                :href="route('chats.show', historyItem.id)"
                :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
              >
                <span class="truncate">{{ historyItem.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <SidebarGroup v-if="groupedChatHistory.older.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Older</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="historyItem in groupedChatHistory.older" :key="`chat-${historyItem.id}`">
            <SidebarMenuButton
              as-child :class="{
                'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url,
              }" :tooltip="historyItem.title"
            >
              <Link
                :prefetch="['mount', 'hover']" cache-for="1m"
                :href="route('chats.show', historyItem.id)"
                :aria-label="`Open chat: ${historyItem.title}`" class="block w-full"
              >
                <span class="truncate">{{ historyItem.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

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
    </div>
  </div>
</template>
