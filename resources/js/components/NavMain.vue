<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { ChatHistory , type SharedData } from '@/types';
import { Link, usePage, router } from '@inertiajs/vue3';
import { WhenVisible } from '@inertiajs/vue3';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Icon } from '@iconify/vue';
import { Button } from '@/components/ui/button';

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

const deleteChat = (chatId: number) => {
    router.delete(route('chats.destroy', chatId), {
        preserveScroll: true,
        onSuccess: () => {
            // If we're currently viewing the deleted chat, redirect to index
            if (route('chats.show', chatId, false) === page.url) {
                router.visit(route('chats.index'));
            }
        },
    });
};
</script>

<template>
    <SidebarGroup class="px-2 py-0" v-if="chatHistory?.data?.length > 0">
        <SidebarGroupLabel>
            Chat History
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="historyItem in chatHistory.data" :key="historyItem.id">
                <div class="flex items-center w-full group">
                    <SidebarMenuButton as-child :class="{ 'bg-secondary text-secondary-foreground': route('chats.show', historyItem.id, false) === page.url }"
                        :tooltip="historyItem.title" class="flex-1 mr-1">
                        <Link prefetch :href="route('chats.show', historyItem.id)">
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
                </div>
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
