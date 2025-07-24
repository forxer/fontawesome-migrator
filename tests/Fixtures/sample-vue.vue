<template>
  <div class="vue-component">
    <!-- Font Awesome Vue component syntax -->
    <font-awesome-icon icon="fas fa-home" />
    <font-awesome-icon :icon="['fas', 'fa-user']" />
    <font-awesome-icon icon="far fa-star" />
    
    <!-- Icons that need renaming -->
    <font-awesome-icon icon="fas fa-times" />
    <font-awesome-icon icon="fas fa-external-link" />
    
    <!-- Dynamic icons -->
    <font-awesome-icon :icon="iconName" />
    
    <!-- Pro styles -->
    <font-awesome-icon icon="fal fa-heart" />
    <font-awesome-icon icon="fad fa-magic" />
    
    <!-- In template strings -->
    <i :class="`fas fa-${iconType}`"></i>
  </div>
</template>

<script>
export default {
  name: 'FontAwesomeComponent',
  data() {
    return {
      iconName: 'fas fa-cog',
      iconType: 'gear',
      icons: {
        home: 'fas fa-home',
        user: 'far fa-user',
        close: 'fas fa-times'
      }
    }
  }
}
</script>