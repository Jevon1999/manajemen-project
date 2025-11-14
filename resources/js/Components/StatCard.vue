<template>
  <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200" data-aos="fade-up">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
        <p v-if="change" class="text-sm mt-1" :class="changeClass">
          {{ change }}
        </p>
      </div>
      <div class="p-3 rounded-full" :class="iconBgClass">
        <component :is="icon" class="w-6 h-6" :class="iconClass" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatCard',
  props: {
    title: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      required: true
    },
    change: {
      type: String,
      default: null
    },
    icon: {
      type: Object,
      required: true
    },
    color: {
      type: String,
      default: 'blue'
    }
  },
  computed: {
    changeClass() {
      if (!this.change) return '';
      return this.change.includes('+') ? 'text-green-600' : 'text-red-600';
    },
    iconBgClass() {
      const colors = {
        blue: 'bg-blue-100',
        green: 'bg-green-100',
        yellow: 'bg-yellow-100',
        purple: 'bg-purple-100'
      };
      return colors[this.color] || colors.blue;
    },
    iconClass() {
      const colors = {
        blue: 'text-blue-600',
        green: 'text-green-600',
        yellow: 'text-yellow-600',
        purple: 'text-purple-600'
      };
      return colors[this.color] || colors.blue;
    }
  }
}
</script>
