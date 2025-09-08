<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;

/**
 * Admin/ContactMessages controller
 */
class ContactMessagesController extends AppController
{
    /**
     * GET /admin/contact-messages
     */
    public function index()
    {
        $q       = trim((string)$this->request->getQuery('q'));
        $status  = (string)$this->request->getQuery('status'); // '', unread, read, in_progress, closed
        $from    = $this->request->getQuery('from');
        $to      = $this->request->getQuery('to');

        $table = $this->fetchTable('ContactMessages');

        $query = $table->find()
            ->contain(['Users'])
            ->orderByDesc('ContactMessages.created'); // Cake 5

        if ($q !== '') {
            $query->where([
                'OR' => [
                    'ContactMessages.name LIKE'    => '%' . $q . '%',
                    'ContactMessages.email LIKE'   => '%' . $q . '%',
                    'ContactMessages.message LIKE' => '%' . $q . '%',
                ],
            ]);
        }

        if ($status !== '') {
            $query->where(['ContactMessages.status' => $status]);
        }

        if (!empty($from)) {
            $query->where(['ContactMessages.created >=' => new DateTime($from . ' 00:00:00')]);
        }
        if (!empty($to)) {
            $query->where(['ContactMessages.created <=' => new DateTime($to . ' 23:59:59')]);
        }

        $messages = $this->paginate($query, ['limit' => 12]);

        $statuses = [
            ''            => 'All',
            'unread'      => 'Unread',
            'read'        => 'Read',
            'in_progress' => 'In Progress',
            'closed'      => 'Closed',
        ];

        $this->set(compact('messages', 'q', 'status', 'from', 'to', 'statuses'));
    }

    /**
     * GET/POST /admin/contact-messages/view/{id}
     */
    public function view(int $id)
    {
        $table = $this->fetchTable('ContactMessages');
        $msg   = $table->get($id, contain: ['Users']);

        // Auto mark as read on GET if currently unread
        if ($this->request->is('get') && $msg->status === 'unread') {
            $msg->status = 'read';
            $table->save($msg);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            // Status-only update
            if (empty($data['reply_note']) && !empty($data['status'])) {
                if (in_array($data['status'], ['read', 'in_progress', 'closed'], true)) {
                    $oldStatus = $msg->status;
                    $msg->status = $data['status'];

                    if ($table->save($msg)) {
                        $this->Flash->success("Status updated from '{$oldStatus}' to '{$data['status']}'.");
                        return $this->redirect(['action' => 'view', $id]);
                    }
                    $this->Flash->error('Failed to update status.');
                } else {
                    $this->Flash->error('Invalid status selected.');
                }
            } else {
                // Reply + optional status
                $msg = $table->patchEntity($msg, $data, [
                    'fields' => ['status', 'reply_note'],
                ]);

                $user = $this->request->getSession()->read('Auth.AdminUser');

                if (!empty($data['reply_note'])) {
                    $msg->replied_at = DateTime::now();
                    $msg->replied_by = $user['id'] ?? null;
                }

                if ($table->save($msg)) {
                    $this->Flash->success('Saved reply.');
                    return $this->redirect(['action' => 'index', '?' => $this->request->getQueryParams()]);
                }
                $this->Flash->error('Failed to save. Please check the form.');
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
     * GET /admin/contact-messages/export
     * Direct download CSV (no flash, no redirect)
     */
    public function export()
    {
        $this->request->allowMethod(['get']);
        $this->disableAutoRender(); // important: no templates, no extra output

        $query = $this->ContactMessages->find()
            ->select(['id','name','email','message','status','created','modified'])
            ->orderByDesc('created'); // Cake 5

        $rows = $query->all();

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
        $now = DateTime::now();

        $updated = $this->ContactMessages->updateAll(
            ['status' => 'read', 'modified' => $now],
            ['status' => 'unread']
        );

        $updated === false
            ? $this->Flash->error('Failed to mark messages as read.')
            : $this->Flash->success("Marked {$updated} message(s) as read.");

        return $this->redirect(['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'index']);
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
