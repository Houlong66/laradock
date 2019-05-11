<template>
  <v-alert
    v-model="showWrap"
    :type="type"
    :class="showContent ?'fadein':'fadeout'"
    class="alert"
  >
    {{ text }}
  </v-alert>
</template>

<style scoped>
  .fadein {
    animation: animate_in 0.25s;
  }
  .fadeout {
    animation: animate_out 0.25s;
    opacity: 0;
  }
  @keyframes animate_in {
    0% {
      opacity: 0;
    }
    100%{
      opacity: 1;
    }
  }
  @keyframes animate_out {
    0% {
      opacity: 1;
    }
    100%{
      opacity: 0;
    }
  }
  .alert{
    position: fixed;
    opacity: 1;
    z-index: 999;
    top: 0;
    width: 100%;
  }
</style>
