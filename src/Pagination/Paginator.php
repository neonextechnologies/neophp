<?php

namespace NeoPhp\Pagination;

class Paginator
{
    protected $items;
    protected $total;
    protected $perPage;
    protected $currentPage;
    protected $lastPage;
    protected $path;

    public function __construct(array $items, int $total, int $perPage, int $currentPage = 1, string $path = '')
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = (int) ceil($total / $perPage);
        $this->path = $path ?: $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function previousPageUrl(): ?string
    {
        if ($this->currentPage > 1) {
            return $this->url($this->currentPage - 1);
        }
        return null;
    }

    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage + 1);
        }
        return null;
    }

    public function url(int $page): string
    {
        $path = parse_url($this->path, PHP_URL_PATH);
        parse_str(parse_url($this->path, PHP_URL_QUERY) ?? '', $query);
        $query['page'] = $page;
        
        return $path . '?' . http_build_query($query);
    }

    public function links(int $onEachSide = 3): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav aria-label="Pagination"><ul class="pagination">';

        // Previous
        if ($this->currentPage > 1) {
            $html .= sprintf(
                '<li class="page-item"><a class="page-link" href="%s">Previous</a></li>',
                $this->previousPageUrl()
            );
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Pages
        $start = max(1, $this->currentPage - $onEachSide);
        $end = min($this->lastPage, $this->currentPage + $onEachSide);

        if ($start > 1) {
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">1</a></li>', $this->url(1));
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($page = $start; $page <= $end; $page++) {
            if ($page === $this->currentPage) {
                $html .= sprintf('<li class="page-item active"><span class="page-link">%d</span></li>', $page);
            } else {
                $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">%d</a></li>', $this->url($page), $page);
            }
        }

        if ($end < $this->lastPage) {
            if ($end < $this->lastPage - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= sprintf('<li class="page-item"><a class="page-link" href="%s">%d</a></li>', $this->url($this->lastPage), $this->lastPage);
        }

        // Next
        if ($this->hasMorePages()) {
            $html .= sprintf(
                '<li class="page-item"><a class="page-link" href="%s">Next</a></li>',
                $this->nextPageUrl()
            );
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'from' => ($this->currentPage - 1) * $this->perPage + 1,
            'to' => min($this->currentPage * $this->perPage, $this->total),
        ];
    }
}
