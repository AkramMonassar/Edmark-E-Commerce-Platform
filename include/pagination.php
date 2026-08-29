<?php
function paginate($total, $perPage, $currentPage, $baseUrl, $pageParam = 'page') {
    $totalPages = (int) ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $separator = str_contains($baseUrl, '?') ? '&' : '?';

    $html = '<nav aria-label="ترقيم الصفحات" class="mt-4">';
    $html .= '<ul class="pagination justify-content-center flex-wrap gap-1">';

    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($baseUrl . $separator . $pageParam . '=' . ($currentPage - 1), ENT_QUOTES) . '">السابق</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($baseUrl . $separator . $pageParam . '=' . $i, ENT_QUOTES) . '">' . $i . '</a></li>';
        }
    }

    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($baseUrl . $separator . $pageParam . '=' . ($currentPage + 1), ENT_QUOTES) . '">التالي</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}