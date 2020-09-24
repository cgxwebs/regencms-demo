<?php

namespace App\Domain;

use App\Concerns\Assert;
use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\StoryStatus;
use App\Enums\TagVisibility;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class StoryListFilter implements Arrayable
{
    const SORT_ASC = false;
    const SORT_DESC = true;
    const ORDER_LIST = [
        'created.desc' => 'Newest',
        'created.asc' => 'Oldest',
        'updated.desc' => 'Newest update',
        'updated.asc' => 'Oldest update',
        'title.desc' => 'Z-A',
        'title.asc' => 'A-Z'
    ];

    private $channels = [];
    private $tags = [];
    private $tag_visibility = 'all';
    private $story_status = 'all';
    private $sort_by = [];
    private int $perPage = 10;
    private int $totalItems;
    private int $pagePtr = 1;
    private string $route;

    private Request $request;
    private Connection $db;
    private $user;

    private ChannelsTagsIndex $ctIndex;

    public function __construct(
        Connection $db,
        Request $request,
        $user,
        ChannelsTagsIndex $ctIndex
    )
    {
        $this->db = $db;
        $this->request = $request;
        $this->user = $user;
        $this->ctIndex = $ctIndex;
    }

    public function getFilterOptions()
    {
        $channel_list = $this->ctIndex->getSource('channel');
        $tag_list = $this->ctIndex->getSource('tag');
        $vis_list = TagVisibility::getInstances();
        $status_list = StoryStatus::getInstances();

        return [
            'channel_list' => $channel_list,
            'tag_list' => $tag_list,
            'status_list' => $status_list,
            'order_list' => self::ORDER_LIST
        ];
    }

    public function getFilterSelection()
    {
        $sort_selection = $this->request->only('sort_channel', 'sort_tag', 'sort_status', 'sort_order', 'page');

        $defaults = array_merge(
            [
                'page' => '1',
                'sort_channel' => '0',
                'sort_tag' => '0',
                'sort_status' => 'all',
                'sort_order' => 'created.desc'
            ],
            $sort_selection
        );

        $rules = [
            'page' => 'integer|min:1',
            'sort_channel' => 'integer',
            'sort_tag' => 'integer',
            'sort_status' => Rule::in(['all', ...StoryStatus::getValues()]),
            'sort_order' => Rule::in(array_keys(self::ORDER_LIST))
        ];

        $validator = Validator::make($defaults, $rules);
        if ($validator->fails()) {
            abort(404);
        }

        return $defaults;
    }

    public function filterSelection(array $selection)
    {
        if ($selection['sort_channel'] != '0') {
            $this->filterChannels([$selection['sort_channel']]);
        }
        if ($selection['sort_tag'] != '0') {
            $this->filterTags([$selection['sort_tag']]);
        }
        if ($selection['sort_status'] != 'all') {
            $this->filterStoryStatus($selection['sort_status']);
        }
        $this->sortBy($selection['sort_order']);
        $this->setPagePtr($selection['page']);
        $this->setPerPage(10);
    }

    public function filterChannels(array $channels)
    {
        $this->channels = $channels;
    }

    public function filterTags(array $tags)
    {
        $this->tags = $tags;
    }

    public function filterTagVisibility(string $vis)
    {
        Assert::oneOf($vis, TagVisibility::getValues());
        $this->tag_visibility = $vis;
    }

    public function filterStoryStatus(string $status)
    {
        Assert::oneOf($status, StoryStatus::getValues());
        $this->story_status = $status;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = $totalItems;
    }

    public function getPagePtr(): int
    {
        return $this->pagePtr;
    }

    public function setPagePtr(int $pagePtr): void
    {
        $this->pagePtr = $pagePtr;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    public function getPageUrls($paginator)
    {
        $next_page = '';
        $prev_page = '';

        if ($paginator->hasMorePages()) {
            $next_page = $this->getUrl(
                $this->route . '?',
                '&page='.($paginator->currentPage() + 1)
            );
        }
        if ($paginator->currentPage() > 1) {
            $prev_page = $this->getUrl(
                $this->route . '?',
                '&page='.($paginator->currentPage() - 1)
            );
        }

        return [
            'next_page' => $next_page,
            'prev_page' => $prev_page
        ];
    }

    public function getQueries()
    {
        [$story_query, $story_bindings] = $this->getBaseQuery('main');
        // Must be zero-based on start
        $offset = ($this->pagePtr - 1) * $this->perPage;
        $limit_stmt = sprintf(" OFFSET %d LIMIT %d ", $offset, $this->perPage);
        return [
            'total' => $this->getBaseQuery('total'),
            'story' => [$story_query.$limit_stmt, $story_bindings]
        ];
    }

    private function getBaseQuery($queryType = 'main')
    {
        // Visible Stories
        $vs = $this->db->table('story_tag', 'st');
        $vs->select('st.story_id AS id')->from('story_tag', 'st');
        $vs->leftJoin('tags AS t', 't.id', '=', 'st.tag_id');

        $channelsJoined = false;
        $hs = null;
        if (!$this->user->isSuper()) {
            $vs->leftJoin('channel_tag AS ct', 'st.tag_id', '=', 'ct.tag_id');
            $vs->leftJoin('channel_user AS cu', 'cu.channel_id', '=', 'ct.channel_id');
            $vs->where('cu.user_id', '=', $this->user->id);
            $channelsJoined = true;
            // Hidden Stories
            $hs = $this->db->table('story_tag', 'st')
                ->select('st.story_id AS id')
                ->whereNotExists(function ($query)  {
                    /**
                     * @var $query Builder
                     */
                    $query->select('ct.tag_id')
                        ->from('channel_tag', 'ct')
                        ->leftJoin('channel_user AS cu', 'ct.channel_id', '=', 'cu.channel_id')
                        ->where('cu.user_id', $this->user->id)
                        ->where('st.tag_id', $query->raw('ct.tag_id'));
                });
        }

        if ($this->channels) {
            if (!$channelsJoined) {
                $vs->leftJoin('channel_tag AS ct', 'st.tag_id', '=', 'ct.tag_id');
            }
            $vs->whereIn('ct.channel_id', $this->channels);
        }

        if ($this->tags) {
            $vs->whereIn('t.id', $this->tags);
        }

//        if ($this->tag_visibility != 'all') {
//            $vs->where('t.visibility', '=', $this->tag_visibility);
//        }

        if (count($this->tags)) {
            $vs_filter[] = 't.id IN (' . $this->makePlaceholders(count($this->tags)) . ')';
            $vs_bind[] = $this->tags;
        }

//        if ($this->tag_visibility != 'all') {
//            $vs_filter[] = 't.visibility = ?';
//            $vs_bind[] = [$this->tag_visibility];
//        }

        // Main Query
        $qb = $this->db->table('stories', 's');

        if ($this->story_status !== 'all') {
            $qb->where('s.status', '=', $this->story_status);
        }

        if ($queryType === 'main') {
            if ($this->sort_by) {
                $qb->orderBy('s.'.$this->sort_by[0], $this->sort_by[1]);
            }

            $qb->orderBy('s.id', 'desc');
        }

        if ($queryType === 'total') {
            $qb->select($this->db->raw('count(s.id) as total'));
        }



        // Putting it all together
        if ($vs->getBindings()) {
            $qb->whereExists(function ($query) {
                $query->select('s.id')->from('visible_stories', 'vs')->whereRaw('vs.id = s.id');
            });
            $final_query = 'WITH visible_stories AS ('. $vs->toSql() .') ';

            if ($hs) {
                $qb->whereNotExists(function ($query) {
                    $query->select('s.id')->from('hidden_stories', 'hs')->whereRaw('hs.id = s.id');
                });
                $final_query .= ', hidden_stories AS ('. $hs->toSql() .') ';
            }

            $final_query .= $qb->toSql();
            // Order is important
            $final_bindings = array_merge(
                $vs->getBindings(),
                $hs ? $hs->getBindings(): [],
                $qb->getBindings(),
            );
        } else {
            $final_query = $qb->toSql();
            $final_bindings = $qb->getBindings();
        }

        return [ $final_query, $final_bindings ];
    }

    public function sortBy(string $sort_order)
    {
        $so = explode('.', $sort_order);

        $method = 'sortBy'.ucfirst($so[0]);
        $order = $so[1] == 'desc' ? $this::SORT_DESC : $this::SORT_ASC;

        if (method_exists($this, $method)) {
            $this->$method($order);
        }
    }

    public function sortByTitle(bool $desc = true)
    {
        $this->sort_by = ['title' , ($desc ? 'desc' : 'asc')];
    }

    public function sortByCreated(bool $desc = true)
    {
        $this->sort_by = ['created_at' , ($desc ? 'desc' : 'asc')];
    }

    public function sortByUpdated(bool $desc = true)
    {
        $this->sort_by = ['updated_at' , ($desc ? 'desc' : 'asc')];
    }

    private function makePlaceholders(int $count): string
    {
        $ph = '';
        while ($count) {
            $ph .= '?,';
            $count--;
        }
        return trim($ph, ',');
    }

    public function toArray()
    {
        return [
            'sort_channel' => ($glued = implode(',', $this->channels)) === '' ? '0' : $glued,
            'sort_tag' => ($glued = implode(',', $this->tags)) === '' ? '0' : $glued,
            'sort_status' => $this->story_status,
            'sort_order' => str_replace('_at', '', implode('.', $this->sort_by)),
            'page' => $this->pagePtr
        ];
    }

    public function getQueryString()
    {
        $qs = $this->toArray();
        unset($qs['page']);
        $string = '';
        foreach($qs as $k => $v) {
            $string .= $k . '=' .$v . '&';
        }
        return trim($string, '&');
    }

    public function getUrl($prepend = '', $append = '')
    {
        return $prepend . $this->getQueryString() . $append;
    }
}
