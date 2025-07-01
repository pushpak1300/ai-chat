<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('FileUploadController', function (): void {
    it('uploads file successfully for authenticated user', function (): void {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('test-document.pdf', 1024);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/file/upload', [
                'file' => $file,
            ]);
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'filename',
                'stored_filename',
                'path',
                'url',
                'size',
                'type',
            ])
            ->assertJson([
                'success' => true,
                'filename' => 'test-document.pdf',
            ]);
        
        $data = $response->json();
        Storage::disk('public')->assertExists($data['path']);
    });
    
    it('uploads image file successfully', function (): void {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test-image.jpg');
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/file/upload', [
                'file' => $file,
            ]);
        
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'filename' => 'test-image.jpg',
            ]);
        
        $data = $response->json();
        Storage::disk('public')->assertExists($data['path']);
        expect($data['type'])->toContain('image');
    });
    
    it('rejects file that is too large', function (): void {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('large-file.pdf', 11 * 1024); // 11MB
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/file/upload', [
                'file' => $file,
            ]);
        
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });
    
    it('rejects unsupported file type', function (): void {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('test.exe', 1024);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/file/upload', [
                'file' => $file,
            ]);
        
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });
    
    it('requires authentication', function (): void {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('test-document.pdf', 1024);
        
        $response = $this->postJson('/api/file/upload', [
            'file' => $file,
        ]);
        
        $response->assertUnauthorized();
    });
    
    it('requires file to be present', function (): void {
        $response = $this->actingAs($this->user)
            ->postJson('/api/file/upload', []);
        
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });
    
    it('generates unique filename for files with same name', function (): void {
        Storage::fake('public');
        
        $file1 = UploadedFile::fake()->create('test.pdf', 1024);
        $file2 = UploadedFile::fake()->create('test.pdf', 1024);
        
        $response1 = $this->actingAs($this->user)
            ->postJson('/api/file/upload', ['file' => $file1]);
        
        $response2 = $this->actingAs($this->user)
            ->postJson('/api/file/upload', ['file' => $file2]);
        
        $response1->assertOk();
        $response2->assertOk();
        
        $data1 = $response1->json();
        $data2 = $response2->json();
        
        expect($data1['stored_filename'])->not->toBe($data2['stored_filename']);
        expect($data1['filename'])->toBe('test.pdf');
        expect($data2['filename'])->toBe('test.pdf');
    });
});