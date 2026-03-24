# Thumbnail Image Feature - Code Examples

## Model Examples

### Get Product with Images
```php
// Eager load images to avoid N+1 queries
$product = Product::with('images', 'mainImage')->find($id);

// Get main image
$mainImage = $product->mainImage;
echo $mainImage->image; // Output: images/products/1234567890_abc123.jpg

// Get all images
foreach ($product->images as $image) {
    echo $image->image;
    echo $image->is_main ? ' (Main)' : '';
}

// Get thumbnail (main or fallback)
echo $product->thumbnail; // Output: images/products/1234567890_abc123.jpg
```

### Create Product with Images
```php
// Create product
$product = Product::create([
    'name' => 'Product Name',
    'slug' => 'product-name',
    'price' => 99.99,
    'category_id' => 1,
    'stock' => 10,
    'image' => 'images/products/main.jpg'
]);

// Add thumbnail images
ProductImage::create([
    'product_id' => $product->id,
    'image' => 'images/products/thumb1.jpg',
    'is_main' => true,
    'order' => 0
]);

ProductImage::create([
    'product_id' => $product->id,
    'image' => 'images/products/thumb2.jpg',
    'is_main' => false,
    'order' => 1
]);
```

### Update Main Image
```php
// Set specific image as main
$image = ProductImage::find($imageId);
ProductImage::where('product_id', $image->product_id)->update(['is_main' => false]);
$image->update(['is_main' => true]);

// Or use the controller method
$imageController = new ProductImageController();
$imageController->setMain($image);
```

## Controller Examples

### Upload Multiple Images
```php
public function uploadMultiple(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    $order = ProductImage::where('product_id', $request->product_id)->max('order') ?? 0;

    foreach ($request->file('images') as $file) {
        $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/products'), $imageName);
        
        ProductImage::create([
            'product_id' => $request->product_id,
            'image' => 'images/products/' . $imageName,
            'is_main' => false,
            'order' => ++$order
        ]);
    }

    return response()->json(['success' => true, 'message' => 'Images uploaded successfully']);
}
```

### Delete Product with Images
```php
public function deleteProduct(Product $product)
{
    // Delete main image
    if ($product->image && file_exists(public_path($product->image))) {
        unlink(public_path($product->image));
    }
    
    // Delete all thumbnail images
    foreach ($product->images as $img) {
        if (file_exists(public_path($img->image))) {
            unlink(public_path($img->image));
        }
    }
    
    // Delete product and related images from database
    $product->delete();
    
    return redirect()->back()->with('success', 'Product deleted successfully');
}
```

## Blade Template Examples

### Display Product with Thumbnails
```blade
<div class="product-gallery">
    <!-- Main Image -->
    <div class="main-image">
        <img id="mainImage" 
             src="{{ asset($product->mainImage ? $product->mainImage->image : $product->image) }}" 
             alt="{{ $product->name }}"
             class="w-full h-auto">
    </div>

    <!-- Thumbnails -->
    <div class="thumbnails">
        @foreach($product->images as $image)
            <img src="{{ asset($image->image) }}" 
                 alt="Thumbnail"
                 class="thumbnail {{ $image->is_main ? 'active' : '' }}"
                 onclick="changeMainImage('{{ asset($image->image) }}', this, {{ $image->id }})">
        @endforeach
    </div>
</div>
```

### Admin Image Management
```blade
<div class="image-management">
    <h3>Current Images</h3>
    
    @if($product->images->count() > 0)
        <div class="image-grid">
            @foreach($product->images as $image)
                <div class="image-item">
                    <img src="{{ asset($image->image) }}" alt="Product image">
                    
                    @if(!$image->is_main)
                        <button onclick="setMainImage({{ $image->id }})" class="btn-primary">
                            Set as Main
                        </button>
                    @else
                        <span class="badge">Main Image</span>
                    @endif
                    
                    <button onclick="deleteImage({{ $image->id }})" class="btn-danger">
                        Delete
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <p>No images uploaded yet</p>
    @endif

    <!-- Upload New Images -->
    <div class="upload-section">
        <h4>Upload New Images</h4>
        <input type="file" id="imageUpload" multiple accept="image/*" onchange="uploadImages(event)">
    </div>
</div>
```

