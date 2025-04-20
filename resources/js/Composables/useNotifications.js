import { ref } from 'vue';

export default function useNotifications() {
    const notifications = ref([]);

    function showNotification(message, color = 'mgreen', title = 'Уведомление', timeout = 4000) {
        const id = Date.now();
        notifications.value.push({ id, message, color, title, show: false });
        
        setTimeout(() => {
            const notification = notifications.value.find(n => n.id === id);
            if (notification) {
                notification.show = true;
            }
        }, 10);

        setTimeout(() => {
            closeNotification(id);
        }, timeout);

        return id;
    }

    function closeNotification(id) {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value[index].show = false;
            
            setTimeout(() => {
                notifications.value = notifications.value.filter(n => n.id !== id);
            }, 500);
        }
    }

    return {
        notifications,
        showNotification,
        closeNotification
    };
} 