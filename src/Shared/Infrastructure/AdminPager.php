<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Infrastructure;

final class AdminPager
{
    /**
     * @return array{page:int,per_page:int,offset:int}
     */
    public static function fromRequest(int $defaultPerPage = 25, int $maxPerPage = 100): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['per_page'] ?? $defaultPerPage);
        $perPage = max(1, min($maxPerPage, $perPage > 0 ? $perPage : $defaultPerPage));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    /**
     * @return array{page:int,per_page:int,total:int,total_pages:int,has_prev:bool,has_next:bool}
     */
    public static function meta(int $total, int $page, int $perPage): array
    {
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $page = max(1, min($page, $totalPages));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }
}
