<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import BackgroundImage from "../assets/loginbackground.png";

const router = useRouter();
const auth = useAuthStore();

const username = ref("");
const password = ref("");
const loading = ref(false);
const error = ref(null);

async function handleLogin() {
    error.value = null;
    loading.value = true;

    try {
        await auth.login({
            username: username.value,
            password: password.value,
        });

        router.push("/admin/home");
    } catch (err) {
        if (err.response?.status === 401) {
            error.value = "Invalid username or password.";
        } else if (err.response?.status === 403) {
            error.value = "Account is inactive.";
        } else {
            error.value = "Something went wrong.";
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        class="relative min-h-screen flex items-center justify-center overflow-hidden"
    >
        <!-- BACKGROUND IMAGE -->
        <div
            class="absolute inset-0 bg-cover bg-center"
            :style="{ backgroundImage: `url(${BackgroundImage})` }"
        ></div>

        <!-- OVERLAY -->
        <div class="absolute inset-0"></div>

        <!-- CONTENT -->
        <div
            class="relative z-10 w-full max-w-md rounded-[28px] px-10 py-10 bg-white/5 backdrop-blur-xs border border-white/15 shadow-[0_20px_60px_rgba(0,0,0,0.45)]"
        >
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-[28px] font-semibold text-white tracking-tight">
                    Bithaven Admin
                </h1>
                <p class="text-white/60 text-sm mt-2">
                    Secure access to the system
                </p>
            </div>

            <!-- Error -->
            <div
                v-if="error"
                class="mb-6 text-sm text-red-200 bg-red-500/20 border border-red-400/30 rounded-xl px-4 py-3"
            >
                {{ error }}
            </div>

            <!-- Form -->
            <form @submit.prevent="handleLogin" class="space-y-6">
                <!-- Username -->
                <div>
                    <label class="block text-sm text-white/60 mb-2">
                        Username
                    </label>

                    <div class="relative">
                        <input
                            v-model="username"
                            type="text"
                            required
                            placeholder="Enter your username"
                            class="w-full px-4 py-3 rounded-xl bg-white text-gray border border-white/15 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30 transition"
                        />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm text-white/60 mb-2">
                        Password
                    </label>

                    <div class="relative">
                        <input
                            v-model="password"
                            type="password"
                            required
                            placeholder="Enter your password"
                            class="w-full px-4 py-3 rounded-xl bg-white text-gray border border-white/15 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30 transition"
                        />
                    </div>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full mt-4 py-3 rounded-xl bg-white text-gray-900 font-semibold hover:bg-white/90 transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? "Signing in..." : "Sign In" }}
                </button>
            </form>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-white/40">
                Authorized personnel only
            </p>
        </div>
    </div>
</template>
