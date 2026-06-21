<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_documents', function (Blueprint $table) {
            $table->string('review_status')->default('pending')->after('ocr_result');
            $table->text('review_comment')->nullable()->after('review_status');
        });
    }

    public function down(): void
    {
        Schema::table('case_documents', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'review_comment']);
        });
    }
};
