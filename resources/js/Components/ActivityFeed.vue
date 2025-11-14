<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200" data-aos="fade-up" data-aos-delay="300">
    <div class="p-6 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
    </div>
    <div class="p-6">
      <div class="flow-root">
        <ul role="list" class="-mb-8">
          <li v-for="(activity, activityIdx) in activities" :key="activity.id">
            <div class="relative pb-8">
              <span v-if="activityIdx !== activities.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
              <div class="relative flex space-x-3">
                <div>
                  <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getActivityColor(activity.type)">
                    <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-white" />
                  </span>
                </div>
                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                  <div>
                    <p class="text-sm text-gray-500">
                      {{ activity.description }}
                    </p>
                  </div>
                  <div class="whitespace-nowrap text-right text-sm text-gray-500">
                    <time :datetime="activity.created_at">{{ formatTime(activity.created_at) }}</time>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { 
  FolderPlusIcon, 
  CheckCircleIcon, 
  ClockIcon, 
  UserPlusIcon,
  ChatBubbleLeftIcon 
} from '@heroicons/vue/24/solid'

export default {
  name: 'ActivityFeed',
  components: {
    FolderPlusIcon,
    CheckCircleIcon,
    ClockIcon,
    UserPlusIcon,
    ChatBubbleLeftIcon
  },
  props: {
    title: {
      type: String,
      default: 'Recent Activity'
    },
    activities: {
      type: Array,
      default: () => [
        {
          id: 1,
          type: 'project_created',
          description: 'Project "Website Redesign" dibuat',
          created_at: '2025-09-11T10:00:00Z'
        },
        {
          id: 2,
          type: 'task_completed',
          description: 'Task "Design Homepage" selesai',
          created_at: '2025-09-11T09:30:00Z'
        },
        {
          id: 3,
          type: 'member_added',
          description: 'John Doe ditambahkan ke project',
          created_at: '2025-09-11T09:00:00Z'
        }
      ]
    }
  },
  methods: {
    getActivityColor(type) {
      const colors = {
        'project_created': 'bg-blue-500',
        'task_completed': 'bg-green-500',
        'task_created': 'bg-yellow-500',
        'member_added': 'bg-purple-500',
        'comment_added': 'bg-indigo-500'
      };
      return colors[type] || 'bg-gray-500';
    },
    getActivityIcon(type) {
      const icons = {
        'project_created': 'FolderPlusIcon',
        'task_completed': 'CheckCircleIcon',
        'task_created': 'ClockIcon',
        'member_added': 'UserPlusIcon',
        'comment_added': 'ChatBubbleLeftIcon'
      };
      return icons[type] || 'ClockIcon';
    },
    formatTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffInMinutes = Math.floor((now - date) / (1000 * 60));
      
      if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`;
      } else if (diffInMinutes < 1440) {
        return `${Math.floor(diffInMinutes / 60)}h ago`;
      } else {
        return `${Math.floor(diffInMinutes / 1440)}d ago`;
      }
    }
  }
}
</script>
