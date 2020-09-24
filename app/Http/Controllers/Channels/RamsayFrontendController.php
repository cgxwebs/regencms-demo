<?php


namespace App\Http\Controllers\Channels;


use App\Channel;
use App\Domain\StoryPagerOptions;
use App\Domain\StoryRepository;
use App\Domain\TagRepository;
use App\Http\Controllers\Controller;
use App\Story;
use Illuminate\Support\Facades\Route;

class RamsayFrontendController extends Controller
{
    const VIEWDIR = 'channel_ramsay/';
    const CHANNEL_NAME = 'ramsay';

    private StoryRepository $storyRepository;
    private TagRepository $tagRepository;
    private $channel;
    private $is_staging = false;

    public function __construct(StoryRepository $storyRepository, TagRepository $tagRepository)
    {
        $this->storyRepository = $storyRepository;
        $this->tagRepository = $tagRepository;
        $this->channel = Channel::where('name', '=', self::CHANNEL_NAME)->firstOrFail();
        $domain = Route::getCurrentRoute()->domain();
        $this->is_staging = false !== strpos($domain, 'staging');
    }

    public function index()
    {
        return view(self::VIEWDIR.'index', [
            'is_staging' => $this->is_staging,
        ]);
    }

    public function about(string $slug = '')
    {
        $pager = new StoryPagerOptions(
            10, 1, 'a-z'
        );

        $articles = $this->storyRepository->getPublishedStoriesByTag(
            $this->channel->id,
            'ramsay_about',
            $pager
        );

        if (empty($slug) && count($articles)) {
            $first = $articles->get('items')->first();
            if ($first && $first->slug) {
                return redirect()->route(($this->is_staging ? 'staging_' : '').'about', ['slug' => $first->slug]);
            } else {
                abort(404);
            }
        }

        /**
         * @var $viewing Story
         */
        $viewing = $this->storyRepository->getSingleStory(
            $this->channel->id,
            $slug,
            'slug'
        );

        if ($viewing) {
            return view(self::VIEWDIR . 'about', [
                'articles' => $articles['items'],
                'viewing' => $viewing,
                'slug' => $slug,
                'is_staging' => $this->is_staging,
            ]);
        }

        abort(404);
    }

    public function recipes(int $id = 0)
    {
        $pager = new StoryPagerOptions(
            30, 1, 'a-z'
        );

        $tags = $this->tagRepository->getVisibleTags($this->channel->id);
        $categories = [];
        $recipes = [];
        foreach($tags as $t) {
            if (false !== strpos($t->name, 'ramsay_recipes.')) {
                $categories[] = $t;
                $recipes[$t->name] = [];
            }
        }

        $all = $this->storyRepository->getPublishedStoriesByTag(
            $this->channel->id,
            'ramsay_recipes',
            $pager
        );
        foreach($all->get('items') as $r) {
            foreach($r->tags as $rt) {
                if(isset($recipes[$rt->name])) {
                    $recipes[$rt->name][] = $r;
                }
            }
        }

        $viewing = null;
        $id = intval($id);
        if ($id) {
            /**
             * @var $viewing Story
             */
            $viewing = $this->storyRepository->getSingleStory(
                $this->channel->id,
                $id
            );
        }

        return view(self::VIEWDIR . 'recipes', [
            'categories' => $categories,
            'recipes' => $recipes,
            'viewing' => $viewing,
            'is_staging' => $this->is_staging,
        ]);
    }

}
