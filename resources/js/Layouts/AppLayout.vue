<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">PM</span>
          </div>
          <span class="text-xl font-bold text-gray-900">ProjectHub</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-500">
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- User Info -->
      <div class="p-6 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
            <span class="text-white font-medium text-sm">{{ userInitials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ $page.props.auth?.user?.full_name || 'User' }}</p>
            <p class="text-xs text-gray-500 truncate">{{ $page.props.auth?.user?.email || 'user@example.com' }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="mt-6 px-3">
        <div class="space-y-1">
          <!-- Dashboard -->
          <Link :href="route('dashboard')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <HomeIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Dashboard
          </Link>

          <!-- Projects -->
          <Link :href="route('projects.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('projects.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <FolderIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('projects.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Projects
          </Link>

          <!-- Boards -->
          <Link :href="route('boards.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('boards.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <Squares2X2Icon class="w-5 h-5 mr-3" :class="isCurrentRoute('boards.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Boards
          </Link>

          <!-- Tasks -->
          <Link :href="route('cards.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('cards.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <ClipboardDocumentListIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('cards.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Tasks
          </Link>

          <!-- Time Tracking -->
          <Link :href="route('timelogs.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('timelogs.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <ClockIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('timelogs.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Time Tracking
          </Link>

          <!-- Calendar -->
          <Link :href="route('calendar')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('calendar') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
            <CalendarDaysIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('calendar') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
            Calendar
          </Link>
        </div>

        <!-- Admin Section -->
        <div v-if="isAdmin" class="mt-8">
          <div class="px-3 mb-2">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Administration</h3>
          </div>
          <div class="space-y-1">
            <Link :href="route('users.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('users.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
              <UsersIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('users.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
              Users
            </Link>
            <Link :href="route('reports.index')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('reports.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
              <ChartBarIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('reports.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
              Reports
            </Link>
            <Link :href="route('settings')" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" :class="isCurrentRoute('settings') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
              <CogIcon class="w-5 h-5 mr-3" :class="isCurrentRoute('settings') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
              Settings
            </Link>
          </div>
        </div>

        <!-- Logout -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <form @submit.prevent="logout">
            <button type="submit" class="group flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
              <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500" />
              Logout
            </button>
          </form>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64">
      <!-- Top Bar -->
      <div class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between h-16 px-6">
          <div class="flex items-center">
            <button @click="sidebarOpen = true" class="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-500">
              <Bars3Icon class="w-6 h-6" />
            </button>
            <!-- Breadcrumbs -->
            <nav class="flex ml-4" aria-label="Breadcrumb">
              <ol class="flex items-center space-x-4">
                <li>
                  <div class="flex items-center text-sm text-gray-500">
                    {{ title || 'Dashboard' }}
                  </div>
                </li>
              </ol>
            </nav>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="p-1 rounded-full text-gray-400 hover:text-gray-500">
              <BellIcon class="w-6 h-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  HomeIcon,
  FolderIcon,
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  ClockIcon,
  CalendarDaysIcon,
  UsersIcon,
  ChartBarIcon,
  CogIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

defineProps({
  title: String
})

const sidebarOpen = ref(false)

const userInitials = computed(() => {
  const name = window.page?.props?.auth?.user?.full_name || 'User'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
})

const isAdmin = computed(() => {
  return window.page?.props?.auth?.user?.role === 'admin'
})

const isCurrentRoute = (routeName) => {
  if (routeName.includes('*')) {
    const baseRoute = routeName.replace('.*', '')
    return route().current()?.startsWith(baseRoute)
  }
  return route().current(routeName)
}

const logout = () => {
  router.post(route('logout'))
}
</script>
