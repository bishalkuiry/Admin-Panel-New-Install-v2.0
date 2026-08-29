    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Product Catalog Tables
     * 
     * All product-related tables including products, variants, images, and inventory management.
     */
    return new class extends Migration
    {
        public function up(): void
        {
            // ============================================
            // PRODUCTS
            // ============================================
            
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('sku')->unique();
                $table->string('vendor_sku')->nullable();
                $table->string('barcode')->nullable();
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->json('nutritional_info')->nullable();
                $table->decimal('price', 10, 2);
                $table->decimal('commission', 10, 2)->nullable()->comment('Product specific commission percentage or flat value');
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->string('tax_class')->nullable();
                $table->string('hsn_code')->nullable();
                $table->integer('quantity')->default(0);
                $table->integer('low_stock_threshold')->default(5);
                $table->boolean('track_inventory')->default(true);
                $table->boolean('allow_backorder')->default(false);
                $table->string('unit')->default('piece');
                $table->decimal('weight', 8, 2)->nullable();
                $table->string('weight_unit')->default('g');
                $table->decimal('length', 8, 2)->nullable();
                $table->decimal('width', 8, 2)->nullable();
                $table->decimal('height', 8, 2)->nullable();
                $table->string('dimension_unit')->default('cm');
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
                $table->string('brand')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->string('status')->default('draft');
                $table->string('visibility')->default('global');
                $table->date('manufacture_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->integer('shelf_life_days')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('meta_image')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('parent_product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['store_id', 'status']);
                $table->index(['category_id', 'is_active']);
                $table->index(['brand_id', 'is_active']);
                $table->index(['status', 'visibility']);
                $table->index('expiry_date');
                $table->index('updated_at');
                $table->index(['is_active', 'status', 'name']);
                $table->index(['is_active', 'status', 'sku']);
                $table->index('name');
                $table->index('sku');
                $table->index('slug');
                $table->fullText(['name', 'sku', 'description']);
            });

            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('image');
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->index(['product_id', 'is_primary']);
            });

            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('sku')->unique();
                $table->string('barcode')->nullable();
                $table->string('name')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_value')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->default('g');
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active']);
            $table->index('barcode');
        });

        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_variant_id', 'attribute_value_id'], 'pva_unique');
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id', 'attribute_value_id'], 'pa_unique');
        });

        Schema::create('product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'tag_id']);
        });

        Schema::create('product_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('type')->default('certificate');
            $table->boolean('is_public')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'type']);
        });

        Schema::create('product_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status')->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });

        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
        Schema::dropIfExists('product_imports');
        Schema::dropIfExists('product_attachments');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
