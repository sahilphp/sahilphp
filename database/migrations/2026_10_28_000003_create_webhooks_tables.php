<?php

declare(strict_types=1);

use Sahil\Database\Schema\Schema;
use Sahil\Database\Schema\Table;
use Sahil\Migrations\Migration;

return new class () extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('webhook_endpoints', static function (Table $table): void {
            $table->string('id', 64)->primary();
            $table->string('url');
            $table->text('events');
            $table->integer('active')->default(1);
            $table->text('secret'); // sealed via Encrypter (APP_KEY)
            $table->integer('created_at');
            $table->integer('updated_at');
        });

        $schema->create('webhook_deliveries', static function (Table $table): void {
            $table->string('id', 64)->primary();
            $table->string('endpoint_id', 64);
            $table->string('event');
            $table->string('status', 20);
            $table->integer('attempts')->default(0);
            $table->integer('last_status')->nullable();
            $table->text('error')->nullable();
            $table->text('payload');
            $table->integer('created_at');
            $table->integer('completed_at')->nullable();
            $table->index('endpoint_id');
        });
    }

    public function down(Schema $schema): void
    {
        $schema->drop('webhook_deliveries');
        $schema->drop('webhook_endpoints');
    }
};
