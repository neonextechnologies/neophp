# Storage & Filesystem

Complete reference for file storage and filesystem operations.

## Storage

Storage manager and facade.

### Basic Operations

#### `disk($name = null)`

Get disk instance.

```php
$local = Storage::disk('local');
$s3 = Storage::disk('s3');
$public = Storage::disk('public');
```

#### `exists($path)`

Check if file exists.

```php
if (Storage::exists('file.txt')) {
    // File exists
}
```

#### `missing($path)`

Check if file doesn't exist.

```php
if (Storage::missing('config.json')) {
    // File doesn't exist
}
```

#### `get($path)`

Get file contents.

```php
$content = Storage::get('file.txt');
```

#### `put($path, $contents, $visibility = null)`

Store file.

```php
Storage::put('file.txt', 'Contents');
Storage::put('file.txt', $contents, 'public');
```

#### `putFile($path, $file, $visibility = null)`

Store uploaded file.

```php
$path = Storage::putFile('avatars', $request->file('avatar'));
$path = Storage::putFile('documents', $file, 'public');
```

#### `putFileAs($path, $file, $name, $visibility = null)`

Store uploaded file with name.

```php
$path = Storage::putFileAs('avatars', $request->file('avatar'), 'user-123.jpg');
```

#### `delete($paths)`

Delete file(s).

```php
Storage::delete('file.txt');
Storage::delete(['file1.txt', 'file2.txt']);
```

#### `copy($from, $to)`

Copy file.

```php
Storage::copy('old.txt', 'new.txt');
```

#### `move($from, $to)`

Move file.

```php
Storage::move('old-location.txt', 'new-location.txt');
```

### Directory Operations

#### `files($directory = null, $recursive = false)`

List files.

```php
$files = Storage::files('uploads');
$allFiles = Storage::files('uploads', true);
```

#### `allFiles($directory = null)`

List all files recursively.

```php
$files = Storage::allFiles('uploads');
```

#### `directories($directory = null, $recursive = false)`

List directories.

```php
$dirs = Storage::directories('uploads');
```

#### `allDirectories($directory = null)`

List all directories recursively.

```php
$dirs = Storage::allDirectories('uploads');
```

#### `makeDirectory($path)`

Create directory.

```php
Storage::makeDirectory('uploads/documents');
```

#### `deleteDirectory($directory)`

Delete directory.

```php
Storage::deleteDirectory('temp');
```

### File Information

#### `size($path)`

Get file size in bytes.

```php
$bytes = Storage::size('file.txt');
```

#### `lastModified($path)`

Get last modified timestamp.

```php
$timestamp = Storage::lastModified('file.txt');
```

#### `mimeType($path)`

Get MIME type.

```php
$mime = Storage::mimeType('image.jpg');
```

#### `path($path)`

Get absolute path.

```php
$fullPath = Storage::path('file.txt');
```

#### `url($path)`

Get public URL.

```php
$url = Storage::url('avatars/user.jpg');
```

#### `temporaryUrl($path, $expiration, $options = [])`

Get temporary URL (S3).

```php
$url = Storage::temporaryUrl(
    'documents/private.pdf',
    now()->addMinutes(30)
);
```

### Visibility

#### `getVisibility($path)`

Get file visibility.

```php
$visibility = Storage::getVisibility('file.txt'); // 'public' or 'private'
```

#### `setVisibility($path, $visibility)`

Set file visibility.

```php
Storage::setVisibility('file.txt', 'public');
Storage::setVisibility('private.txt', 'private');
```

### Streaming

#### `download($path, $name = null, $headers = [])`

Download file.

```php
return Storage::download('documents/report.pdf');
return Storage::download('documents/report.pdf', 'Monthly-Report.pdf');
```

#### `readStream($path)`

Get file stream.

```php
$stream = Storage::readStream('large-file.dat');
```

#### `writeStream($path, $resource, $options = [])`

Write stream to file.

```php
$stream = fopen('local-file.txt', 'r');
Storage::writeStream('remote-file.txt', $stream);
fclose($stream);
```

---

## Disk Configurations

### Local Disk

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
]
```

### Public Disk

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL') . '/storage',
        'visibility' => 'public',
    ],
]
```

### S3 Disk

```php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
    ],
]
```

