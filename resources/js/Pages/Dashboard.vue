<template>
  <AppLayout title="Dashboard">
    <!-- Welcome Section -->
    <div class="mb-8" data-aos="fade-down">
      <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ $page.props.auth?.user?.full_name || 'User' }}!</h1>
      <p class="text-gray-600 mt-2">Here's what's happening with your projects today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard
        title="Total Projects"
        :value="stats.totalProjects"
        change="+12% from last month"
        :icon="FolderIcon"
        color="blue"
      />
      <StatCard
        title="Total Tasks"
        :value="stats.totalCards"
        change="+8% from last month"
        :icon="ClipboardDocumentListIcon"
        color="green"
      />
      <StatCard
        title="Completed Tasks"
        :value="stats.completedCards"
        change="+23% from last month"
        :icon="CheckCircleIcon"
        color="purple"
      />
      <StatCard
        title="Team Members"
        :value="stats.totalUsers"
        change="+2 new this month"
        :icon="UsersIcon"
        color="yellow"
      />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Recent Projects -->
      <ProjectList
        title="Recent Projects"
        :projects="recentProjects"
      />

      <!-- Activity Feed -->
      <ActivityFeed
        title="Recent Activity"
        :activities="activities"
      />
    </div>

    <!-- Project Progress Chart -->
    <div class="mt-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200" data-aos="fade-up" data-aos-delay="400">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Project Progress Overview</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div v-for="project in projectProgress" :key="project.project_name" class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex justify-between text-sm font-medium text-gray-900 mb-1">
                  <span>{{ project.project_name }}</span>
                  <span>{{ project.progress_percentage || 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                    :style="{ width: (project.progress_percentage || 0) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8" data-aos="fade-up" data-aos-delay="500">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link :href="route('projects.create')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <FolderPlusIcon class="w-8 h-8 text-blue-600 mb-2" />
            <span class="text-sm font-medium text-gray-900">New Project</span>
          </Link>
          <Link :href="route('cards.index')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <PlusIcon class="w-8 h-8 text-green-600 mb-2" />
            <span class="text-sm font-medium text-gray-900">Add Task</span>
          </Link>
          <Link :href="route('users.index')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <UserPlusIcon class="w-8 h-8 text-purple-600 mb-2" />
            <span class="text-sm font-medium text-gray-900">Invite Member</span>
          </Link>
          <Link :href="route('reports.index')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <ChartBarIcon class="w-8 h-8 text-yellow-600 mb-2" />
            <span class="text-sm font-medium text-gray-900">View Reports</span>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import ProjectList from '@/Components/ProjectList.vue'
import ActivityFeed from '@/Components/ActivityFeed.vue'
import { Link } from '@inertiajs/vue3'
import {
  FolderIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  UsersIcon,
  FolderPlusIcon,
  PlusIcon,
  UserPlusIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalProjects: 0,
      totalCards: 0,
      completedCards: 0,
      totalUsers: 0
    })
  },
  recentProjects: {
    type: Array,
    default: () => []
  },
  projectProgress: {
    type: Array,
    default: () => []
  },
  activities: {
    type: Array,
    default: () => []
  }
})
</script>
