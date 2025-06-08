import type { Message } from '@/types'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { toast } from 'vue-sonner'

export function useMessageVoting(message: Message, chatId?: string) {
  const form = useForm({
    message_id: message.id,
    is_upvoted: message.is_upvoted,
  })

  const isUpvoted = computed(() => form.is_upvoted === true)
  const isDownvoted = computed(() => form.is_upvoted === false)
  const isProcessing = computed(() => form.processing)

  function voteMessage(isUpvote: boolean) {
    const action = isUpvote ? 'upvote' : 'downvote'
    const previousState = form.is_upvoted

    form.is_upvoted = isUpvote

    const promise = new Promise((resolve, reject) => {
      form.patch(route('chats.update', { chat: chatId }), {
        async: true,
        onSuccess: () => resolve({ action }),
        onError: () => {
          form.is_upvoted = previousState
          reject(new Error(`Failed to ${action}`))
        },
      })
    })

    toast.promise(promise, {
      loading: `${action === 'upvote' ? 'Upvoting' : 'Downvoting'} response...`,
      success: `Response ${action}d!`,
      error: `Failed to ${action}. Please try again`,
    })
  }

  const upvoteMessage = () => voteMessage(true)
  const downvoteMessage = () => voteMessage(false)

  return {
    isUpvoted,
    isDownvoted,
    isProcessing,
    upvoteMessage,
    downvoteMessage,
  }
}