### Product Listing with Thumbnails
```blade
<div class="product-card">
    <div class="product-image">
        <img src="{{ asset($product->thumbnail) }}" 
             alt="{{ $product->name }}"
             class="w-full h-48 object-cover">
    </div>
    <div class="product-info">
        <h3>{{ $product->name }}</h3>
        <p class="price">{{ number_format($product->price) }}đ</p>
        <a href="{{ route('product.show', $product->slug) }}" class="btn">View Details</a>
    </div>
</div>
```

## JavaScript Examples

### Image Switching
```javascript
function changeMainImage(src, imgElement, imageId) {
    // Update main image display
    document.getElementById('mainImage').src = src;
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    imgElement.classList.add('active');
    
    // Update database if imageId provided
    if (imageId) {
        fetch(`/product-images/${imageId}/set-main`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error setting main image:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
```

### Image Upload
```javascript
function uploadImages(event) {
    const files = event.target.files;
    const productId = document.getElementById('product-id').value;
    
    for (let file of files) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('product_id', productId);
        
        fetch('/product-images/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Image uploaded successfully');
                location.reload(); // Refresh to show new image
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while uploading the image');
        });
    }
}
```

### Image Deletion
```javascript
function deleteImage(imageId) {
    if (!confirm('Are you sure you want to delete this image?')) {
        return;
    }
    
    fetch('/product-images/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({image_id: imageId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Image deleted successfully');
            location.reload(); // Refresh to remove image
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}
```

### Set Main Image
```javascript
function setMainImage(imageId) {
    if (!confirm('Set this image as the main product image?')) {
        return;
    }
    
    fetch(`/product-images/${imageId}/set-main`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Main image updated');
            location.reload(); // Refresh to show changes
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
```

## API Request Examples

### Upload Image (cURL)
```bash
curl -X POST http://localhost/product-images/upload \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -F "image=@/path/to/image.jpg" \
  -F "product_id=1"
```

### Set Main Image (cURL)
```bash
curl -X POST http://localhost/product-images/1/set-main \
  -H "X-CSRF-TOKEN: your-csrf-token"
```

### Delete Image (cURL)
```bash
curl -X POST http://localhost/product-images/delete \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -H "Content-Type: application/json" \
  -d '{"image_id": 1}'
```

## Query Examples

### Get Products with Main Images
```php
$products = Product::with('mainImage')
    ->where('category_id', 1)
    ->get();

foreach ($products as $product) {
    echo $product->name . ': ' . $product->mainImage->image;
}
```

### Get Products with All Images
```php
$products = Product::with('images')
    ->where('stock', '>', 0)
    ->paginate(12);

foreach ($products as $product) {
    echo $product->name . ' has ' . $product->images->count() . ' images';
}
```

### Get Main Images Only
```php
$mainImages = ProductImage::where('is_main', true)
    ->with('product')
    ->get();

foreach ($mainImages as $image) {
    echo $image->product->name . ': ' . $image->image;
}
```

## Validation Examples

### Validate Image Upload
```php
$request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    'product_id' => 'required|exists:products,id'
]);
```

### Validate Multiple Images
```php
$request->validate([
    'images' => 'required|array|min:1|max:10',
    'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    'product_id' => 'required|exists:products,id'
]);
```

## Error Handling Examples

### Try-Catch Upload
```php
try {
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $imageName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/products'), $imageName);
        
        ProductImage::create([
            'product_id' => $request->product_id,
            'image' => 'images/products/' . $imageName,
            'is_main' => false
        ]);
    }
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Error uploading image: ' . $e->getMessage()
    ], 500);
}
```

### JavaScript Error Handling
```javascript
fetch('/product-images/upload', {
    method: 'POST',
    body: formData,
    headers: {'X-CSRF-TOKEN': token}
})
.then(response => {
    if (!response.ok) throw new Error('Network response was not ok');
    return response.json();
})
.then(data => {
    if (data.success) {
        showSuccess('Image uploaded successfully');
    } else {
        showError(data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
    showError('An error occurred while uploading the image');
});
```

## Performance Optimization Examples

### Eager Load Images
```php
// Good - Avoids N+1 queries
$products = Product::with('images', 'mainImage')->get();

// Bad - Causes N+1 queries
$products = Product::all();
foreach ($products as $product) {
    $images = $product->images; // Query for each product
}
```

### Lazy Load Images
```php
// Load images only when needed
$product = Product::find($id);
$images = $product->images()->limit(5)->get();
```

### Cache Main Images
```php
// Cache main image for 1 hour
$mainImage = Cache::remember(
    "product_{$product->id}_main_image",
    3600,
    function () use ($product) {
        return $product->mainImage;
    }
);
```
