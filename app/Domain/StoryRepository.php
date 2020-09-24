<?php

namespace App\Domain;

use App\Concerns\Assert;
use App\Domain\Services\Story\StoryReader;
use App\Enums\StoryStatus;
use App\Enums\TagVisibility;
use App\Story;
use Illuminate\Database\Connection;
use Illuminate\Pagination\LengthAwarePaginator;


final class StoryRepository
{
    private Connection $db;

    private TagRepository $tagRepository;

    public function __construct(
        Connection $db,
        TagRepository $tagRepository,
        StoryReader $reader
    )
    {
        $this->db = $db;
        $this->tagRepository = $tagRepository;
        $this->reader = $reader;
    }

    public function listStories(StoryListFilter $filters): LengthAwarePaginator
    {
        $queries = $filters->getQueries();
        [$total_query, $total_bindings] = $queries['total'];
        [$story_query, $story_bindings] = $queries['story'];

        $total_res = $this->db->select($total_query, $total_bindings);
        $total_items = $total_res[0]->total;
        $stories = Story::fromQuery($story_query, $story_bindings);

        return new LengthAwarePaginator(
            $stories,
            $total_items,
            $filters->getPerPage(),
            $filters->getPagePtr()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getSingleStory($channel_id, $story_key, $type = 'id')
    {
        Assert::oneOf($type, ['id', 'slug']);

        /**
         * Retrieves a "visible" story wherein:
         * - Story is not a draft
         * - Story belongs to the specified channel
         * - Story has no tags with hidden visibility
         */
        // Checks existence
        $query =
            "WITH visible_stories AS (
                SELECT st.story_id AS id FROM story_tag st
                INNER JOIN tags t ON t.id = st.tag_id
                INNER JOIN channel_tag ct ON t.id = ct.tag_id
                WHERE ct.channel_id = :channel_id
            ),
            hidden_stories AS (
                SELECT st.story_id AS id FROM story_tag st
                INNER JOIN tags t ON t.id = st.tag_id
                INNER JOIN channel_tag ct ON t.id = ct.tag_id
                WHERE ct.channel_id = :channel_id AND t.visibility = :tag_hidden
            )
            SELECT id
            FROM stories s
            WHERE %s
            AND s.status != :story_draft
            AND EXISTS (SELECT 1 FROM visible_stories vs WHERE vs.id = s.id )
            AND NOT EXISTS (SELECT 1 FROM hidden_stories vs WHERE vs.id = s.id )";

        $query = sprintf($query, $type === 'id' ? 's.id = :story_key' : 's.slug = :story_key');

        $bindings = [
            'story_key' => $story_key,
            'channel_id' => $channel_id,
            'tag_hidden' => TagVisibility::getValue('Hidden'),
            'story_draft' => StoryStatus::getValue('Draft')
        ];

        if ($res = $this->db->select($query, $bindings)) {
            $story = Story::where('id', $res[0]->id)->first();
            if ($story) {
                return $story->setReadable($this->reader->read($story));
            }
        }

        return null;
    }

    /**
     * Retrieve all published stories with visible tags
     */
    public function getPublishedStories($channel_id, StoryPagerOptions $pager)
    {
        $visible_tags = $this->tagRepository->getVisibleTagIds($channel_id);
        $in_binds = implode(',', array_fill(0, count($visible_tags), '?'));

        $query = sprintf(
            "WITH visible_stories AS (
                SELECT st.story_id AS id FROM story_tag st
                WHERE st.tag_id IN (%s)
            ),
            hidden_stories AS (
                SELECT st.story_id AS id FROM story_tag st
                INNER JOIN tags t ON t.id = st.tag_id
                INNER JOIN channel_tag ct ON t.id = ct.tag_id
                WHERE ct.channel_id = ?
                AND t.visibility != ?
            ) ", $in_binds);

        /** @noinspection SqlResolve */
        /** @noinspection SqlNoDataSourceInspection */
        $query .=
            "SELECT %s
            FROM stories s
            WHERE s.status = ?
            AND EXISTS (SELECT 1 FROM visible_stories vs WHERE vs.id = s.id )
            AND NOT EXISTS (SELECT 1 FROM hidden_stories vs WHERE vs.id = s.id ) ";

        $bindings = array_merge($visible_tags, [
            $channel_id,
            TagVisibility::Visible,
            StoryStatus::Published,
        ]);

        if (count($visible_tags) === 0) {
            return [ 'total' => 0, 'items' => []];
        }

        $pager->setTotal($this->countTotalItems($query, $bindings));
        return collect([
            'total' => $pager->getTotal(),
            'items' => $this->getStoryItems($query, $bindings, $pager),
            'urls' => $pager->getPageUrls(),
        ]);
    }

    /**
     * Gets all published stories belonging to a tag. Tag must not be hidden.
     */
    public function getPublishedStoriesByTag($channel_id, $tag_name, StoryPagerOptions $pager)
    {
        $query =
            "WITH visible_stories AS (
                    SELECT st.story_id AS id FROM story_tag st
                    INNER JOIN tags t ON t.id = st.tag_id
                    INNER JOIN channel_tag ct ON t.id = ct.tag_id
                    WHERE ct.channel_id = :channel_id
                      AND (
                        (t.name = :tag_name AND t.visibility != :tag_hidden)
                        OR
                        (t.name LIKE :tag_parent AND t.visibility = :tag_visible)
                      )
            ),
            hidden_stories AS (
                SELECT st.story_id AS id FROM story_tag st
                INNER JOIN tags t ON t.id = st.tag_id
                INNER JOIN channel_tag ct ON t.id = ct.tag_id
                WHERE ct.channel_id = :channel_id
                AND t.visibility != :tag_visible
                AND t.name != :tag_name
            )
            SELECT %s
            FROM stories s
            WHERE s.status = :story_published
            AND EXISTS (SELECT 1 FROM visible_stories vs WHERE vs.id = s.id )
            AND NOT EXISTS (SELECT 1 FROM hidden_stories vs WHERE vs.id = s.id ) ";

        $bindings = [
            'channel_id' => $channel_id,
            'tag_name' => $tag_name,
            'tag_parent' => $tag_name.'.%',
            'tag_hidden' => TagVisibility::Hidden,
            'tag_visible' => TagVisibility::Visible,
            'story_published' => StoryStatus::Published
        ];

        $pager->setTotal($this->countTotalItems($query, $bindings));

        return collect([
            'total' => $pager->getTotal(),
            'items' => $this->getStoryItems($query, $bindings, $pager),
            'urls' => $pager->getPageUrls(),
        ]);
    }

    /**
     * Get stories based on query and pager selections
     * Query must only retrieve IDs of stories which will later be populated as models
     * Make sure to leave a %s placeholder after SELECT!
     */
    private function getStoryItems($query, $bindings, StoryPagerOptions $pager)
    {
        $pager_stmt = $pager->getSortAndPagerStmt();
        $pager_sort = $pager->getSortCols(false);
        $visible_stories =  $this->db->select(
            sprintf($query . $pager_stmt, 's.id'),
            $bindings
        );
        if ($visible_stories) {
            $story_ids = array_map(function($item) {
                return $item->id;
            }, $visible_stories);
            $qb = Story::whereIn('id', $story_ids);
            foreach($pager_sort as $s) {
                $qb->orderBy($s[0], $s[1]);
            }
            $stories = $qb->get();
            return $this->prepareReadable($stories);
        }
        return [];
    }

    private function countTotalItems($query, $bindings)
    {
        $total_select = $this->db->select(
            sprintf($query, 'count(s.id) AS total'),
            $bindings
        );
        return $total_select[0]->total;
    }

    private function prepareReadable($stories)
    {
        foreach($stories as $s) {
            /**
             * @var $s Story
             */
            $s->setReadable($this->reader->read($s));
        }
        return $stories;
    }
}
