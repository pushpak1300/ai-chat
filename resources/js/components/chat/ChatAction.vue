<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { router, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
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
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'

const chatId = inject<string | null>('chatId', null)
const deleteForm = useForm({})

function deleteChat(chatId?: string) {
  if (!chatId) {
    return
  }

  deleteForm.delete(route('chats.destroy', chatId), {
    preserveScroll: true,
    onSuccess: () => {
      router.flushAll()
    },
  })
}
</script>

<template>
  <AlertDialog v-if="chatId">
    <AlertDialogTrigger as-child>
      <Button variant="ghost" class="h-8 w-8 hover:text-destructive">
        <Icon icon="lucide:trash-2" class="h-4 w-4" />
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete
          the chat and remove all its messages from our servers.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction
          class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
          :disabled="deleteForm.processing"
          @click="deleteChat(chatId)"
        >
          <Icon
            v-if="deleteForm.processing"
            icon="lucide:loader-2"
            class="h-4 w-4 mr-2 animate-spin"
          />
          {{ deleteForm.processing ? "Deleting..." : "Delete Chat" }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
