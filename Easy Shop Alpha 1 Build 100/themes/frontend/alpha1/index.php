<!-- This is the template for the Alpha 1 theme -->
<div class="hero-section">
    <div class="container">
        <h1 class="site-title"><?php echo htmlspecialchars($store_name); ?></h1>
        <?php if (!empty($slogan)): ?>
            <p class="slogan"><?php echo htmlspecialchars($slogan); ?></p>
        <?php endif; ?>
    </div>
</div>

<main class="container">
    <h2>Our Products</h2>

    <?php if (empty($products)): ?>
        <p class="no-products">No products are available at the moment. Please check back later!</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="/uploads/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                    <a href="/product.php?id=<?php echo $product['id']; ?>" class="view-product-button">View Product</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>