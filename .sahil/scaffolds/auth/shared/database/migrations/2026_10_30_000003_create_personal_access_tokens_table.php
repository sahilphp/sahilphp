<?php

declare(strict_types=1);

use Sahil\Database\Schema\Schema;
use Sahil\Database\Schema\Table;
use Sahil\Migrations\Migration;

/**
 * First-party API tokens (Sahil\Auth\Tokens). Only the SHA-256 digest of
 * the secret is stored; the plaintext is shown once at creation.
 */
return new class () extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('personal_access_tokens', static function (Table $table): void {
            $table->id();
            $table->string('tokenable_type');
            $table->integer('tokenable_id');
            $table->string('name');
            $table->string('token_id', 64)->unique();
            $table->string('token_hash', 64);
            $table->text('abilities');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index('expires_at');
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('personal_access_tokens');
    }
};
