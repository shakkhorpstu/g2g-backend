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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // notifiable_id, notifiable_type (User or Psw)
            
            // Appointment Notifications
            $table->boolean('appointment_notification')->default(true);
            
            // Activity Update Channels
            $table->boolean('activity_email')->default(true);
            $table->boolean('activity_sms')->default(true);
            $table->boolean('activity_push')->default(true);
            
            // Promotional Channels  
            $table->boolean('promotional_email')->default(true);
            $table->boolean('promotional_sms')->default(true);
            $table->boolean('promotional_push')->default(true);
            
            $table->timestamps();
            
            // Ensure one setting record per user/psw
            $table->unique(['notifiable_id', 'notifiable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};