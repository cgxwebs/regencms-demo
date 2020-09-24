<?php

namespace App\Http\Controllers\Api;

use App\Domain\TagRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagApiController extends Controller
{
    use ApiConcerns;

    private $repo;

    public function __construct(
        Request $request,
        TagRepository $tagRepository
    )
    {
        $this->request = $request;
        $this->tagRepository = $tagRepository;
    }

    public function listByChannel(string $channel)
    {
        $channelModel = $this->findEntity('channel', $channel);
        $tags = $this->tagRepository->getVisibleTags($channelModel->id);
        return $this->getApiResponse($tags);
    }

}
