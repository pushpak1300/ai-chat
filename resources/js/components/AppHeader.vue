<script setup lang="ts">
import type { BreadcrumbItem, NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import { LayoutGrid, Menu, Search } from 'lucide-vue-next'
import { computed } from 'vue'
import AppLogo from '@/components/AppLogo.vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { Button } from '@/components/ui/button'
import {
  NavigationMenu,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'

interface Props {
  breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

const page = usePage()

const isCurrentRoute = computed(() => (url: string) => page.url === url)

const activeItemStyles = computed(
  () => (url: string) =>
    isCurrentRoute.value(url)
      ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
      : '',
)

const mainNavItems: NavItem[] = [
  {
    title: 'Chat',
    href: '/chat',
    icon: LayoutGrid,
  },
]
</script>

<template>
  <div>
    <div class="border-b border-sidebar-border/80">
      <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
        <div class="lg:hidden">
          <Sheet>
            <SheetTrigger :as-child="true">
              <Button
                variant="ghost"
                size="icon"
                class="mr-2 h-9 w-9"
              >
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[300px] p-6">
              <SheetTitle class="sr-only">
                Navigation Menu
              </SheetTitle>
              <SheetHeader class="flex justify-start text-left">
                <AppLogoIcon
                  class="size-6 fill-current text-black dark:text-white"
                />
              </SheetHeader>
              <div
                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
              >
                <nav class="-mx-3 space-y-1">
                  <Link
                    v-for="item in mainNavItems"
                    :key="item.title"
                    :href="item.href"
                    class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                    :class="activeItemStyles(item.href)"
                  >
                    <component
                      :is="item.icon"
                      v-if="item.icon"
                      class="h-5 w-5"
                    />
                    {{ item.title }}
                  </Link>
                </nav>
              </div>
            </SheetContent>
          </Sheet>
        </div>

        <Link :href="route('chat')" class="flex items-center gap-x-2">
          <AppLogo />
        </Link>

        <!-- Desktop Menu -->
        <div class="hidden h-full lg:flex lg:flex-1">
          <NavigationMenu class="ml-10 flex h-full items-stretch">
            <NavigationMenuList
              class="flex h-full items-stretch space-x-2"
            >
              <NavigationMenuItem
                v-for="(item, index) in mainNavItems"
                :key="index"
                class="relative flex h-full items-center"
              >
                <Link :href="item.href">
                  <NavigationMenuLink
                    class="h-9 cursor-pointer px-3"
                    :class="[
                      navigationMenuTriggerStyle(),
                      activeItemStyles(item.href),
                    ]"
                  >
                    <component
                      :is="item.icon"
                      v-if="item.icon"
                      class="mr-2 h-4 w-4"
                    />
                    {{ item.title }}
                  </NavigationMenuLink>
                </Link>
                <div
                  v-if="isCurrentRoute(item.href)"
                  class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                />
              </NavigationMenuItem>
            </NavigationMenuList>
          </NavigationMenu>
        </div>

        <div class="ml-auto flex items-center space-x-2">
          <div class="relative flex items-center space-x-1">
            <Button
              variant="ghost"
              size="icon"
              class="group h-9 w-9 cursor-pointer"
            >
              <Search
                class="size-5 opacity-80 group-hover:opacity-100"
              />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="props.breadcrumbs.length > 1"
      class="flex w-full border-b border-sidebar-border/70"
    >
      <div
        class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
      >
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </div>
    </div>
  </div>
</template>
