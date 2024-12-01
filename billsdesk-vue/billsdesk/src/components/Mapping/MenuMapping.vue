<template>
  <div>
    <ul>
      <li :class="{
        active: $route.path === '/mapping-settings/selecting-files'
      }"><router-link to="/mapping-settings/selecting-files">Selecting file<i class="pi pi-chevron-right"></i></router-link></li>
      <li :class="{
        active: $route.path === '/mapping-settings/invoice-template'
      }"><router-link to="/mapping-settings/invoice-template">Invoice template<i class="pi pi-chevron-right"></i></router-link></li>
      <li
      :class="{
        active: $route.path === '/mapping-settings/mapping' 
        || $route.path.includes('/invoice-template/new') 
        || $route.path.includes('/invoice-template/edit')
        || $route.path.includes('/invoice-template/existing')
      }"
      ><router-link to="/mapping-settings/mapping">Mapping<i class="pi pi-chevron-right"></i></router-link></li>
      <li :class="{
        active: $route.path === '/mapping-settings/correction-rules'
      }"><router-link to="/mapping-settings/correction-rules">Correction Rules<i class="pi pi-chevron-right"></i></router-link></li>
    </ul>
  </div>
</template>

<script setup></script>

<style scoped lang="scss">
    ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    width: 100%;
    color: #000;
    border: 1px solid #e7e7e7;
    border-radius: 5px;

    li {
        border-bottom: 1px solid #e7e7e7;
        padding: 2px 20px;

        a {
          display: flex;
          align-items: center;
          justify-content: space-between;
          color: #000;
          padding: 8px 16px;
          text-decoration: none;
          width: 100%;
        }
        
        &:hover {
          background-color: #f1f1f1;
        }

        &.active {
          background-color: #f1f1f1;
        }

        pointer-events: none;
    }
    }
</style>
