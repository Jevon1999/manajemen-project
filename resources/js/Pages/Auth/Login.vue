<template>
  <div class="min-h-screen flex">
    <!-- Left side - Form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white">
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <!-- Logo and Header -->
        <div data-aos="fade-down" data-aos-delay="100">
          <div class="flex items-center justify-center lg:justify-start mb-8">
            <div class="h-12 w-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <span class="ml-3 text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              ProjectHub
            </span>
          </div>
          
          <div class="text-center lg:text-left">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h2>
            <p class="text-gray-600">Sign in to your account to continue</p>
          </div>
        </div>

        <!-- Form -->
        <div class="mt-8" data-aos="fade-up" data-aos-delay="200">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  required
                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': form.errors.email }"
                  placeholder="Enter your email"
                />
              </div>
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <LockClosedIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': form.errors.password }"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" />
                  <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" />
                </button>
              </div>
              <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                {{ form.errors.password }}
              </div>
            </div>

            <!-- Remember Me and Forgot Password -->
            <div class="flex items-center justify-between" data-aos="fade-up" data-aos-delay="300">
              <div class="flex items-center">
                <input
                  id="remember"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors"
                />
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                  Remember me
                </label>
              </div>

              <div class="text-sm">
                <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                  Forgot your password?
                </a>
              </div>
            </div>

            <!-- Submit Button -->
            <div data-aos="fade-up" data-aos-delay="400">
              <button
                type="submit"
                :disabled="form.processing"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
              >
                <span v-if="!form.processing" class="flex items-center">
                  <ArrowRightOnRectangleIcon class="w-5 h-5 mr-2" />
                  Sign in
                </span>
                <span v-else class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Signing in...
                </span>
              </button>
            </div>
          </form>

          <!-- Register Link -->
          <div class="mt-6 text-center" data-aos="fade-up" data-aos-delay="500">
            <p class="text-sm text-gray-600">
              Don't have an account? 
              <Link :href="route('register')" class="font-semibold text-blue-600 hover:text-blue-500 transition-colors">
                Create one here
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right side - Image/Illustration -->
    <div class="hidden lg:block relative w-0 flex-1">
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
          <!-- Animated Background Elements -->
          <div class="absolute top-20 left-10 w-32 h-32 bg-white/5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1000"></div>
          <div class="absolute bottom-32 right-20 w-24 h-24 bg-white/5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1200"></div>
          <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/5 rounded-full animate-pulse" data-aos="fade-in" data-aos-delay="1400"></div>

          <!-- Project Cards -->
          <div 
            class="absolute top-20 right-10 w-64 h-40 bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-6"
            data-aos="fade-left" 
            data-aos-delay="600"
          >
            <div class="flex items-center mb-4">
              <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg"></div>
              <div class="ml-3">
                <div class="w-20 h-3 bg-white/30 rounded"></div>
                <div class="w-16 h-2 bg-white/20 rounded mt-2"></div>
              </div>
            </div>
            <div class="space-y-2">
              <div class="w-full h-2 bg-white/20 rounded"></div>
              <div class="w-3/4 h-2 bg-white/20 rounded"></div>
              <div class="w-1/2 h-2 bg-white/20 rounded"></div>
            </div>
            <div class="mt-4 flex justify-between items-center">
              <div class="flex -space-x-2">
                <div class="w-6 h-6 bg-white/30 rounded-full border border-white/20"></div>
                <div class="w-6 h-6 bg-white/30 rounded-full border border-white/20"></div>
                <div class="w-6 h-6 bg-white/30 rounded-full border border-white/20"></div>
              </div>
              <div class="w-12 h-6 bg-green-400/30 rounded-full"></div>
            </div>
          </div>

          <div 
            class="absolute bottom-40 left-10 w-56 h-36 bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-5"
            data-aos="fade-right" 
            data-aos-delay="800"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl"></div>
              <div class="text-xs text-white/70">85% Complete</div>
            </div>
            <div class="space-y-3">
              <div class="w-full h-2 bg-white/20 rounded-full">
                <div class="w-4/5 h-2 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full"></div>
              </div>
              <div class="grid grid-cols-4 gap-2">
                <div class="h-6 bg-white/20 rounded"></div>
                <div class="h-6 bg-white/20 rounded"></div>
                <div class="h-6 bg-purple-400/50 rounded"></div>
                <div class="h-6 bg-white/10 rounded"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="relative h-full flex flex-col justify-center items-center text-center p-12">
          <div data-aos="zoom-in" data-aos-delay="400">
            <h1 class="text-4xl font-bold text-white mb-6">
              Manage Projects
              <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                Like Never Before
              </span>
            </h1>
            <p class="text-xl text-blue-100 mb-8 max-w-md">
              Experience the power of seamless project management with our intuitive and modern platform.
            </p>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-8 text-center max-w-md" data-aos="fade-up" data-aos-delay="600">
              <div>
                <div class="text-2xl font-bold text-white">1000+</div>
                <div class="text-sm text-blue-200">Projects</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-white">50+</div>
                <div class="text-sm text-blue-200">Teams</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-white">99%</div>
                <div class="text-sm text-blue-200">Uptime</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { 
  EnvelopeIcon, 
  LockClosedIcon, 
  EyeIcon, 
  EyeSlashIcon,
  ArrowRightOnRectangleIcon 
} from '@heroicons/vue/24/outline'

export default {
  components: {
    Link,
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightOnRectangleIcon
  },
  
  setup() {
    const showPassword = ref(false)
    
    const form = useForm({
      email: '',
      password: '',
      remember: false,
    })
    
    const submit = () => {
      form.post(route('login.post'), {
        onFinish: () => {
          form.reset('password')
        },
      })
    }
    
    return {
      form,
      submit,
      showPassword
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
