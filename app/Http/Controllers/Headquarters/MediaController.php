<?php

namespace App\Http\Controllers\Headquarters;

use App\Domain\Services\Media\MediaManager;
use App\Media;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\{MediaRequest, MediaEditRequest};
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class MediaController extends Controller
{
    const ROUTE = 'hq.media.';
    const VIEWDIR = 'hq/media/';

    private MediaManager $mediaManager;

    private Guard $auth;

    public function __construct(MediaManager $mediaManager, Guard $auth)
    {
        $this->mediaManager = $mediaManager;
        $this->auth = $auth;
        $this->mediaManager->checkStorageDirectories();
    }

    public function index()
    {
        $this->authorize('create', Media::class);
        $media_index = Media::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(24);
        return view(self::VIEWDIR . 'list', [
            'media_index' => $media_index,
            'action' => route(self::ROUTE . 'store')
        ]);

    }

    public function store(MediaRequest $request)
    {
        $this->authorize('create', Media::class);
        $user = Auth::user();
        $uploaded = $request->file('media');

        if (!is_array($uploaded)) {
            throw new BadRequestHttpException();
        }

        try {
            $count = min(count($uploaded), intval(config('regencms.media_batch')));
            while ($count) {
                $file = array_shift($uploaded);
                $this->mediaManager->storeFile($file, $user);
                $count--;
            }
        } catch(Throwable $t) {
            return redirect()
                ->route(self::ROUTE.'index')
                ->with('save_failed', true)
                ->with('save_failed_message', 'Unrecoverable error encountered.');
        }

        return redirect()->route(self::ROUTE . 'index')
            ->with('save_success', true)
            ->with('save_success_message', 'See uploaded file(s) below.');
    }

    public function edit(Media $medium)
    {
        $this->authorize('write', $medium);
        return view(self::VIEWDIR . 'edit', [
            'action' => route(self::ROUTE . 'update', ['medium' => $medium]),
            'delete' => route(self::ROUTE . 'delete', ['medium' => $medium]),
            'edit' => $medium,
        ]);
    }

    public function update(MediaEditRequest $request, Media $medium)
    {
        $this->authorize('write', $medium);
        try {
            $this->mediaManager->editFile($request, $medium);
        } catch(Throwable $t) {
            return redirect()
                ->route(self::ROUTE.'edit', ['medium' => $medium])
                ->with('save_failed', true)
                ->with('save_failed_message', 'Unrecoverable error encountered.');
        }
        return redirect()
            ->route(self::ROUTE.'edit', ['medium' => $medium])
            ->with('save_success', true);
    }

    public function delete(Media $medium)
    {
        $this->authorize('write', $medium);
        return view('layouts.dashboard_delete_item', [
            'action' => route(self::ROUTE . 'destroy', ['medium' => $medium]),
            'back_route' => route(self::ROUTE.'edit', ['medium' => $medium]),
            'item_type' => 'Media',
            'item_name' => empty($medium->description) ? $medium->filepath : $medium->description,
        ]);
    }

    public function destroy(Request $request, Media $medium)
    {
        $this->authorize('write', $medium);

        $file_name = empty($medium->description) ? $medium->filepath : $medium->description;
        $this->validate($request, [
            'password' => 'required|string|password'
        ]);

        $this->mediaManager->deleteFile($medium);

        return redirect()->route(self::ROUTE.'index')
            ->with('delete_success_message', sprintf("File '%s' has been deleted.", $file_name));
    }
}