### FTP Disk

```php
'disks' => [
    'ftp' => [
        'driver' => 'ftp',
        'host' => env('FTP_HOST'),
        'username' => env('FTP_USERNAME'),
        'password' => env('FTP_PASSWORD'),
        'port' => env('FTP_PORT', 21),
        'root' => env('FTP_ROOT', '/'),
        'passive' => true,
    ],
]
```

---

## Practical Examples

### Avatar Upload

```php
class ProfileController
{
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048'
        ]);
        
        $user = $request->user();
        
        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);
        
        return back()->with('success', 'Avatar updated!');
    }
}
```

### Document Upload & Management

```php
class DocumentController
{
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        $file = $request->file('document');
        $filename = time() . '-' . $file->getClientOriginalName();
        
        $path = Storage::disk('s3')->putFileAs(
            'documents/' . auth()->id(),
            $file,
            $filename,
            'private'
        );
        
        $document = Document::create([
            'user_id' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);
        
        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully!');
    }
    
    public function download(Document $document)
    {
        $this->authorize('download', $document);
        
        return Storage::disk('s3')->download(
            $document->path,
            $document->name
        );
    }
    
    public function temporaryUrl(Document $document)
    {
        $this->authorize('view', $document);
        
        $url = Storage::disk('s3')->temporaryUrl(
            $document->path,
            now()->addMinutes(30)
        );
        
        return response()->json(['url' => $url]);
    }
}
```

### Image Processing

```php
class ImageService
{
    public function processUpload($file, $folder = 'images')
    {
        // Store original
        $originalPath = Storage::disk('public')->putFile($folder, $file);
        
        // Create thumbnail
        $image = Image::make($file);
        $image->fit(200, 200);
        
        $thumbnailPath = $folder . '/thumbnails/' . basename($originalPath);
        Storage::disk('public')->put($thumbnailPath, (string) $image->encode());
        
        // Create medium size
        $image = Image::make($file);
        $image->fit(800, 600);
        
        $mediumPath = $folder . '/medium/' . basename($originalPath);
        Storage::disk('public')->put($mediumPath, (string) $image->encode());
        
        return [
            'original' => $originalPath,
            'thumbnail' => $thumbnailPath,
            'medium' => $mediumPath
        ];
    }
    
    public function deleteImages($paths)
    {
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
```

### CSV Export

```php
class ExportService
{
    public function exportUsers()
    {
        $filename = 'users-export-' . date('Y-m-d-His') . '.csv';
        $filepath = 'exports/' . $filename;
        
        $handle = fopen('php://temp', 'r+');
        
        // Headers
        fputcsv($handle, ['ID', 'Name', 'Email', 'Created At']);
        
        // Data
        User::chunk(1000, function($users) use ($handle) {
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s')
                ]);
            }
        });
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        Storage::put($filepath, $content);
        
        return $filepath;
    }
    
    public function downloadExport($filepath)
    {
        if (!Storage::exists($filepath)) {
            abort(404);
        }
        
        return Storage::download($filepath);
    }
}
```

### File Backup

```php
class BackupService
{
    public function backup($source, $destination)
    {
        $files = Storage::disk($source)->allFiles();
        
        foreach ($files as $file) {
            $content = Storage::disk($source)->get($file);
            Storage::disk($destination)->put($file, $content);
        }
        
        return count($files);
    }
    
    public function backupDatabase()
    {
        $filename = 'database-backup-' . date('Y-m-d-His') . '.sql';
        
        // Generate database dump
        $dump = $this->generateDatabaseDump();
        
        // Store locally
        Storage::put('backups/' . $filename, $dump);
        
        // Upload to S3
        Storage::disk('s3')->put('backups/' . $filename, $dump);
        
        // Delete old backups (keep last 30 days)
        $this->deleteOldBackups(30);
        
        return $filename;
    }
    
    protected function deleteOldBackups($days)
    {
        $files = Storage::disk('s3')->files('backups');
        $cutoff = now()->subDays($days)->timestamp;
        
        foreach ($files as $file) {
            $modified = Storage::disk('s3')->lastModified($file);
            
            if ($modified < $cutoff) {
                Storage::disk('s3')->delete($file);
            }
        }
    }
}
```

### Temporary Files

