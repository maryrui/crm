<template>
  <div v-loading="loading"
       class="main-container">
    <filtrate-handle-view class="filtrate-bar"
                          moduleType="contract"
                          :showYearSelect="true"
                          @load="loading=true"
                          @change="getDataList">
    </filtrate-handle-view>
    <div class="content">
      <div class="axis-content">
        <div id="axismain"></div>
      </div>
      <div class="table-content">
        <el-button :loading="downloadLoading" style="margin:10px 0 10px 0;" type="primary" icon="el-icon-document" @click="handleDownload(fieldList, list, '订单数量分析')">
          导出
        </el-button>
        <el-table :data="list"
                  height="300"
                  stripe
                  border
                  highlight-current-row>
          <el-table-column v-for="(item, index) in fieldList"
                           :key="index"
                           align="center"
                           :fixed="index==0?true: false"
                           :min-width="index==0?180: 100"
                           header-align="center"
                           show-overflow-tooltip
                           :prop="item.field"
                           :label="item.name">
          </el-table-column>
        </el-table>
      </div>
    </div>
  </div>
</template>

<script>
import achievementMixins from '../mixins/achievement'
import exportTable from '../mixins/exportTable'

export default {
  /** 订单数量分析 */
  name: 'achievement-count-statistics',
  data() {
    return {}
  },
  mixins: [achievementMixins, exportTable],
  computed: {},
  created() {
    this.type = 'count'
  },
  methods: {}
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
@import '../styles/detail.scss';
</style>
