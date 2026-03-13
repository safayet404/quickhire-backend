<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_listings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // logged-in seeker
            $table->string('name');
            $table->string('email');
            $table->string('resume_link');
            $table->text('cover_note')->nullable();
            $table->string('status')->default('pending'); // pending | reviewed | accepted | rejected
            $table->text('status_note')->nullable();      // employer/admin can leave a note
            $table->timestamps();

            $table->unique(['job_id', 'email']); // prevent duplicate applications
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
