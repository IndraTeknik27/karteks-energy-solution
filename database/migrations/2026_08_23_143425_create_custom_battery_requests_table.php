<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_battery_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('chemistry', 50)->nullable()->comment('Li-ion, LiFePO4, Lainnya');
            $table->string('voltage', 50)->nullable();
            $table->string('capacity', 50)->nullable()->comment('Ah');
            $table->decimal('kwh', 10, 2)->nullable();
            $table->string('application')->nullable();
            $table->string('current_load')->nullable()->comment('misal: 50A continuous');
            $table->json('dimensions')->nullable()->comment('length, width, height in mm');
            $table->integer('quantity')->default(1);
            $table->date('deadline')->nullable();
            $table->longText('description')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', [
                'submitted', 'under_review', 'revision_requested', 'quoted',
                'approved', 'rejected', 'in_production', 'completed', 'cancelled',
            ])->default('submitted');
            $table->decimal('estimated_price', 15, 2)->nullable();
            $table->decimal('final_price', 15, 2)->nullable();
            $table->integer('revision_count')->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('assigned_to');
            $table->index('status');
        });

        Schema::create('custom_battery_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('custom_battery_requests')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->integer('file_size')->nullable()->comment('bytes');
            $table->enum('uploaded_by', ['customer', 'admin'])->default('customer');
            $table->timestamps();

            $table->index('request_id');
        });

        Schema::create('custom_battery_request_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('custom_battery_requests')->cascadeOnDelete();
            $table->integer('revision_number');
            $table->enum('requested_by', ['admin', 'customer'])->default('admin');
            $table->text('admin_note')->nullable();
            $table->text('customer_response')->nullable();
            $table->json('field_changes')->nullable()->comment('perubahan field yang diminta');
            $table->enum('status', ['pending', 'responded', 'accepted'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['request_id', 'revision_number'], 'cbr_revisions_req_rev_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_battery_request_revisions');
        Schema::dropIfExists('custom_battery_request_files');
        Schema::dropIfExists('custom_battery_requests');
    }
};