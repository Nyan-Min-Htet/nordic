<template>
    <div>
        <!-- Navbar -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">Nordic Inventory</h1>
                <button
                    @click="logout"
                    class="text-red-600 hover:text-red-800 font-medium"
                >
                    Logout
                </button>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Notifications -->
            <div v-if="notification.message" :class="notification.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-4 py-3 rounded mb-6">
                {{ notification.message }}
            </div>

            <!-- Products -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Products</h2>

            <div v-if="loadingProducts" class="text-gray-500">Loading products...</div>

            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Add</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="product in products" :key="product.id">
                            <td class="px-6 py-4 text-gray-800">{{ product.name }}</td>
                            <td class="px-6 py-4 text-gray-600">${{ Number(product.price).toFixed(2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ product.stock }}</td>
                            <td class="px-6 py-4">
                                <input
                                    v-model.number="product.qty"
                                    type="number"
                                    min="1"
                                    :max="product.stock"
                                    class="w-16 px-2 py-1 border border-gray-300 rounded text-center"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    @click="addToCart(product)"
                                    :disabled="product.stock === 0"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Add
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cart -->
            <div v-if="cart.length > 0" class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Your Order</h2>

                <div v-for="(item, index) in cart" :key="item.product_id" class="flex justify-between items-center py-2 border-b">
                    <span class="text-gray-800">{{ item.name }} x {{ item.quantity }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-600">${{ (item.price * item.quantity).toFixed(2) }}</span>
                        <button @click="cart.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-4 pt-4 border-t">
                    <span class="font-bold text-gray-800">Total: ${{ cartTotal.toFixed(2) }}</span>
                    <button
                        @click="placeOrder"
                        :disabled="placingOrder"
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ placingOrder ? 'Placing Order...' : 'Place Order' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../axios';

const router = useRouter();
const products = ref([]);
const cart = ref([]);
const loadingProducts = ref(true);
const placingOrder = ref(false);
const notification = reactive({ message: '', type: '' });

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

const showNotification = (message, type = 'success') => {
    notification.message = message;
    notification.type = type;
    setTimeout(() => { notification.message = ''; }, 3000);
};

const fetchProducts = async () => {
    loadingProducts.value = true;
    try {
        const { data } = await api.get('/products');
        products.value = data.map(p => ({ ...p, qty: 1 }));
    } catch {
        showNotification('Failed to load products.', 'error');
    } finally {
        loadingProducts.value = false;
    }
};

const addToCart = (product) => {
    const qty = product.qty || 1;
    const existing = cart.value.find(i => i.product_id === product.id);
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
};

const placeOrder = async () => {
    placingOrder.value = true;
    try {
        const items = cart.value.map(({ product_id, quantity }) => ({ product_id, quantity }));
        await api.post('/orders', { items });
        cart.value = [];
        showNotification('Order placed successfully!');
        fetchProducts();
    } catch (err) {
        showNotification(err.response?.data?.message || 'Failed to place order.', 'error');
    } finally {
        placingOrder.value = false;
    }
};

const logout = async () => {
    try {
        await api.post('/logout');
    } catch {
        // ignore
    }
    localStorage.removeItem('token');
    router.push('/');
};

onMounted(fetchProducts);
</script>
