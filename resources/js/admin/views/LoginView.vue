<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

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
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 to-blue-800"
    >
        <div
            class="bg-white dark:bg-gray-800 w-full max-w-md rounded-3xl shadow-2xl p-10"
        >
            <!-- Title -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Bithaven Admin
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                    Sign in to continue
                </p>
            </div>

            <!-- Error -->
            <div
                v-if="error"
                class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3"
            >
                {{ error }}
            </div>

            <!-- Form -->
            <form @submit.prevent="handleLogin" class="space-y-5">
                <div>
                    <label
                        class="block text-sm text-gray-600 dark:text-gray-300 mb-1"
                    >
                        Username
                    </label>
                    <input
                        v-model="username"
                        type="text"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm text-gray-600 dark:text-gray-300 mb-1"
                    >
                        Password
                    </label>
                    <input
                        v-model="password"
                        type="password"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50"
                >
                    {{ loading ? "Signing in..." : "Sign In" }}
                </button>
            </form>
        </div>
    </div>
</template>
