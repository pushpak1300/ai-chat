<script setup lang="ts">
import type { ChatHistory } from '@/types'
import { Link } from '@inertiajs/vue3'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar'
import AppLogo from './AppLogo.vue'

defineProps<{
  chatHistory?: ChatHistory | null
  chatNextPage?: number | null
}>()
</script>

<template>
  <Sidebar collapsible="offcanvas" variant="sidebar">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('chats.index')">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :chat-history="chatHistory" :chat-next-page="chatNextPage" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
