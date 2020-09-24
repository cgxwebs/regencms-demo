<?php


namespace App\Domain;


use App\Enums\TagVisibility;
use App\Tag;
use App\User;
use Illuminate\Database\Connection;

class TagRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getVisibleTagIds($channel_id) {
        $tags = $this->getVisibleTags($channel_id);
        $ids = [];
        foreach($tags as $t) {
            $ids[] = $t->id;
        }
        return $ids;
    }

    /**
     * Retrieve visible tags including their descendants
     * If a parent is non-visible, it applies to its children
     */
    public function getVisibleTags($channel_id)
    {
        $query = "
            SELECT t.*
            FROM tags t
            INNER JOIN channel_tag ct ON ct.tag_id = t.id
            WHERE ct.channel_id = :channel_id
            ORDER BY t.name ASC;
        ";

        $bindings = [
            'channel_id' =>$channel_id
        ];

        $collection = Tag::fromQuery($query, $bindings);
        $visible = [];
        $parents = [];

        foreach($collection as $tag) {
            /**
             * @var $tag Tag
             */
            $isChild = true;
            if (false === strpos($tag->name, ".")) {
                // Get visibility of parents in name => boolean pair
                $parents[$tag->name] = $tag->visibility === TagVisibility::Visible;
                $isChild = false;
            }

            if ($tag->visibility === TagVisibility::Visible) {
                if ($isChild) {
                    $parent = substr($tag->name, 0, strpos($tag->name, "."));
                    // Parent is not visible, don't add to list
                    if (isset($parents[$parent]) && false === $parents[$parent]) {
                        continue;
                    }
                }
                $visible[] = $tag;
            }
        }

        return $visible;
    }

    public function getVisibleTagsByUser(User $user)
    {
        $query = "
            SELECT t.*
            FROM tags t
            INNER JOIN channel_tag ct ON ct.tag_id = t.id
            INNER JOIN channel_user cu ON cu.channel_id = ct.channel_id
            WHERE cu.user_id = :user_id
            ORDER BY t.name ASC;
        ";

        $bindings = [
            'user_id' => $user->id
        ];

        return Tag::fromQuery($query, $bindings);
    }

}
