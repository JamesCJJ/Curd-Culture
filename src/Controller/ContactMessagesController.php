<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;

/**
 * Admin > Contact Messages
 * - Safe date filtering: normalize and validate "from" / "to" before building conditions
 * - Never construct DateTime with an invalid string
 * - Manual pagination to match the view
 */
class ContactMessagesController extends AppController
{
    /**
     * Normalize a date string to YYYY-MM-DD and validate it.
     * Returns null when invalid/empty. Accepts separators "/", "." and "-".
     */
    private function normalizeYmd(?string $s): ?string
    {
        $s = trim((string)$s);
        if ($s === '') {
            return null;
        }

        // Unify separators and strip spaces
        $s = str_replace(['/', '.'], '-', $s);
        $s = preg_replace('/\s+/', '', $s ?? '') ?? '';

        // Only 4-digit year is allowed
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $s));
        if (!checkdate($m, $d, $y)) {
            return null;
        }

        return $s; // YYYY-MM-DD
    }

    /**
     * Index: list messages with filters and pagination.
     */
    public function index()
    {
        $q      = trim((string)$this->request->getQuery('q'));
        $status = (string)$this->request->getQuery('status');
        $fromIn = (string)$this->request->getQuery('from');
        $toIn   = (string)$this->request->getQuery('to');

        // Normalize/validate dates; null if invalid
        $from = $this->normalizeYmd($fromIn);
        $to   = $this->normalizeYmd($toIn);

        // If both present but reversed, swap to make a valid range
        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
            $this->Flash->warning('Date range was corrected: "From" was after "To".');
        }

        $Messages = $this->fetchTable('ContactMessages');

        $query = $Messages->find()
            ->orderByDesc('ContactMessages.created');

        if ($q !== '') {
            $query->where([
                'OR' => [
                    'ContactMessages.name LIKE'    => '%' . $q . '%',
                    'ContactMessages.email LIKE'   => '%' . $q . '%',
                    'ContactMessages.message LIKE' => '%' . $q . '%',
                ]
            ]);
        }

        if ($status !== '') {
            // Accept known states; ignore unknowns silently
            $allowed = ['new', 'in_progress', 'closed', 'unread', 'read'];
            if (in_array($status, $allowed, true)) {
                $query->where(['ContactMessages.status' => $status]);
            }
        }

        // Safe date conditions (only add when normalized OK)
        if ($from !== null) {
            $query->where(['ContactMessages.created >=' => new DateTime($from . ' 00:00:00')]);
        }
        if ($to !== null) {
            $query->where(['ContactMessages.created <=' => new DateTime($to . ' 23:59:59')]);
        }

        // Manual pagination to match the template
        $limit  = 20;
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $messages    = $query->limit($limit)->offset($offset)->all();
        $totalCount  = $query->count();
        $totalPages  = (int)ceil($totalCount / $limit);
        $pagination  = [
            'page'   => $page,
            'pages'  => $totalPages,
            'count'  => $totalCount,
            'hasPrev'=> $page > 1,
            'hasNext'=> $page < $totalPages,
        ];

        // Pass normalized values back to the view so inputs refill correctly
        $this->set(compact('messages', 'pagination', 'q', 'status', 'from', 'to'));
    }
}
