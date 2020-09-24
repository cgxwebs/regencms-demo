<?php

namespace App\Domain;

final class StoryPagerOptions
{
    const SORT_OPTIONS = [
        'newest' => 'SORT_NEWEST',
        'oldest' => 'SORT_OLDEST',
        'new-update' => 'SORT_NEW_UPDATE',
        'old-update' => 'SORT_OLD_UPDATE',
        'a-z' => 'SORT_ALPHA',
        'z-a' => 'SORT_ALPHA_REV'
    ];
    const SORT_NEWEST = 'created_at DESC';
    const SORT_OLDEST = 'created_at ASC';
    const SORT_NEW_UPDATE = 'updated_at DESC';
    const SORT_OLD_UPDATE = 'updated_at ASC';
    const SORT_ALPHA = 'title ASC';
    const SORT_ALPHA_REV = 'title DESC';

    private int $perPage;
    private int $pagePtr;
    private $sort;
    private int $total;

    public function __construct(int $limit = 10, int $pointer = 1, $sort = self::SORT_OPTIONS['newest'])
    {
        $this->perPage = $limit;
        $this->pagePtr = $pointer;
        $this->sort = $sort;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPagePtr(): int
    {
        return $this->pagePtr;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getSortAndPagerStmt()
    {
        $sort_stmt = sprintf(" ORDER BY %s ", implode(', ', $this->getSortCols()));
        $offset = ($this->pagePtr - 1) * $this->perPage;
        $limit_stmt = sprintf(" OFFSET %d LIMIT %d ", $offset, $this->perPage);
        return $sort_stmt.$limit_stmt;
    }

    public function getPageUrls()
    {
        $next_page = '';
        $prev_page = '';
        $total_pages = ceil($this->total / $this->perPage);
        $url = 'page=%d&sort=%s';
        if ($this->pagePtr < $total_pages) {
            $next_page = sprintf($url, $this->pagePtr + 1, $this->getSort());
        }
        if ($this->pagePtr > 1) {
            $prev_page = sprintf($url, $this->pagePtr - 1, $this->getSort());
        }
        return [
            'next_page' => $next_page,
            'prev_page' => $prev_page
        ];
    }

    public function getSortCols($asString = true)
    {
        $sort_col = constant(get_class($this) . '::' . self::SORT_OPTIONS[$this->sort]);
        $temp = [
            explode(' ', $sort_col),
            ['id', 'DESC']
        ];
        if ($asString) {
            return array_map(function ($s) {
                // Add alias and convert to string
                return sprintf('s.%s %s', $s[0], $s[1]);
            }, $temp);
        }
        return $temp;
    }
}
