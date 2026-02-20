<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('numero_documento', 64)->nullable()->index();
            $table->string('ruc', 32)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('direccion')->nullable();
            $table->string('sucursal')->nullable();
            $table->json('cliente')->nullable(); // { nombre, dni_ruc, direccion }
            $table->json('boleta')->nullable();  // { numero, fecha_emision, fecha_vencimiento, moneda, forma_pago }
            $table->json('detalles')->nullable(); // [{ descripcion, cantidad, precio_unitario, descuento_unitario, importe_total_unitario }]
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_ventas');
    }
};
