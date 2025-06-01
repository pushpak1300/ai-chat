# Chat UI for Vue.js with shadcn/vue

This is a complete chat UI implementation built with Vue 3, TypeScript, and shadcn/vue components, based on the Next.js chat interface you provided.

## Features

- 🎨 Modern, responsive chat interface
- 💬 Real-time message display with user and assistant roles
- ✨ Smooth animations and transitions
- 📎 File attachment support (placeholder)
- 🎯 Suggested actions for quick prompts
- 🔄 Message actions (copy, upvote, downvote)
- 🎛️ Model and visibility selectors
- 📱 Mobile-responsive design
- 🌙 Dark mode support (via shadcn/vue)

## Components Structure

```
resources/js/components/chat/
├── ChatContainer.vue          # Main chat container
├── ChatHeader.vue            # Header with controls
├── Messages.vue              # Messages container
├── Message.vue               # Individual message
├── MultimodalInput.vue       # Input with attachments
├── SuggestedActions.vue      # Quick action buttons
├── Greeting.vue              # Welcome message
├── ThinkingMessage.vue       # Loading state
├── MessageActions.vue        # Message interaction buttons
├── MarkdownRenderer.vue      # Simple markdown rendering
├── ModelSelector.vue         # AI model selection
├── VisibilitySelector.vue    # Chat visibility settings
└── SidebarToggle.vue         # Sidebar toggle button

resources/js/components/icons/
├── SparklesIcon.vue          # AI assistant icon
├── PlusIcon.vue              # Add/new chat icon
├── ArrowDownIcon.vue         # Scroll indicator
├── CopyIcon.vue              # Copy message icon
├── ThumbUpIcon.vue           # Upvote icon
├── ThumbDownIcon.vue         # Downvote icon
├── ChevronDownIcon.vue       # Dropdown indicator
├── CheckCircleFillIcon.vue   # Selection indicator
├── LockIcon.vue              # Private visibility
├── GlobeIcon.vue             # Public visibility
└── SidebarLeftIcon.vue       # Sidebar toggle
```

## Usage

### Basic Implementation

```vue
<template>
  <div class="min-h-screen bg-background">
    <ChatContainer
      :chat-id="chatId"
      :initial-messages="initialMessages"
      :initial-chat-model="initialChatModel"
      :initial-visibility-type="initialVisibilityType"
      :is-readonly="false"
      :session="session"
    />
  </div>
</template>

<script setup lang="ts">
import ChatContainer from '@/components/chat/ChatContainer.vue'

const chatId = ref('')
const initialChatModel = ref('gpt-4')
const initialVisibilityType = ref<'private' | 'public'>('private')

const session = ref({
  user: {
    id: '1',
    name: 'Demo User',
    email: 'demo@example.com'
  }
})

const initialMessages = ref([
  {
    id: '1',
    role: 'assistant',
    content: 'Hello! How can I help you today?',
    createdAt: new Date()
  }
])
</script>
```

### Integration with Laravel

1. **Create a route** in `routes/web.php`:
```php
Route::get('/chat/{id?}', function ($id = null) {
    return Inertia::render('Chat', [
        'chatId' => $id ?? 'new-chat',
        'initialMessages' => [],
        'initialChatModel' => 'gpt-4',
        'initialVisibilityType' => 'private',
        'session' => auth()->user()
    ]);
})->name('chat');
```

2. **Create the page** in `resources/js/pages/Chat.vue`:
```vue
<template>
  <div class="min-h-screen bg-background">
    <ChatContainer
      :chat-id="chatId"
      :initial-messages="initialMessages"
      :initial-chat-model="initialChatModel"
      :initial-visibility-type="initialVisibilityType"
      :is-readonly="false"
      :session="session"
    />
  </div>
</template>

<script setup lang="ts">
import ChatContainer from '@/components/chat/ChatContainer.vue'

interface Props {
  chatId: string
  initialMessages: Array<any>
  initialChatModel: string
  initialVisibilityType: 'private' | 'public'
  session: any
}

defineProps<Props>()
</script>
```

## Customization

### Styling
The components use Tailwind CSS classes and shadcn/vue design tokens. You can customize:

- Colors via CSS variables in your `app.css`
- Component spacing and sizing via Tailwind classes
- Dark mode behavior via the `dark:` prefix classes

### Message Types
Extend the message interface to support different content types:

```typescript
interface Message {
  id: string
  role: 'user' | 'assistant'
  content: string
  attachments?: Array<Attachment>
  createdAt: Date
  // Add custom fields
  type?: 'text' | 'code' | 'image'
  metadata?: Record<string, any>
}
```

### AI Integration
Replace the placeholder AI response logic in `ChatContainer.vue`:

```typescript
const handleSubmit = async () => {
  if (input.value.trim() && status.value === 'ready') {
    status.value = 'streaming'
    
    // Add user message
    messages.value.push({
      id: Date.now().toString(),
      role: 'user',
      content: input.value,
      createdAt: new Date()
    })
    
    const userMessage = input.value
    input.value = ''
    
    try {
      // Replace with your AI API call
      const response = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: userMessage,
          chatId: props.chatId,
          model: selectedModelId.value
        })
      })
      
      const data = await response.json()
      
      messages.value.push({
        id: data.id,
        role: 'assistant',
        content: data.content,
        createdAt: new Date()
      })
    } catch (error) {
      console.error('Failed to get AI response:', error)
    } finally {
      status.value = 'ready'
    }
  }
}
```

## Dependencies

Make sure you have these dependencies installed:

```json
{
  "@vueuse/core": "^12.8.2",
  "class-variance-authority": "^0.7.1",
  "clsx": "^2.1.1",
  "tailwind-merge": "^3.3.0"
}
```

## Next Steps

1. **Add real AI integration** - Connect to OpenAI, Anthropic, or your preferred AI service
2. **Implement file uploads** - Add actual file upload functionality
3. **Add message persistence** - Save messages to database
4. **Real-time updates** - Use WebSockets or Server-Sent Events for live updates
5. **Add more message types** - Support for code blocks, images, etc.
6. **Implement search** - Add message search functionality
7. **Add chat history** - Implement sidebar with previous conversations

## Notes

- This implementation focuses on the UI/UX and provides placeholders for backend integration
- The markdown renderer is basic - consider using a proper markdown library like `marked` or `markdown-it`
- File upload functionality needs backend API endpoints
- Message voting/rating needs backend persistence
- The sidebar toggle currently just logs - implement actual sidebar functionality

The chat UI is designed to be modular and extensible, making it easy to integrate with your existing Laravel application and AI services.