```php
class TemporaryFileService
{
    public function create($content, $extension = 'tmp')
    {
        $filename = 'temp/' . uniqid() . '.' . $extension;
        
        Storage::put($filename, $content);
        
        return $filename;
    }
    
    public function cleanup()
    {
        $files = Storage::files('temp');
        $cutoff = now()->subHours(24)->timestamp;
        
        foreach ($files as $file) {
            if (Storage::lastModified($file) < $cutoff) {
                Storage::delete($file);
            }
        }
    }
}
```

### Multi-Disk Upload

```php
class MultiDiskUploadService
{
    public function uploadToMultipleDisks($file, $path)
    {
        $disks = ['local', 's3', 'backup'];
        $results = [];
        
        foreach ($disks as $disk) {
            try {
                $fullPath = Storage::disk($disk)->putFile($path, $file);
                $results[$disk] = [
                    'success' => true,
                    'path' => $fullPath
                ];
            } catch (\Exception $e) {
                $results[$disk] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}
```

### Chunked Upload

```php
class ChunkedUploadController
{
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'chunk' => 'required|integer',
            'chunks' => 'required|integer',
            'filename' => 'required|string'
        ]);
        
        $chunk = $request->input('chunk');
        $chunks = $request->input('chunks');
        $filename = $request->input('filename');
        
        $tempDir = 'temp/chunks/' . md5($filename);
        Storage::makeDirectory($tempDir);
        
        // Store chunk
        $chunkFilename = "{$tempDir}/{$chunk}.part";
        $request->file('file')->storeAs($tempDir, "{$chunk}.part");
        
        // Check if all chunks uploaded
        $uploadedChunks = count(Storage::files($tempDir));
        
        if ($uploadedChunks === $chunks) {
            // Merge chunks
            $finalPath = $this->mergeChunks($tempDir, $filename, $chunks);
            
            // Cleanup
            Storage::deleteDirectory($tempDir);
            
            return response()->json([
                'success' => true,
                'path' => $finalPath
            ]);
        }
        
        return response()->json([
            'success' => true,
            'uploaded' => $uploadedChunks,
            'total' => $chunks
        ]);
    }
    
    protected function mergeChunks($tempDir, $filename, $chunks)
    {
        $finalPath = 'uploads/' . $filename;
        $handle = fopen(Storage::path($finalPath), 'wb');
        
        for ($i = 0; $i < $chunks; $i++) {
            $chunkPath = "{$tempDir}/{$i}.part";
            $chunkContent = Storage::get($chunkPath);
            fwrite($handle, $chunkContent);
        }
        
        fclose($handle);
        
        return $finalPath;
    }
}
```

---

## Best Practices

### File Naming

```php
// Good - unique and organized
$path = Storage::putFileAs(
    'uploads/' . auth()->id(),
    $file,
    time() . '-' . Str::slug($file->getClientOriginalName())
);

// Bad - potential collisions
Storage::put('file.txt', $content);
```

### Security

```php
// Validate file uploads
$request->validate([
    'file' => 'required|file|mimes:jpg,png,pdf|max:10240'
]);

// Check file MIME type
$mimeType = $file->getMimeType();
if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
    abort(422, 'Invalid file type');
}

// Store with private visibility
Storage::putFile('private-documents', $file, 'private');
```

### Error Handling

```php
try {
    Storage::disk('s3')->put('file.txt', $content);
} catch (\Exception $e) {
    Log::error('Storage error', [
        'error' => $e->getMessage(),
        'file' => 'file.txt'
    ]);
    
    // Fallback to local
    Storage::disk('local')->put('file.txt', $content);
}
```

### Cleanup

```php
// Delete old files
$files = Storage::files('temp');
foreach ($files as $file) {
    if (Storage::lastModified($file) < now()->subDay()->timestamp) {
        Storage::delete($file);
    }
}

// Delete related files when model deleted
class Document extends Model
{
    protected static function booted()
    {
        static::deleted(function($document) {
            Storage::disk('s3')->delete($document->path);
        });
    }
}
```

---

## Next Steps

- [File Uploads Guide](../core-concepts/file-uploads.md)
- [Image Processing](../advanced/image-processing.md)
- [S3 Integration](../advanced/s3-integration.md)
- [Security Best Practices](../advanced/security.md)
