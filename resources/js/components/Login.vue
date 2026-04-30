<template>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-cubes"></i>
                </div>
                <h1>Nordic Inventory</h1>
                <p class="subtitle">Log in to your account</p>
            </div>

            <div v-if="error" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ error }}</span>
            </div>

            <form @submit.prevent="login">
                <div class="form-group">
                    <label class="input-label">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-input"
                            placeholder="test@example.com"
                            required
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            v-model="form.password"
                            type="password"
                            class="form-input"
                            placeholder="password"
                            required
                        />
                    </div>
                </div>

                <button type="submit" :disabled="loading" class="login-button">
                    <span v-if="loading">
                        <i class="fas fa-spinner fa-spin"></i> Logging in...
                    </span>
                    <span v-else>
                        <i class="fas fa-sign-in-alt"></i> Login
                    </span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import api from "../axios";

const router = useRouter();
const loading = ref(false);
const error = ref("");

const form = reactive({
    email: "",
    password: "",
});

const login = async () => {
    loading.value = true;
    error.value = "";

    try {
        const { data } = await api.post("/login", form);
        localStorage.setItem("token", data.token);
        router.push("/dashboard");
    } catch (err) {
        error.value =
            err.response?.data?.message || "Login failed. Please try again.";
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.login-card {
    width: 100%;
    max-width: 440px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    padding: 30px;
}

.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.logo {
    width: 70px;
    height: 70px;
    margin: 0 auto 15px;
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
}

h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}

.subtitle {
    color: #6b7280;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.input-label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.input-with-icon {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.form-input {
    width: 100%;
    padding: 14px 15px 14px 45px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    background: white;
}

.login-button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 10px;
}

.login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
}

.login-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.error-message {
    background: #fef2f2;
    color: #dc2626;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.error-message i {
    font-size: 18px;
}

.demo-credentials {
    background: #ede9fe;
    padding: 15px;
    border-radius: 10px;
    margin-top: 25px;
    font-size: 14px;
    color: #4f46e5;
}

.demo-credentials h3 {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
}

.credential {
    display: flex;
    margin-bottom: 8px;
}

.credential-label {
    font-weight: 500;
    min-width: 70px;
}

@media (max-width: 480px) {
    .login-card {
        padding: 25px 20px;
    }

    .logo {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }

    h1 {
        font-size: 24px;
    }
}
</style>
