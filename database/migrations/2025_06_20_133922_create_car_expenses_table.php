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
        Schema::create('car_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('stock_number');
            $table->string('expenses_for');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10);
            $table->unsignedBigInteger('created_by'); // Auth user ID
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes
            
            // Add foreign key constraint
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index('stock_number');
            $table->index('created_at');
            $table->index('created_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_expenses');
    }
};