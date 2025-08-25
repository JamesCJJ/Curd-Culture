<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\FrozenTime;

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
        $status  = (string)$this->request->getQuery('status'); // '', new, in_progress, closed
        $from    = $this->request->getQuery('from');
        $to      = $this->request->getQuery('to');

        $table = $this->fetchTable('ContactMessages');

        $query = $table->find()
            ->contain(['Users'])
            ->orderByDesc('ContactMessages.created');

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
            // new / in_progress / closed
            $query->where(['ContactMessages.status' => $status]);
        }

        if (!empty($from)) {
            $query->where(['ContactMessages.created >=' => new FrozenTime($from . ' 00:00:00')]);
        }
        if (!empty($to)) {
            $query->where(['ContactMessages.created <=' => new FrozenTime($to . ' 23:59:59')]);
        }

        $messages = $this->paginate($query, [
            'limit' => 12,
        ]);


        $statuses = [
            ''            => 'All',
            'new'         => 'New',
            'in_progress' => 'In progress',
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

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            $msg = $table->patchEntity($msg, $data, [
                'fields' => ['status', 'reply_note'],
            ]);

            $user = $this->request->getSession()->read('Auth.AdminUser');

            if (!empty($data['reply_note'])) {
                $msg->replied_at = \Cake\I18n\FrozenTime::now();
                $msg->replied_by = $user['id'] ?? null;
            }

            if ($table->save($msg)) {
                $this->Flash->success('Saved reply.');
                return $this->redirect(['action' => 'index', '?' => $this->request->getQueryParams()]);
            }
            $this->Flash->error('Failed to save. Please check the form.');
        }


        $statuses = [
            'new'         => 'New',
            'in_progress' => 'In progress',
            'closed'      => 'Closed',
        ];

        $this->set(compact('msg', 'statuses'));
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
