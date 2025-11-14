<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200" data-aos="fade-up" data-aos-delay="200">
    <div class="p-6 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
    </div>
    <div class="p-6">
      <div class="space-y-4">
        <div v-for="project in projects" :key="project.project_id" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <div class="flex-1">
            <h4 class="font-medium text-gray-900">{{ project.project_name }}</h4>
            <p class="text-sm text-gray-600">{{ project.description || 'Tidak ada deskripsi' }}</p>
            <div class="mt-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span class="font-medium">{{ getProgress(project) }}%</span>
              </div>
              <div class="mt-1 bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: getProgress(project) + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div class="ml-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(project.status)">
              {{ project.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProjectList',
  props: {
    title: {
      type: String,
      default: 'Recent Projects'
    },
    projects: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    getProgress(project) {
      // Implement progress calculation logic
      return Math.floor(Math.random() * 100); // Placeholder
    },
    getStatusClass(status) {
      const classes = {
        'active': 'bg-green-100 text-green-800',
        'planning': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-blue-100 text-blue-800',
        'on-hold': 'bg-gray-100 text-gray-800'
      };
      return classes[status] || classes['active'];
    }
  }
}
</script>
