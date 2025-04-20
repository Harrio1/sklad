<script setup>
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => []
    }
});

// Проверяем тип переданных props и адаптируем если нужно
const notificationsList = computed(() => {
    if (Array.isArray(props.notifications)) {
        return props.notifications;
    } else if (props.notifications && props.notifications.value && Array.isArray(props.notifications.value)) {
        return props.notifications.value;
    }
    return [];
});

const emit = defineEmits(['close']);

function closeNotification(id) {
    const notification = notificationsList.value.find(n => n.id === id);
    if (notification) {
        notification.show = false;
        
        setTimeout(() => {
            emit('close', id);
        }, 500);
    }
}
</script>

<template>
    <div class="notifications-container">
        <div v-for="notification in notificationsList" :key="notification.id"
             class="notification-item"
             :class="[notification.color, notification.show ? 'show' : '']"
             @click="closeNotification(notification.id)">
            <div v-if="notification.title" class="notification-title">{{ notification.title }}</div>
            <div class="notification-message">{{ notification.message }}</div>
        </div>
    </div>
</template>

<style scoped>
.notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 350px;
}

.notification-item {
    padding: 15px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    opacity: 0;
    transform: translateX(30px);
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-item.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-title {
    font-weight: 700;
    margin-bottom: 5px;
    font-size: 1.1em;
}

.notification-message {
    font-size: 0.95em;
    line-height: 1.4;
}

.notification-item.mgreen {
    background-color: #10B981;
}

.notification-item.mred {
    background-color: #DC2626;
}

.notification-item.mblue {
    background-color: #3B82F6;
}
</style> 