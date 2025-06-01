import { ref, watch, onUnmounted, nextTick } from 'vue'

type ScrollFlag = ScrollBehavior | false

export function useScrollToBottom() {
  const containerRef = ref<HTMLElement>()
  const endRef = ref<HTMLElement>()
  const isAtBottom = ref(false)
  const scrollBehavior = ref<ScrollFlag>(false)

  let observer: IntersectionObserver | null = null

  const setupObserver = () => {
    if (endRef.value && !observer) {
      observer = new IntersectionObserver(
        (entries) => {
          const entry = entries[0]
          if (entry.isIntersecting) {
            onViewportEnter()
          } else {
            onViewportLeave()
          }
        },
        {
          root: containerRef.value || null,
          threshold: 0.1
        }
      )

      observer.observe(endRef.value)
    }
  }

  const scrollToBottom = (behavior: ScrollBehavior = 'smooth') => {
    scrollBehavior.value = behavior
  }

  const onViewportEnter = () => {
    isAtBottom.value = true
  }

  const onViewportLeave = () => {
    isAtBottom.value = false
  }

  watch(scrollBehavior, (newBehavior) => {
    if (newBehavior && endRef.value) {
      endRef.value.scrollIntoView({ behavior: newBehavior })
      scrollBehavior.value = false
    }
  })

  watch(endRef, () => {
    if (endRef.value) {
      nextTick(() => {
        setupObserver()
      })
    }
  })

  onUnmounted(() => {
    if (observer) {
      observer.disconnect()
    }
  })

  const scrollToBottomInstant = () => {
    scrollToBottom('auto')
  }

  const scrollToBottomSmooth = () => {
    scrollToBottom('smooth')
  }

  return {
    containerRef,
    endRef,
    isAtBottom,
    scrollToBottom,
    scrollToBottomInstant,
    scrollToBottomSmooth,
    onViewportEnter,
    onViewportLeave
  }
}
