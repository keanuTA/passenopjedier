<!-- resources/js/Components/Toast.vue -->
<template>
    <div v-if="visible" class="toast" :class="type">
      <span>{{ message }}</span>
      <button @click="close">x</button>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      message: {
        type: String,
        required: true
      },
      type: {
        type: String,
        default: 'info'
      }
    },
    data() {
      return {
        visible: true
      };
    },
    methods: {
      close() {
        this.visible = false;
      }
    }
  };
  </script>
  
  <style scoped>
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #333;
      color: #fff;
      padding: 10px;
      border-radius: 5px;
      z-index: 1000;
    }
    .toast.info {
      background: #3498db;
    }
    .toast.success {
      background: #2ecc71;
    }
    .toast.error {
      background: #e74c3c;
    }
  </style>