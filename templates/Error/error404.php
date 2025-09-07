<?php
/**
 * Custom 404 Page Not Found Error Template
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;

$this->layout = 'error';
$this->assign('title', 'Page Not Found - 404');
?>

<section class="error-404">
    <div class="error-content">
        <div class="error-icon">
            <span class="icon-404">404</span>
        </div>
        
        <h1 class="error-title">Oops! Page Not Found</h1>
        
        <p class="error-message">
            We're sorry, but the page you're looking for doesn't exist. 
            It might have been moved, deleted, or you may have entered the wrong URL.
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
            
            <?= $this->Html->link(
                '📞 Contact Us',
                ['prefix' => false, 'controller' => 'ContactMessages', 'action' => 'add'],
                ['class' => 'btn btn-secondary']
            ) ?>
            
            <button onclick="history.back()" class="btn btn-outline">
                ← Go Back
            </button>
        </div>
        
        <div class="error-suggestions">
            <h3>What you can do:</h3>
            <ul>
                <li>Check the URL for typos</li>
                <li>Go back to the previous page</li>
                <li>Visit our homepage to find what you're looking for</li>
                <li>Contact us if you think this is an error</li>
            </ul>
        </div>
    </div>
</section>

<style>
.error-404 {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.error-content {
    max-width: 600px;
    text-align: center;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem 2rem;
    border: 1px solid #e2e8f0;
}

.error-icon {
    margin-bottom: 2rem;
}

.icon-404 {
    display: inline-block;
    font-size: 6rem;
    font-weight: 900;
    color: #3b82f6;
    text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
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
    background: #f8fafc;
    border: 1px solid #e2e8f0;
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
    background: #e2e8f0;
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
    margin: 0 0 2rem 0;
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
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
    border-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #64748b;
    color: #fff;
    border-color: #64748b;
}

.btn-secondary:hover {
    background: #475569;
    border-color: #475569;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.4);
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

.error-suggestions {
    text-align: left;
    background: #f8fafc;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.error-suggestions h3 {
    margin: 0 0 1rem 0;
    font-size: 1.125rem;
    color: #1e293b;
    font-weight: 600;
}

.error-suggestions ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #64748b;
}

.error-suggestions li {
    margin: 0.5rem 0;
    line-height: 1.5;
}

/* Dark theme support */
.theme-dark .error-404 {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.theme-dark .error-content {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}

.theme-dark .error-title {
    color: #f1f5f9;
}

.theme-dark .error-message {
    color: #94a3b8;
}

.theme-dark .error-details {
    background: #0f172a;
    border-color: #334155;
}

.theme-dark .error-details p {
    color: #94a3b8;
}

.theme-dark .error-details code {
    background: #334155;
    color: #e2e8f0;
}

.theme-dark .error-suggestions {
    background: #0f172a;
    border-color: #334155;
}

.theme-dark .error-suggestions h3 {
    color: #f1f5f9;
}

.theme-dark .error-suggestions li {
    color: #94a3b8;
}

/* Responsive design */
@media (max-width: 768px) {
    .error-404 {
        padding: 1rem;
    }
    
    .error-content {
        padding: 2rem 1.5rem;
    }
    
    .icon-404 {
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
