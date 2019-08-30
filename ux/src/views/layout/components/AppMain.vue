<template>
  <div style="height: 100%;">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'AppMain',
  created() {
      this.getOAMessagNum()
      this.getcrmMessagNum()
  },
  computed: {},
  methods: {
      /**
       * 获取消息数
       */
      getOAMessagNum() {
          this.$store
              .dispatch('GetOAMessageNum')
              .then(res => {})
              .catch(() => {})
      },
      /**
       * 获取消息数
       */
      getcrmMessagNum() {
          this.$store
              .dispatch('GetMessageNum')
              .then(res => {})
              .catch(() => {})
      }
    }
}
</script>

<style scoped>
</style>
