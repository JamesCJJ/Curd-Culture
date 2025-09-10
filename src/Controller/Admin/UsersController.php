<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;

/**
 * Admin Users Controller
 * User management system for administrators
 */
class UsersController extends AppController
{
    /**
     * Index method - List all users
     */
    public function index()
    {
        $query = trim((string)$this->request->getQuery('q'));
        $role = (string)$this->request->getQuery('role');
        $status = (string)$this->request->getQuery('status');
        
        $table = $this->fetchTable('Users');
        
        $usersQuery = $table->find()
            ->orderByDesc('Users.created');
            
        // Search functionality
        if ($query !== '') {
            $usersQuery->where([
                'OR' => [
                    'Users.name LIKE' => '%' . $query . '%',
                    'Users.email LIKE' => '%' . $query . '%',
                ]
            ]);
        }
        
        // Filter by role
        if ($role !== '') {
            $usersQuery->where(['Users.role' => $role]);
        }
        
        // Filter by status
        if ($status !== '') {
            $usersQuery->where(['Users.status' => $status]);
        }
        
        // Pagination
        $limit = 20;
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;
        
        $users = $usersQuery->limit($limit)->offset($offset)->all();
        $totalCount = $usersQuery->count();
        $totalPages = (int)ceil($totalCount / $limit);
        
        // Statistics
        $stats = [
            'total' => $table->find()->count(),
            'admins' => $table->find()->where(['role' => 'admin'])->count(),
            'customers' => $table->find()->where(['role' => 'customer'])->count(),
            'active' => $table->find()->where(['status' => 'active'])->count(),
            'inactive' => $table->find()->where(['status !=' => 'active'])->count(),
        ];
        
        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ];
        
        $this->set(compact('users', 'pagination', 'stats', 'query', 'role', 'status'));
    }
    
    /**
     * View method - Display user details
     */
    public function view($id = null)
    {
        $user = $this->fetchTable('Users')->get($id);
        
        // Get user's orders if they're a customer
        $orders = [];
        if ($user->role === 'customer') {
            $orders = $this->fetchTable('Orders')
                ->find()
                ->where(['user_id' => $user->id])
                ->orderByDesc('created')
                ->limit(10)
                ->all();
        }
        
        $this->set(compact('user', 'orders'));
    }
    
    /**
     * Add method - Create new user
     */
    public function add()
    {
        $table = $this->fetchTable('Users');
        $user = $table->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Hash password
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $user = $table->patchEntity($user, $data);
            
            if ($table->save($user)) {
                $this->Flash->success(__('User has been created successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            
            $this->Flash->error(__('Unable to create user. Please check the form and try again.'));
        }
        
        $this->set(compact('user'));
    }
    
    /**
     * Edit method - Update user
     */
    public function edit($id = null)
    {
        $table = $this->fetchTable('Users');
        $user = $table->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
            
            $user = $table->patchEntity($user, $data);
            
            if ($table->save($user)) {
                $this->Flash->success(__('User has been updated successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            
            $this->Flash->error(__('Unable to update user. Please check the form and try again.'));
        }
        
        $this->set(compact('user'));
    }
    
    /**
     * Delete method - Remove user
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Users');
        $user = $table->get($id);
        
        if ($table->delete($user)) {
            $this->Flash->success(__('User has been deleted successfully.'));
        } else {
            $this->Flash->error(__('Unable to delete user.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus($id = null)
    {
        $this->request->allowMethod(['post']);
        $table = $this->fetchTable('Users');
        $user = $table->get($id);
        
        // Get current admin user
        $identity = $this->request->getAttribute('identity');
        $currentUserId = $identity ? $identity->get('id') : null;
        
        // Prevent admin from toggling their own status
        if ($currentUserId && (int)$user->id === (int)$currentUserId) {
            $this->Flash->error(__('You cannot change your own status.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        
        if ($table->save($user)) {
            $this->Flash->success(__('User status updated successfully.'));
        } else {
            $this->Flash->error(__('Unable to update user status.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Export users to CSV
     */
    public function export()
    {
        $this->disableAutoRender();
        
        $users = $this->fetchTable('Users')->find()
            ->select([
                'id', 'name', 'email', 'role', 'status', 
                'timezone', 'language', 'theme', 'created', 'modified'
            ])
            ->orderByDesc('created')
            ->all();
        
        $filename = 'users_' . DateTime::now()->format('Ymd_His') . '.csv';
        
        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename);
        
        $out = fopen('php://temp', 'r+');
        fputcsv($out, [
            'ID', 'Name', 'Email', 'Role', 'Status', 'Timezone', 
            'Language', 'Theme', 'Created', 'Modified'
        ]);
        
        foreach ($users as $user) {
            fputcsv($out, [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->status,
                $user->timezone,
                $user->language,
                $user->theme,
                $user->created?->format('Y-m-d H:i:s') ?? '',
                $user->modified?->format('Y-m-d H:i:s') ?? '',
            ]);
        }
        
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        
        return $this->response->withStringBody($csv);
    }
}
