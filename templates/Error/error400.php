<?php
/**
 * Custom 400 Bad Request Error Template
 * @var \App\View\AppView $this
 * @var \Cake\Database\StatementInterface $error
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = 'error';
$this->assign('title', 'Bad Request - 400');

if (Configure::read('debug')) :
    $this->layout = 'dev_error';
    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');
    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
    <strong>SQL Query Params: </strong>
    <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>

<section class="error-400">
    <div class="error-content">
        <div class="error-icon">
            <span class="icon-400">400</span>
        </div>
        
        <h1 class="error-title">Bad Request</h1>
        
        <p class="error-message">
            The server couldn't understand your request. This might be due to invalid data or a malformed request.
        </p>
        
        <div class="error-details">
            <p><strong>Requested URL:</strong> <code><?= h($url) ?></code></p>
        </div>
        
        <div class="error-actions">
            <?= $this->Html->link(
                '🏠 Go to Homepage',
                ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                ['class' => 'btn btn-primary btn-large']
            ) ?>
            
            <button onclick="history.back()" class="btn btn-outline">
                ← Go Back
            </button>
        </div>
    </div>
</section>

<style>
.error-400 {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.error-content {
    max-width: 600px;
    text-align: center;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem 2rem;
    border: 1px solid #fecaca;
}

.error-icon {
    margin-bottom: 2rem;
}

.icon-400 {
    display: inline-block;
    font-size: 6rem;
    font-weight: 900;
    color: #dc2626;
    text-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
    line-height: 1;
}

.error-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem 0;
    line-height: 1.2;
}

.error-message {
    font-size: 1.125rem;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 2rem 0;
}

.error-details {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 0 0 2rem 0;
    text-align: left;
}

.error-details p {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
}

.error-details code {
    background: #fee2e2;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.8rem;
    color: #1e293b;
    word-break: break-all;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
}

.btn-primary:hover {
    background: #b91c1c;
    border-color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-outline {
    background: transparent;
    color: #64748b;
    border-color: #d1d5db;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #9ca3af;
    transform: translateY(-1px);
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}

/* Dark theme support */
.theme-dark .error-400 {
    background: linear-gradient(135deg, #1f1b1b 0%, #2d1b1b 100%);
}

.theme-dark .error-content {
    background: #1e293b;
    border-color: #7f1d1d;
    color: #e2e8f0;
}

.theme-dark .error-title {
    color: #f1f5f9;
}

.theme-dark .error-message {
    color: #94a3b8;
}

.theme-dark .error-details {
    background: #1f1b1b;
    border-color: #7f1d1d;
}

.theme-dark .error-details p {
    color: #94a3b8;
}

.theme-dark .error-details code {
    background: #2d1b1b;
    color: #e2e8f0;
}

/* Responsive design */
@media (max-width: 768px) {
    .error-400 {
        padding: 1rem;
    }
    
    .error-content {
        padding: 2rem 1.5rem;
    }
    
    .icon-400 {
        font-size: 4rem;
    }
    
    .error-title {
        font-size: 2rem;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}
</style>
