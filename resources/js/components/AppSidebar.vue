<script setup lang="ts">
import type { ChatHistory } from '@/types'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar'
import AppLogo from './AppLogo.vue'
import { Link } from '@inertiajs/vue3'

defineProps<{
  chatHistory?: ChatHistory
}>()
</script>

<template>
  <Sidebar collapsible="offcanvas" variant="sidebar">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child class="cursor-default hover:bg-transparent focus:bg-transparent pointer-events-none">
            <Link :disabled="true" :href="route('chats.index')">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :chat-history="chatHistory" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
