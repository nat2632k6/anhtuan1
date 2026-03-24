# Thumbnail Feature - Migration Guide for Existing Products

## Overview
This guide helps you migrate existing products to use the new thumbnail image system. You have two options: automatic migration or manual migration.

## Option 1: Automatic Migration (Recommended)

### Step 1: Create a Seeder
Create a new seeder to migrate existing product images:

```bash
php artisan make:seeder MigrateProductImagesToThumbnailsSeeder
```

### Step 2: Add Migration Logic
Edit `database/seeders/MigrateProductImagesToThumbnailsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class MigrateProductImagesToThumbnailsSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        foreach ($products as $product) {
            // Skip if already has thumbnail images
            if ($product->images()->exists()) {
                echo "Product {$product->id} already has images, skipping...\n";
                continue;
            }
            
            // Create thumbnail from main image
            if ($product->image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $product->image,
                    'is_main' => true,
                    'order' => 0
                ]);
                echo "Migrated product {$product->id}: {$product->name}\n";
            } else {
                echo "Product {$product->id} has no image, skipping...\n";
            }
        }
        
        echo "Migration completed!\n";
    }
}
```

### Step 3: Run the Seeder
```bash
php artisan db:seed --class=MigrateProductImagesToThumbnailsSeeder
```

## Option 2: Manual Migration via Tinker

### Step 1: Open Tinker
```bash
php artisan tinker
```

### Step 2: Run Migration Commands
```php
// Get all products
$products = App\Models\Product::all();

// Migrate each product
foreach ($products as $product) {
    if ($product->image && !$product->images()->exists()) {
        App\Models\ProductImage::create([
            'product_id' => $product->id,
            'image' => $product->image,
            'is_main' => true,
            'order' => 0
        ]);
        echo "Migrated: {$product->name}\n";
    }
}

// Verify migration
App\Models\Product::with('images')->get()->each(function($p) {
    echo "{$p->name}: {$p->images()->count()} images\n";
});
```

## Option 3: Database Query

### Direct SQL Migration
```sql
-- Insert existing product images as thumbnails
INSERT INTO product_images (product_id, image, is_main, order, created_at, updated_at)
SELECT id, image, 1, 0, NOW(), NOW()
FROM products
WHERE image IS NOT NULL
AND id NOT IN (SELECT DISTINCT product_id FROM product_images);
```

## Verification Steps

### 1. Check Migration Status
```php
// In Tinker
$products = App\Models\Product::with('images')->get();
$products->each(function($p) {
    echo "{$p->name}: {$p->images()->count()} images\n";
});
```

### 2. Verify Main Images
```php
// Check main images are set correctly
$mainImages = App\Models\ProductImage::where('is_main', true)->count();
echo "Total main images: $mainImages\n";
```

### 3. Test Product Display
1. Go to product listing page
2. Verify images display correctly
3. Go to product detail page
4. Verify main image displays
5. Verify thumbnails display
6. Test clicking thumbnails

## Rollback Plan

If you need to rollback the migration:

### Option 1: Rollback Database
```bash
php artisan migrate:rollback
```

### Option 2: Delete Migrated Data
```php
// In Tinker
App\Models\ProductImage::truncate();
```

### Option 3: Restore from Backup
```bash
# Restore database from backup
mysql -u user -p database < backup.sql
```

## Post-Migration Tasks

### 1. Update Product Listing View
If you have a custom product listing, update it to use the new thumbnail:

```blade
<!-- Old way -->
<img src="{{ asset($product->image) }}" alt="{{ $product->name }}">

<!-- New way -->
<img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}">
```

### 2. Update Product Detail View
Already updated in the implementation, but verify:

```blade
<!-- Main image -->
<img src="{{ asset($product->mainImage->image) }}" alt="{{ $product->name }}">

<!-- Thumbnails -->
@foreach($product->images as $image)
    <img src="{{ asset($image->image) }}" alt="Thumbnail">
@endforeach
```

### 3. Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
```

### 4. Test All Features
- [ ] Product listing displays images
- [ ] Product detail displays main image
- [ ] Thumbnails display correctly
- [ ] Clicking thumbnail switches image
- [ ] Admin can upload new images
- [ ] Admin can set main image
- [ ] Admin can delete images

## Batch Migration Script

Create a custom command for easier migration:

```bash
php artisan make:command MigrateProductImages
```

Edit `app/Console/Commands/MigrateProductImages.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;

class MigrateProductImages extends Command
{
    protected $signature = 'migrate:product-images {--force : Skip confirmation}';
    protected $description = 'Migrate existing product images to thumbnail system';

