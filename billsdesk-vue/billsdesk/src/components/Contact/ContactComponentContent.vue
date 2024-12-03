<template>
  <div class="contact-card" @click="editContact">
    <div class="avatar">
      <span>{{ contact.name.charAt(0).toUpperCase() }}</span>
    </div>
    <div class="contact-details">
      <h3>{{ contact.name }}</h3>
      <p class="date">{{ formattedDate(contact.updated_at) }}</p>
      <div class="info">
        <p><i class="pi pi-envelope"></i> {{ contact.email }}</p>
        <p><i class="pi pi-phone"></i> {{ contact.phone }}</p>
        <p><i class="pi pi-home"></i> {{ contact.address }}</p>
      </div>
    </div>
    <i class="pi pi-pencil"></i>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  contact: {
    type: Object,
    required: true,
  },
});

const formattedDate = (date_param) => {
  const date = new Date(date_param);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}; 


const emit = defineEmits(['editContact']);

const editContact = () => {
  emit('editContact', props.contact);
};


</script>

<style scoped lang="scss">
.contact-card {
    display: flex;
    align-items: center;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
    position: relative;
    cursor: pointer;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: bold;
  color: #555;
  margin-right: 16px;
}

.contact-details {
  flex: 1;
}

.contact-details h3 {
  margin: 0;
  font-size: 18px;
}

.contact-details .date {
  margin: 4px 0 12px;
  color: #888;
  font-size: 14px;
}

.info p {
  margin: 4px 0;
  display: flex;
  align-items: center;
}

i{
    margin-right: 8px;
}

.icon {
  margin-right: 8px;
}

.pi-pencil{
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>
