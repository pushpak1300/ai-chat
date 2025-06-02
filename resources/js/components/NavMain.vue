<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { ChatHistory , type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { WhenVisible } from '@inertiajs/vue3';

withDefaults(defineProps<{
    chatHistory: ChatHistory;
}>(), {
    'chatHistory': () => ({
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
});
const page = usePage<SharedData>()
</script>

<template>
    <SidebarGroup class="px-2 py-0" v-if="chatHistory?.data?.length > 0">
        <SidebarGroupLabel>
            Chat History
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="historyItem in chatHistory.data" :key="historyItem.id">
                <SidebarMenuButton as-child :class="{ 'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url }"
                    :tooltip="historyItem.title">
                    <Link prefetch :href="route('chats.show', historyItem.id)">
                    <span>{{ historyItem.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
        <WhenVisible :params="{
            preserveUrl: true,
            data: {
                page: chatHistory?.current_page + 1,
            },
            only: ['chatHistory'],
        }" :always="chatHistory.next_page_url !== null">
            <template #fallback>
                <SidebarGroupLabel class="mt-2">
                    <div>Loading...</div>
                </SidebarGroupLabel>
            </template>
        </WhenVisible>
        <SidebarGroupLabel class="mt-2">
            You have reached the end of your chat history.
        </SidebarGroupLabel>
    </SidebarGroup>
</template>