    public function handle()
    {
        $products = Product::all();
        $count = 0;

        $this->info("Found {$products->count()} products");

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to migrate all product images?')) {
                return;
            }
        }

        foreach ($products as $product) {
            if ($product->image && !$product->images()->exists()) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $product->image,
                    'is_main' => true,
                    'order' => 0
                ]);
                $count++;
                $this->line("✓ Migrated: {$product->name}");
            }
        }

        $this->info("\nMigration completed! {$count} products migrated.");
    }
}
```

Run with:
```bash
php artisan migrate:product-images
php artisan migrate:product-images --force
```

## Troubleshooting

### Issue: Images not showing after migration
**Solution:**
1. Verify file paths are correct
2. Check files exist in `public/images/products/`
3. Clear browser cache
4. Run `php artisan storage:link` if using storage disk

### Issue: Duplicate images created
**Solution:**
1. Check if seeder was run multiple times
2. Delete duplicates: `ProductImage::where('is_main', false)->delete();`
3. Re-run migration

### Issue: Main image not set
**Solution:**
```php
// In Tinker
$images = App\Models\ProductImage::where('is_main', false)->get();
$images->each(function($img) {
    $img->update(['is_main' => true]);
});
```

### Issue: Some products have no images
**Solution:**
```php
// Find products without images
$productsWithoutImages = App\Models\Product::doesntHave('images')->get();
$productsWithoutImages->each(function($p) {
    echo "{$p->name}: {$p->image}\n";
});
```

## Performance Optimization

### Add Database Indexes
```sql
ALTER TABLE product_images ADD INDEX idx_product_id (product_id);
ALTER TABLE product_images ADD INDEX idx_is_main (is_main);
ALTER TABLE product_images ADD INDEX idx_order (order);
```

### Eager Load Images
```php
// Good - Avoids N+1 queries
$products = Product::with('images', 'mainImage')->get();

// Bad - Causes N+1 queries
$products = Product::all();
foreach ($products as $product) {
    $images = $product->images;
}
```

## Data Validation

### Check for Missing Images
```php
// Find products with image path but no file
$products = Product::all();
$missing = [];

foreach ($products as $product) {
    if ($product->image && !file_exists(public_path($product->image))) {
        $missing[] = $product->name;
    }
}

if (!empty($missing)) {
    echo "Missing files: " . implode(', ', $missing);
}
```

### Verify Image Integrity
```php
// Check all images exist
$images = App\Models\ProductImage::all();
$missing = 0;

foreach ($images as $image) {
    if (!file_exists(public_path($image->image))) {
        echo "Missing: {$image->image}\n";
        $missing++;
    }
}

echo "Total missing: $missing\n";
```

## Migration Statistics

After migration, check statistics:

```php
// In Tinker
$stats = [
    'total_products' => App\Models\Product::count(),
    'products_with_images' => App\Models\Product::has('images')->count(),
    'total_images' => App\Models\ProductImage::count(),
    'main_images' => App\Models\ProductImage::where('is_main', true)->count(),
    'thumbnail_images' => App\Models\ProductImage::where('is_main', false)->count(),
];

foreach ($stats as $key => $value) {
    echo "$key: $value\n";
}
```

## Backup Before Migration

### Create Database Backup
```bash
# MySQL
mysqldump -u user -p database > backup_before_migration.sql

# SQLite
cp database/database.sqlite database/database.sqlite.backup
```

### Create File Backup
```bash
# Backup product images
cp -r public/images/products public/images/products.backup
```

## Post-Migration Checklist

- [ ] Database migration completed
- [ ] All products have thumbnail images
- [ ] Main images are set correctly
- [ ] Product listing displays images
- [ ] Product detail displays images
- [ ] Thumbnail switching works
- [ ] Admin can upload new images
- [ ] Admin can set main image
- [ ] Admin can delete images
- [ ] Images are deleted with product
- [ ] Cache cleared
- [ ] Backup created
- [ ] Performance verified

## Support

If you encounter issues during migration:

1. Check the troubleshooting section above
2. Review the THUMBNAIL_FEATURE_GUIDE.md
3. Check Laravel logs in `storage/logs/`
4. Verify database migration ran: `php artisan migrate:status`
5. Test with a single product first

## Rollback Instructions

If you need to rollback:

```bash
# Rollback migration
php artisan migrate:rollback

# Or manually delete data
php artisan tinker
App\Models\ProductImage::truncate();
```

Then restore from backup:
```bash
# MySQL
mysql -u user -p database < backup_before_migration.sql

# SQLite
cp database/database.sqlite.backup database/database.sqlite
```
