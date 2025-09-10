<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateArticlesTags extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('articles_tags');
        $table->addColumn('article_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('tag_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addIndex(['article_id', 'tag_id'], ['name' => 'articles_tags_unique', 'unique' => true]);
        $table->addIndex(['tag_id'], ['name' => 'tag_key']);
        $table->create();
    }
}
