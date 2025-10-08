<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;

/**
 * Admin > Contact Messages
 */
class ContactMessagesController extends AppController
{

    private function normalizeYmd(?string $s): ?string
    {
        $s = trim((string)$s);
        if ($s === '') {
            return null;
        }

        $s = str_replace(['/', '.'], '-', $s);
        $s = preg_replace('/\s+/', '', $s ?? '') ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $s));
        if (!checkdate($m, $d, $y)) {
            return null;
        }

        return $s;
    }

    /**
     * GET /admin/contact-messages
     */
    public function index()
    {
        $q      = trim((string)$this->request->getQuery('q'));
        $status = (string)$this->request->getQuery('status');
        $fromIn = (string)$this->request->getQuery('from');
        $toIn   = (string)$this->request->getQuery('to');


        $from = $this->normalizeYmd($fromIn);
        $to   = $this->normalizeYmd($toIn);


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
            $allowed = ['new', 'in_progress', 'closed', 'unread', 'read'];
            if (in_array($status, $allowed, true)) {
                $query->where(['ContactMessages.status' => $status]);
            }
        }


        if ($from !== null) {
            $query->where(['ContactMessages.created >=' => new DateTime($from . ' 00:00:00')]);
        }
        if ($to !== null) {
            $query->where(['ContactMessages.created <=' => new DateTime($to . ' 23:59:59')]);
        }


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

        $this->set(compact('messages', 'pagination', 'q', 'status', 'from', 'to'));
    }

    /**
     * GET /admin/contact-messages/view/{id}
     */
    public function view(int $id)
    {
        $table = $this->fetchTable('ContactMessages');
        $msg   = $table->get($id, contain: ['Users']);


        if ($this->request->is('get') && $msg->status === 'unread') {
            $msg->status = 'read';
            $table->save($msg);
        }


        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            if (!empty($data['status'])) {
                if (in_array($data['status'], ['unread', 'read', 'in_progress', 'closed'], true)) {
                    $oldStatus  = $msg->status;
                    $msg->status = $data['status'];

                    if ($table->save($msg)) {
                        $this->Flash->success("Status updated from '{$oldStatus}' to '{$data['status']}'.");
                        return $this->redirect(['action' => 'view', $id]);
                    }
                    $this->Flash->error('Failed to update status.');
                } else {
                    $this->Flash->error('Invalid status selected.');
                }
            }
        }

        $statuses = [
            'unread'      => 'Unread',
            'read'        => 'Read',
            'in_progress' => 'In Progress',
            'closed'      => 'Closed',
        ];

        $this->set(compact('msg', 'statuses'));
    }

    /**
     * GET/POST /admin/contact-messages/reply/{id}
     */
    public function reply(int $id)
    {
        $table = $this->fetchTable('ContactMessages');
        $msg   = $table->get($id, contain: ['Users']);

        if ($this->request->is('get') && $msg->status === 'unread') {
            $msg->status = 'read';
            $table->save($msg);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            $msg = $table->patchEntity($msg, $data, [
                'fields' => ['status', 'reply_note'],
            ]);

            $user = $this->request->getSession()->read('Auth.AdminUser');

            if (!empty($data['reply_note'])) {
                $msg->replied_at = DateTime::now();
                $msg->replied_by = $user['id'] ?? null;
            }

            if ($table->save($msg)) {
                $this->Flash->success('Reply saved successfully.');
                return $this->redirect(['action' => 'index', '?' => $this->request->getQueryParams()]);
            }
            $this->Flash->error('Failed to save reply. Please check the form.');
        }

        $statuses = [
            'unread'      => 'Unread',
            'read'        => 'Read',
            'in_progress' => 'In Progress',
            'closed'      => 'Closed',
        ];

        $this->set(compact('msg', 'statuses'));
    }

    /**
     * GET /admin/contact-messages/export
     */
    public function export()
    {
        $this->request->allowMethod(['get']);
        $this->disableAutoRender();

        $table = $this->fetchTable('ContactMessages');
        $rows  = $table->find()
            ->select(['id','name','email','message','status','created','modified'])
            ->orderByDesc('created')
            ->all();

        $filename = 'contact_messages_' . DateTime::now()->format('Ymd_His') . '.csv';

        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename);

        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['ID','Name','Email','Message','Status','Created','Modified']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->id,
                (string)$r->name,
                (string)$r->email,
                (string)$r->message,
                (string)$r->status,
                $r->created?->format('Y-m-d H:i:s') ?? '',
                $r->modified?->format('Y-m-d H:i:s') ?? '',
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response->withStringBody($csv);
    }

    /**
     * POST /admin/contact-messages/markAllRead
     */
    public function markAllRead()
    {
        $this->request->allowMethod(['post']);
        $table = $this->fetchTable('ContactMessages');

        $updated = $table->updateAll(
            ['status' => 'read', 'modified' => DateTime::now()],
            ['status' => 'unread']
        );

        $updated === false
            ? $this->Flash->error('Failed to mark messages as read.')
            : $this->Flash->success("Marked {$updated} message(s) as read.");

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'ContactMessages', 'action' => 'index']);
    }

    /**
     * POST /admin/contact-messages/delete/{id}
     */
    public function delete(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);

        $table = $this->fetchTable('ContactMessages');
        $msg   = $table->get($id);

        if ($table->delete($msg)) {
            $this->Flash->success('Message deleted.');
        } else {
            $this->Flash->error('Delete failed.');
        }

        return $this->redirect($this->referer(['action' => 'index'], true));
    }
}
