<?php include('includes/header.php'); ?>

<section class="order-section py-5">
    <div class="container">
        <h1 class="mb-4">Take Away Order</h1>
        
        <!-- Step Wizard -->
        <div class="mb-4">
            <div class="d-flex justify-content-between flex-wrap gap-2 text-center">
                <div class="step flex-fill p-3 border rounded bg-primary text-white" data-step="1">1. Select Items</div>
                <div class="step flex-fill p-3 border rounded bg-light" data-step="2">2. Delivery Info</div>
                <div class="step flex-fill p-3 border rounded bg-light" data-step="3">3. Payment</div>
                <div class="step flex-fill p-3 border rounded bg-light" data-step="4">4. Confirmation</div>
            </div>
        </div>

        <!-- Step 1: Menu Selection -->
        <div class="step-content" id="step-1">
            <h2 class="mb-3">Our Menu</h2>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button class="btn btn-outline-primary category-btn active" data-category="all">All</button>
                <button class="btn btn-outline-primary category-btn" data-category="sushi">Sushi</button>
                <button class="btn btn-outline-primary category-btn" data-category="ramen">Ramen</button>
                <button class="btn btn-outline-primary category-btn" data-category="appetizer">Appetizers</button>
            </div>

            <div class="row menu-items-grid g-4">
                <!-- Menu items will be loaded here dynamically -->
            </div>

            <!-- Order Summary -->
            <div class="mt-5">
                <h3 class="mb-3">Your Order</h3>
                <div class="order-items border rounded p-3 mb-3 bg-light">
                    <!-- Ordered items will appear here -->
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total:</span>
                    <span class="order-total h5 text-primary">Rp 0</span>
                </div>
                <button class="btn btn-success mt-3">Proceed to Delivery Info</button>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>
