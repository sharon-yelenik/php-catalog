<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="/../static/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.css">
    <script src="https://unpkg.com/cloudinary-core/cloudinary-core-shrinkwrap.min.js"></script>
    <script src="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.js"></script>
    
</head>
<body class="products-page">

<!-- Navigation Bar -->
<nav>
    <ul>
    <li><a style="font-size:1.3rem;font-weight:75px;color:white;" href="../index.php">Catalog Creation App</a></li>
        <li style="margin-left:60px;"><a href="products.php">View Products</a></li>
        <li><a href="product_submission.php">Add Product</a></li>    
    </ul>
</nav>

<?php
require_once __DIR__ . '/../config/cloudinary_config.php';  // Make sure this file sets up the Cloudinary API
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Tag\ImageTag; 
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Overlay;
use Cloudinary\Transformation\Compass;
use Cloudinary\Transformation\Adjust;
use Cloudinary\Transformation\Source;
use Cloudinary\Transformation\Position;
use Cloudinary\Transformation\Transformation;
use Cloudinary\Api\Admin\AdminApi;

// Load .env file 
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = new Configuration($_ENV['CLOUDINARY_URL']);
$cld = new Cloudinary($config);

// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);

$api = new AdminAPI($config);


// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query the database for the product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product is not found, show an error
if (!$product) {
    echo "Product not found!";
    exit;
}
?>


    <?php
    if ($product['image_public_id']) {
            $image_url = $cld->image($product['image_public_id'])
                ->resize(
                    Resize::fill()
                        ->width(700)
                        ->height(700)
                        ->gravity(Gravity::autoGravity())
                )
                ->overlay(
                    Overlay::source(Source::image("cloudinary_logo")->resize(Resize::scale()->width(200)))
                        ->position(
                            (new Position())
                                ->gravity(Gravity::compass(Compass::northEast()))
                                ->offsetX(10)
                                ->offsetY(10)
                        )
                )
                ->toUrl();
                $metadata_result = $api->asset($product['image_public_id']);
                file_put_contents('metadata.txt', "Metadata:\n" . json_encode($metadata_result, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
                $price=$metadata_result['metadata']['price'];
                $sku=$metadata_result['metadata']['sku'];
                $category=$metadata_result['metadata']['category'];
                $category_labels = [
                    'clothes' => 'Clothes',
                    'accessories' => 'Accessories',
                    'footwear' => 'Footwear',
                    'home-living' => 'Home & Living',
                    'electronics' => 'Electronics',
                ];
        } else {
            $image_url = null;  // No image if not set
        }

        if ($product['video_moderation_status']==='rejected') {
                
            $video_url = null;
            $message = 'This video didn\'t meet our standards due to ' . $product['rejection_reason'] . ' in the image. Please try uploading a different one.';
        }
        elseif ($product['video_moderation_status']==='pending') {
            $video_url = null;  // No video if not set
            $message = "We're reviewing your video to ensure it meets our publication standards. Please refresh the page in a few minutes.";
        } elseif ($product['video_moderation_status']==='approved') {
            $video_url = $product['video_public_id']; 
            $message ="";
        }
        if ($product['video_public_id']==="invalid") {
            $message="This video was invalid. Try uploading a different one.";
        }
        
        ?>

<div class="container" style="margin-top:50px;">
    <div style="align-self: flex-start; text-align: left;">
        <p style="font-size:12px;">View a product in your catalog, including:</p>
        <ul style="font-size:10px;">
            <li style="margin-top:-3px;">The user-input name of the product retrieved from the database.</li>
            <li style="margin-top:3px;">The image description, auto-generated on upload using the <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon" target="_blank">Cloudinary's AI Content Analysis</a> add-on retrieved from the database.</li>
            <li style="margin-top:3px;">The image and video <a href="https://cloudinary.com/documentation/structured_metadata" target="_blank">structured metadata</a>, including SKU, price, and category, retrieved from Cloudinary.</li>
            <li style="margin-top:3px;">The image, whose <a href="https://cloudinary.com/documentation/php_image_manipulation#direct_url_building" target="_blank">delivery URL is generated</a> using the public ID retrieved from the database. Transformations applied to the image include:
                <ul>
                    <li style="margin-top:3px;"><a href="https://cloudinary.com/documentation/transformation_reference#c_fill" target="_blank">Resizing and cropping</a> to square dimensions, with automatic focus on the important parts using the <a href="https://cloudinary.com/documentation/transformation_reference#g_gravity" target="_blank">gravity</a> parameter.</li>
                    <li style="margin-top:3px;">An <a href="https://cloudinary.com/documentation/transformation_reference#l_layer" target="_blank">image overlay</a> for branding.</li>
                </ul>
            </li>
            <li style="margin-top:3px;">The video, depending on its <a href="https://cloudinary.com/documentation/moderate_assets" target="_blank">moderation</a> status:
                <ul>
                    <li style="margin-top:3px;"><b>Pending</b>: A message is displayed to inform you of the moderation status.</li>
                    <li style="margin-top:3px;"><b>Rejected</b>: A message is displayed informing you of the reason why the video was rejected.</li>
                    <li style="margin-top:3px;"><b>Accepted</b>: Rendered using Cloudinary's <a href="https://cloudinary.com/documentation/cloudinary_video_player" target="_blank">Video Player</a>.</li>
                </ul>
            </li>
        </ul>
    </div>
</div>


    <div class="products-page">
        
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <div class="product-card">
            <div class="product-image" style="position:relative;margin:10px auto;max-width:600px;border:1px solid grey;">
                <p style="width:100%;height:auto;object-fit:contain;"><b>Description:</b> <?php echo $product['image_caption']; ?></p>
            </div>
                        <!-- Display product image if available -->
            <div class="product-image" style="position:relative;margin:10px auto;max-width:300px;border:1px solid grey;">
                <p style="width:100%;height:auto;object-fit:contain;">SKU: <?php echo htmlspecialchars($sku); ?></p>
                <p style="width:100%;height:auto;object-fit:contain;">Price: $<?php echo htmlspecialchars($price); ?></p>
                <?php
                    // Get the category value for the product
                    $category_value = $metadata_result['metadata']['category'];

                    // Find the display text for the category using the mapping
                    $category_display = isset($category_labels[$category_value]) ? $category_labels[$category_value] : 'Unknown';
                ?>
                <p style="width:100%;height:auto;object-fit:contain;">Category: <?php echo htmlspecialchars($category_display); ?></p>
            </div>
                <?php if ($image_url): ?>
                <img class="product-image" src="<?php echo $image_url; ?>" alt="product Image">
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>

            <!-- Display product video if available -->
            <?php if ($product['video_public_id'] && $product['video_public_id']!='pending' && $product['video_moderation_status']!='rejected'): ?>
                <div style="position:relative;max-width:450px;margin:0 auto;">
                    <video style="width:100%;height:auto;object-fit:contain;" id="doc-player" controls muted class="cld-video-player cld-fluid"></video>
                </div>
                <script>
                    // Initialize the Cloudinary video player with a unique ID
                    const player = cloudinary.videoPlayer('doc-player', { cloudName: '<?php echo $_ENV['CLOUDINARY_CLOUD_NAME']; ?>' });
                    player.source('<?php echo $product['video_public_id']; ?>');
                </script>
            <?php elseif (isset($message)): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <p>
                <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a>
            </p>
        </div>
    </div>
    <script>
    // Wait until the page is fully loaded before starting the polling
    window.onload = function() {
        setInterval(() => {
            fetch('/../webhooks/upload_status.json')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        // Clear the upload_status file so we don't catch the completed webhook again until there's another event
                        fetch('clear_upload_status.php')
                            .then(() => {
                                // Refresh the page when the status is completed
                                location.reload(); 
                            });                    }
                });
        }, 3000); // Check every 3 seconds
    }
</script>
</body>
</html>
