<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Bootstrap Pagination Helper
 * 
 * Provides Bootstrap 5 compatible pagination controls
 */
class BootstrapPaginationHelper extends Helper
{
    protected array $helpers = ['Url'];

    /**
     * Generate Bootstrap pagination controls
     *
     * @param array $pagination Pagination data with keys: page, pages, count, hasPrev, hasNext
     * @param array $options Options for customization
     * @return string HTML pagination controls
     */
    public function controls(array $pagination, array $options = []): string
    {
        // Default options
        $options = array_merge([
            'class' => 'pagination justify-content-center',
            'size' => '', // 'pagination-sm' or 'pagination-lg'
            'showInfo' => true,
            'infoClass' => 'text-center mt-2',
            'ariaLabel' => 'Pagination',
            'queryParams' => $this->getView()->getRequest()->getQueryParams(),
            'maxButtons' => 5, // Maximum number of page buttons to show
        ], $options);

        if ($pagination['pages'] <= 1) {
            return '';
        }

        $current = $pagination['page'];
        $totalPages = $pagination['pages'];
        $queryParams = $options['queryParams'];

        // Calculate page range
        $maxButtons = $options['maxButtons'];
        $halfRange = floor($maxButtons / 2);
        $start = max(1, $current - $halfRange);
        $end = min($totalPages, $start + $maxButtons - 1);
        
        // Adjust start if we're near the end
        if ($end - $start + 1 < $maxButtons) {
            $start = max(1, $end - $maxButtons + 1);
        }

        $html = '<nav aria-label="' . h($options['ariaLabel']) . '" class="mt-4">';
        $html .= '<ul class="' . h($options['class']) . ($options['size'] ? ' ' . h($options['size']) : '') . '">';

        // Previous Button
        $html .= '<li class="page-item' . (!$pagination['hasPrev'] ? ' disabled' : '') . '">';
        if ($pagination['hasPrev']) {
            $prevUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $current - 1])]);
            $html .= '<a class="page-link" href="' . h($prevUrl) . '">';
            $html .= '<span aria-hidden="true">&laquo;</span> Previous';
            $html .= '</a>';
        } else {
            $html .= '<span class="page-link"><span aria-hidden="true">&laquo;</span> Previous</span>';
        }
        $html .= '</li>';

        // Show first page if not in range
        if ($start > 1) {
            $firstUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => 1])]);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . h($firstUrl) . '">1</a>';
            $html .= '</li>';
            
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Page Numbers
        for ($i = $start; $i <= $end; $i++) {
            $html .= '<li class="page-item' . ($i == $current ? ' active' : '') . '">';
            if ($i == $current) {
                $html .= '<span class="page-link">' . $i . '</span>';
            } else {
                $pageUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $i])]);
                $html .= '<a class="page-link" href="' . h($pageUrl) . '">' . $i . '</a>';
            }
            $html .= '</li>';
        }

        // Show last page if not in range
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            $lastUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $totalPages])]);
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . h($lastUrl) . '">' . $totalPages . '</a>';
            $html .= '</li>';
        }

        // Next Button
        $html .= '<li class="page-item' . (!$pagination['hasNext'] ? ' disabled' : '') . '">';
        if ($pagination['hasNext']) {
            $nextUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $current + 1])]);
            $html .= '<a class="page-link" href="' . h($nextUrl) . '">';
            $html .= 'Next <span aria-hidden="true">&raquo;</span>';
            $html .= '</a>';
        } else {
            $html .= '<span class="page-link">Next <span aria-hidden="true">&raquo;</span></span>';
        }
        $html .= '</li>';

        $html .= '</ul>';

        // Page Info
        if ($options['showInfo']) {
            $html .= '<div class="' . h($options['infoClass']) . '">';
            $html .= '<small class="text-muted">';
            $html .= 'Page ' . $current . ' of ' . $totalPages;
            if (isset($pagination['count'])) {
                $html .= ' (' . number_format($pagination['count']) . ' total items)';
            }
            $html .= '</small>';
            $html .= '</div>';
        }

        $html .= '</nav>';

        return $html;
    }

    /**
     * Generate simple pagination (just prev/next)
     *
     * @param array $pagination Pagination data
     * @param array $options Options for customization
     * @return string HTML pagination controls
     */
    public function simple(array $pagination, array $options = []): string
    {
        $options = array_merge([
            'class' => 'pagination justify-content-between',
            'queryParams' => $this->getView()->getRequest()->getQueryParams(),
            'prevText' => '← Previous',
            'nextText' => 'Next →',
        ], $options);

        if ($pagination['pages'] <= 1) {
            return '';
        }

        $queryParams = $options['queryParams'];
        $html = '<nav class="mt-4">';
        $html .= '<ul class="' . h($options['class']) . '">';

        // Previous
        $html .= '<li class="page-item' . (!$pagination['hasPrev'] ? ' disabled' : '') . '">';
        if ($pagination['hasPrev']) {
            $prevUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $pagination['page'] - 1])]);
            $html .= '<a class="page-link" href="' . h($prevUrl) . '">' . h($options['prevText']) . '</a>';
        } else {
            $html .= '<span class="page-link">' . h($options['prevText']) . '</span>';
        }
        $html .= '</li>';

        // Next
        $html .= '<li class="page-item' . (!$pagination['hasNext'] ? ' disabled' : '') . '">';
        if ($pagination['hasNext']) {
            $nextUrl = $this->Url->build(['?' => array_merge($queryParams, ['page' => $pagination['page'] + 1])]);
            $html .= '<a class="page-link" href="' . h($nextUrl) . '">' . h($options['nextText']) . '</a>';
        } else {
            $html .= '<span class="page-link">' . h($options['nextText']) . '</span>';
        }
        $html .= '</li>';

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
}
