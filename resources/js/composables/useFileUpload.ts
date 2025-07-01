import { ref } from 'vue'

interface UploadResponse {
  success: boolean
  filename: string
  stored_filename: string
  path: string
  url: string
  size: number
  type: string
  message?: string
}

export function useFileUpload() {
  const uploadQueue = ref<string[]>([])
  const uploadedFiles = ref<UploadResponse[]>([])
  const isUploading = ref(false)

  async function uploadFile(file: File): Promise<UploadResponse | null> {
    const formData = new FormData()
    formData.append('file', file)

    try {
      isUploading.value = true
      uploadQueue.value.push(file.name)

      const response = await fetch('/api/file/upload', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Accept': 'application/json',
        },
      })

      const result: UploadResponse = await response.json()

      if (result.success) {
        uploadedFiles.value.push(result)
        return result
      } else {
        console.error('Upload failed:', result.message)
        return null
      }
    } catch (error) {
      console.error('Upload error:', error)
      return null
    } finally {
      // Remove from upload queue
      const index = uploadQueue.value.indexOf(file.name)
      if (index > -1) {
        uploadQueue.value.splice(index, 1)
      }
      isUploading.value = false
    }
  }

  function removeUploadedFile(filename: string) {
    const index = uploadedFiles.value.findIndex(file => file.filename === filename)
    if (index > -1) {
      uploadedFiles.value.splice(index, 1)
    }
  }

  function clearUploads() {
    uploadedFiles.value = []
    uploadQueue.value = []
  }

  function getAttachmentFilenames(): string[] {
    return uploadedFiles.value.map(file => file.filename)
  }

  return {
    uploadQueue,
    uploadedFiles,
    isUploading,
    uploadFile,
    removeUploadedFile,
    clearUploads,
    getAttachmentFilenames,
  }
}