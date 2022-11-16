<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        date_default_timezone_set('America/Bahia');

        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('category')->default('1'); // 1 -> Usuário padrão, 2 -> ONG
            $table->string('email', 100);
            $table->string('password', 200);
            $table->string('name', 100);
            $table->string('phone', 14)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('facebook', 120)->nullable();
            $table->string('biography', 240)->nullable();
            $table->integer('genre')->nullable();
            $table->date('birthdate');
            $table->string('city', 100)->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->string('work', 100)->nullable();
            $table->string('rua', 200)->nullable();
            $table->string('bairro', 200)->nullable();
            $table->string('avatar', 100)->default('default.jpg');
            $table->string('cover', 100)->default('cover.jpg');
            $table->string('token', 200)->nullable();
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('user_relations', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('user_from');
            $table->integer('user_to');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('pets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('name', 100);
            $table->integer('id_user');
            $table->string('species');
            $table->string('biography', 240)->nullable();
            $table->date('birthdate');
            $table->integer('castrated')->nullable();
            $table->string('avatar', 100)->default('default_pet.jpg');
            $table->string('cover', 100)->default('default_cover_pet.jpg');
            $table->integer('genre')->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->integer('size')->nullable();
            $table->integer('fur')->nullable();
            $table->integer('situation')->nullable();
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('user_relations_pets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_user');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('situation')->default('0'); //0-> Post normal, 1->Pet perdido, 2 ->Pet encontrado, 3->Pet em tratamento, 4->Situação resolvida
            $table->integer('id_user');
            $table->json('marked_pets');
            $table->string('type', 20);
            $table->text('body');
            $table->text('subtitle', 240)->nullable();
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('post_likes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_post');
            $table->integer('id_user');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_post');
            $table->integer('id_user');
            $table->text('body');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('vaccines_card', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_pet');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('rga', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_pet');
            $table->string('code');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });

        Schema::create('location_pet', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->integer('id_pet');
            $table->integer('id_post');
            $table->integer('id_post_comment');
            $table->string('rua');
            $table->string('bairro');
            $table->string('cidade');
            $table->integer('status')->default('1'); // 1 -> Ativo, 2 -> Desativado
            $table->dateTime('date_register');
            $table->dateTime('date_change')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_relations');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('user_relations_pets');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('posts_likes');
        Schema::dropIfExists('posts_comments');
        Schema::dropIfExists('vaccines_card');
        Schema::dropIfExists('rga');
        Schema::dropIfExists('location_pet');
    }
};
