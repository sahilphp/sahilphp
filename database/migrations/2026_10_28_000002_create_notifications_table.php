<?php

declare(strict_types=1);

use Sahil\Database\Schema\Schema;
use Sahil\Database\Schema\Table;
use Sahil\Migrations\Migration;

return new class () extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('notifications', static function (Table $table): void {
            $table->string('id', 64)->primary();
            $table->string('notifiable_type');
            $table->string('notifiable_id');
            $table->string('type');
            $table->text('data');
            $table->integer('read_at')->nullable();
            $table->integer('created_at');
            $table->index('notifiable_type');
            $table->index('notifiable_id');
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('notifications');
    }
};
