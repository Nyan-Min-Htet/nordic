<template>
    <div class="dashboard-container">
        <!-- Navbar -->
        <nav class="dashboard-nav">
            <div class="nav-content">
                <div class="nav-brand">
                    <h1>Nordic Inventory</h1>
                </div>
                <button @click="logout" class="logout-btn">Logout</button>
            </div>
        </nav>

        <div class="dashboard-content">
            <!-- Notifications -->
            <div
                v-if="notification.message"
                :class="[
                    'notification',
                    notification.type === 'success' ? 'success' : 'error',
                ]"
            >
                <span>{{ notification.message }}</span>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <div class="page-header">
                    <h2>Product Inventory</h2>
                    <p>Browse and add products to your cart</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Available Products</h3>
                        <div v-if="loadingProducts" class="loading-spinner">
                            Loading products...
                        </div>
                    </div>

                    <div v-if="!loadingProducts" class="products-grid">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="product-card"
                        >
                            <div class="product-info">
                                <h4 class="product-name">{{ product.name }}</h4>
                                <p class="product-price">
                                    ${{ Number(product.price).toFixed(2) }}
                                </p>
                                <p
                                    class="product-stock"
                                    :class="{ 'low-stock': product.stock < 10 }"
                                >
                                    {{ product.stock }} in stock
                                </p>
                            </div>
                            <div class="product-actions">
                                <div class="quantity-control">
                                    <button
                                        @click="
                                            product.qty > 1
                                                ? product.qty--
                                                : null
                                        "
                                        class="qty-btn"
                                        :disabled="product.qty <= 1"
                                    >
                                        -
                                    </button>
                                    <input
                                        v-model.number="product.qty"
                                        type="number"
                                        min="1"
                                        :max="product.stock"
                                        class="qty-input"
                                    />
                                    <button
                                        @click="
                                            product.qty < product.stock
                                                ? product.qty++
                                                : null
                                        "
                                        class="qty-btn"
                                        :disabled="product.qty >= product.stock"
                                    >
                                        +
                                    </button>
                                </div>
                                <button
                                    @click="addToCart(product)"
                                    :disabled="product.stock === 0"
                                    class="add-to-cart-btn"
                                >
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="cart-section">
                <div class="page-header">
                    <h2>Shopping Cart</h2>
                    <p>Review your items before placing order</p>
                </div>

                <div v-if="cart.length > 0" class="card">
                    <div class="card-header">
                        <h3>Your Order</h3>
                    </div>

                    <div class="cart-items">
                        <div
                            v-for="(item, index) in cart"
                            :key="item.product_id"
                            class="cart-item"
                        >
                            <div class="item-info">
                                <span class="item-name">{{ item.name }}</span>
                                <span class="item-quantity"
                                    >Quantity: {{ item.quantity }}</span
                                >
                            </div>
                            <div class="item-actions">
                                <span class="item-price"
                                    >${{
                                        (item.price * item.quantity).toFixed(2)
                                    }}</span
                                >
                                <button
                                    @click="removeFromCart(index)"
                                    class="remove-item-btn"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="cart-footer">
                        <div class="cart-total">
                            <span>Total:</span>
                            <span class="total-amount"
                                >${{ cartTotal.toFixed(2) }}</span
                            >
                        </div>
                        <button
                            @click="placeOrder"
                            :disabled="placingOrder"
                            class="place-order-btn"
                        >
                            {{
                                placingOrder
                                    ? "Placing Order..."
                                    : "Place Order"
                            }}
                        </button>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <h3>Your cart is empty</h3>
                    <p>Add products from the inventory to get started</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../axios";

const router = useRouter();
const products = ref([]);
const cart = ref([]);
const loadingProducts = ref(true);
const placingOrder = ref(false);
const notification = reactive({ message: "", type: "" });

const cartTotal = computed(() => {
    return cart.value.reduce(
        (sum, item) => sum + item.price * item.quantity,
        0,
    );
});

const showNotification = (message, type = "success") => {
    notification.message = message;
    notification.type = type;
    setTimeout(() => {
        notification.message = "";
    }, 3000);
};

const fetchProducts = async () => {
    loadingProducts.value = true;
    try {
        const { data } = await api.get("/products");
        products.value = data.map((p) => ({ ...p, qty: 1 }));
    } catch {
        showNotification("Failed to load products.", "error");
    } finally {
        loadingProducts.value = false;
    }
};

const addToCart = (product) => {
    const qty = product.qty || 1;
    const existing = cart.value.find((i) => i.product_id === product.id);
    if (existing) {
        existing.quantity += qty;
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            price: Number(product.price),
            quantity: qty,
        });
    }
    showNotification(`${product.name} added to cart!`);
};

const removeFromCart = (index) => {
    const removedItem = cart.value[index];
    cart.value.splice(index, 1);
    showNotification(`${removedItem.name} removed from cart.`);
};

const placeOrder = async () => {
    placingOrder.value = true;
    try {
        const items = cart.value.map(({ product_id, quantity }) => ({
            product_id,
            quantity,
        }));
        await api.post("/orders", { items });
        cart.value = [];
        showNotification("Order placed successfully!");
        fetchProducts();
    } catch (err) {
        showNotification(
            err.response?.data?.message || "Failed to place order.",
            "error",
        );
    } finally {
        placingOrder.value = false;
    }
};

const logout = async () => {
    try {
        await api.post("/logout");
    } catch {
        // ignore
    }
    localStorage.removeItem("token");
    router.push("/");
};

onMounted(() => {
    fetchProducts();
});
</script>

<style scoped>
.dashboard-container {
    min-height: 100vh;
    background-color: #f8fafc;
}

.dashboard-nav {
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand h1 {
    font-size: 1.5rem;
    font-weight: 700;
}

.logout-btn {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.25);
}

.dashboard-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
}

.notification {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.notification.success {
    background-color: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.notification.error {
    background-color: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #6b7280;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.loading-spinner {
    color: #6b7280;
}

.products-grid {
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.product-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1.25rem;
    transition: all 0.3s;
}

.product-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
}

.product-info {
    margin-bottom: 1rem;
}

.product-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 0.5rem;
}

.product-stock {
    font-size: 0.875rem;
    color: #6b7280;
}

.low-stock {
    color: #dc2626;
    font-weight: 500;
}

.product-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: bold;
}

.qty-btn:not(:disabled):hover {
    border-color: #4f46e5;
    color: #4f46e5;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-input {
    width: 60px;
    text-align: center;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-weight: 600;
}

.add-to-cart-btn {
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.add-to-cart-btn:not(:disabled):hover {
    background: #4338ca;
    transform: translateY(-1px);
}

.add-to-cart-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.cart-items {
    padding: 1.5rem;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.item-name {
    font-weight: 600;
    color: #1f2937;
}

.item-quantity {
    font-size: 0.875rem;
    color: #6b7280;
}

.item-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-price {
    font-weight: 600;
    color: #4f46e5;
}

.remove-item-btn {
    color: #ef4444;
    background: none;
    border: 1px solid #ef4444;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.remove-item-btn:hover {
    background: #fef2f2;
}

.cart-footer {
    padding: 1.5rem;
    background: #f9fafb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-total {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cart-total span:first-child {
    font-size: 0.875rem;
    color: #6b7280;
}

.total-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.place-order-btn {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.place-order-btn:not(:disabled):hover {
    background: #059669;
    transform: translateY(-1px);
}

.place-order-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}

.empty-state p {
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
    }

    .cart-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .cart-total {
        align-items: center;
    }

    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .item-actions {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
