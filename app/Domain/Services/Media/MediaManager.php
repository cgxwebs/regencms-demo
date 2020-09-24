<?php


namespace App\Domain\Services\Media;


use App\Http\Requests\MediaEditRequest;
use App\Media;
use App\User;
use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class MediaManager
{
    private Connection $db;
    private Filesystem $storage;
    private string $media_dir = '';

    public function __construct(Connection $db, Filesystem $storage)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->media_dir = config('regencms.media_dir');
    }

    public function checkStorageDirectories()
    {
        if (false === is_readable(storage_path('app/'.$this->media_dir)) ||
            false === is_writable(storage_path('app/'.$this->media_dir))) {
            throw new RuntimeException('Storage directory not ready.');
        }

        if (false === is_readable(public_path($this->media_dir)) ||
            false === is_writable(public_path($this->media_dir))) {
            throw new RuntimeException('Public directory not ready.');
        }
    }

    public function storeFile(UploadedFile $file, User $user): ?Media
    {
        list('subdir' => $sub_dir, 'filename' => $filename) = $this->generateFilename($file);
        $upload_path = $this->media_dir . $sub_dir;

        if ($file->isExecutable() || !$file->isValid()) {
            throw new InvalidArgumentException("Malicious file has been uploaded.");
        }

        try {
            $file_path = $file->storePubliclyAs($upload_path, $filename);

            $media = Media::create([
                'user_id' => $user->id,
                'filepath' => $file_path,
                'filetype' => $file->getMimeType(),
                'filesize' => $this->generateFileSize($file),
                'description' => $this->generateDesc($file),
                'parent' => '0'
            ]);
        } catch(Throwable $t) {
            if ($this->storage->exists($file_path)) {
                $this->storage->delete($file_path);
            }
            $msg = "File upload error encountered.";
            throw new RuntimeException($msg, $t->getCode(), $t);
        }

        return $media;
    }

    public function editFile(MediaEditRequest $request, Media $medium)
    {
        $changes = ['description' => $request->description];

        try {
            $orig = $medium->filepath;
            if ($request->filename !== $medium->getFilename()) {
                $dirname = pathinfo($orig, PATHINFO_DIRNAME);
                $changes['filepath'] = $dirname . '/' . $request->filename;
                $this->storage->move($orig, $changes['filepath']);
            }
        } catch (Throwable $t) {
            $msg = "Filename change error encountered.";
            throw new RuntimeException($msg, $t->getCode(), $t);
        }

        $medium->update($changes);
    }

    public function deleteFile(Media $file)
    {
        if ($this->storage->exists($file->filepath)) {
            $this->storage->delete($file->filepath);
        }
        $file->delete();
    }

    private function generateFilename($file)
    {
        $hash = '';
        while(mb_strlen($hash) < 15) {
            $hash .= strtolower(Str::random(4));
            $hash .= mb_strlen($hash) < 12 ? '-' : '.';
        }

        $subdir = strtolower(Str::random(2));

        $filename = $hash . $file->guessExtension();
        return ['subdir' => $subdir, 'filename' => $filename ];
    }

    private function generateFileSize(UploadedFile $file)
    {
        return round($file->getSize() / 1024 / 1024, 2);
    }

    private function generateDesc(UploadedFile $file)
    {
        return preg_replace('/[^A-Za-z0-9\-\._]/', '', $file->getClientOriginalName());
    }
}
