<template>
    <div>
        <h2>Settings</h2>
        <Tabs value="2">
            <TabList>
                <Tab value="0">Profile</Tab>
                <Tab value="1">Users</Tab>
                <Tab value="2">Contacts</Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <ProfileComponent />
                </TabPanel>
                <TabPanel value="1">
                    <UsersComponent />
                </TabPanel>
                <TabPanel value="2">
                    <ContactsComponent />
                </TabPanel>
            </TabPanels>
        </Tabs>

    </div>
</template>

<script setup>

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import UsersComponent from '@/components/Settings/UsersComponent.vue';
import ProfileComponent from '@/components/Settings/ProfileComponent.vue';
import ContactsComponent from '@/components/Settings/ContactsComponent.vue';
</script>

<style scoped lang='scss'>


    .p-tab-active{
        color: #000000;
        font-weight: bold;
        border-color: #000000;
    }

    .p-tablist-active-bar{
        display: none;
    }


 
</style>