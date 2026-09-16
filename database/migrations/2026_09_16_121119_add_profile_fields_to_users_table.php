<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('first_name')->nullable()->after('phone');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('middle_name');

            $table->string('utm_source')->nullable()->after('date_of_birth');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_content')->nullable()->after('utm_campaign');
            $table->string('referral_code')->nullable()->after('utm_content');

            $table->string('kyc_status')->default('not_started')->after('referral_code');
            $table->boolean('is_blocked')->default(false)->after('kyc_status');
            $table->text('block_reason')->nullable()->after('is_blocked');

            $table->boolean('two_factor_enabled')->default(false)->after('block_reason');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'first_name',
                'last_name',
                'middle_name',
                'date_of_birth',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'referral_code',
                'kyc_status',
                'is_blocked',
                'block_reason',
                'two_factor_enabled',
                'last_login_at',
            ]);
        });
    }
};
