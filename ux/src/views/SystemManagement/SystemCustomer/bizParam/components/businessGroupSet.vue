<template>
  <div v-loading="loading">
    <div class="content-title">
      <span>合同组设置</span>
      <el-button type="primary"
                 class="rt"
                 size="medium"
                 @click="addBusiness">添加合同组</el-button>
    </div>
    <div class="business-table">
      <el-table :data="businessData"
                style="width: 100%"
                stripe
                :header-cell-style="headerCellStyle">
        <el-table-column v-for="(item, index) in businessList"
                         :key="index"
                         show-overflow-tooltip
                         :prop="item.field"
                         :label="item.label"
                         :formatter="fieldFormatter">

        </el-table-column>
        <el-table-column fixed="right"
                         label="操作"
                         width="100">
          <template slot-scope="scope">
            <el-button @click="businessEdit(scope.row)"
                       type="text"
                       size="small">编 辑</el-button>
            <el-button type="text"
                       size="small"
                       @click="businessDelect(scope)">删 除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <business-dialog @businessClose="businessClose"
                     @businessSubmit="businessSubmit"
                     :infoData="businessObj"
                     :businessDialogVisible="businessDialogVisible"
                     :businessTitle="businessTitle">
    </business-dialog>
  </div>
</template>

<script>
import BusinessDialog from '@/views/SystemManagement/components/businessDialog'
import {
  businessGroupList,
  businessGroupAdd,
  businessGroupUpdate,
  businessGroupRead,
  businessGroupDelete
} from '@/api/systemManagement/SystemCustomer'
import { getDateFromTimestamp } from '@/utils'
import moment from 'moment'

export default {
  name: 'business-group-set',

  components: {
    BusinessDialog
  },

  data() {
    return {
      loading: false, // 展示加载中效果

      // 导航显示不同的页面
      menuIndex: 'business',

      // 合同组设置
      /** 合同组每行的信息 */
      businessObj: { name: '', businessDep: [], settingList: '' },
      businessData: [],
      businessList: [
        { label: '合同组名称', field: 'name' },
        { label: '应用部门', field: 'structure_id' },
        { label: '创建时间', field: 'create_time' },
        { label: '创建人', field: 'create_user_id' }
      ],
      // 添加合同组
      businessDialogVisible: false,
      businessTitle: '添加合同组'
    }
  },
  methods: {
    /**
     * 合同组列表头样式
     */
    headerCellStyle(val, index) {
      return { background: '#F2F2F2' }
    },

    /**
     * 合同组列表
     */
    getBusinessGroupList() {
      this.loading = true
      businessGroupList({
        page: 1,
        limit: 100,
        search: ''
      })
        .then(res => {
          this.loading = false
          this.businessData = res.data.list
        })
        .catch(() => {
          this.loading = false
        })
    },

    /**
     * 合同列表格式化
     */
    fieldFormatter(row, column) {
      // 如果需要格式化
      if (column.property == 'structure_id') {
        //格式部门
        var info = row[column.property + '_info']
        var name = ''
        if (info) {
          for (let index = 0; index < info.length; index++) {
            name =
              name + info[index].name + (index === info.length - 1 ? '' : '、')
          }
        }
        return name ? name : '全公司'
      } else if (column.property == 'create_time') {
        //格式时间
        if (row[column.property] == 0 || !row[column.property]) {
          return ''
        }
        return moment(getDateFromTimestamp(row[column.property])).format(
          'YYYY-MM-DD HH:mm:ss'
        )
      } else if (column.property == 'create_user_id') {
        //格式create_user 人
        var info = row[column.property + '_info']
        return info ? info.realname : ''
      }
      return row[column.property]
    },

    /**
     * 合同组编辑
     */
    businessEdit(data) {
      businessGroupRead({
        id: data.type_id
      })
        .then(res => {
          var settingList = []
          if (res.data.status) {
            settingList = res.data.status
          }
          this.businessObj = {
            type_id: data.type_id,
            name: data.name,
            businessDep: data.structure_id_info,
            settingList: settingList
          }
          this.businessDialogVisible = true
          this.businessTitle = '编辑合同组'
        })
        .catch(() => {})
    },

    /**
     * 合同组删除
     */
    businessDelect(scope) {
      this.$confirm('确定删除?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
        .then(() => {
          businessGroupDelete({
            id: scope.row.type_id
          })
            .then(res => {
              this.businessData.splice(scope.$index, 1)
              this.$message.success('删除成功')
            })
            .catch(() => {})
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消删除'
          })
        })
    },

    /**
     * 合同组添加
     */
    addBusiness() {
      this.businessObj = { name: '', businessDep: [], settingList: '' }
      this.businessDialogVisible = true
      this.businessTitle = '添加合同组'
    },

    /**
     * 合同组添加 -- 关闭
     */
    businessClose() {
      this.businessDialogVisible = false
    },

    /**
     * 合同组添加 -- 确定按钮
     */
    businessSubmit(name, dep, list, title, type_id) {
      var businessHandleRequest = null
      var params = {
        name: name,
        structure_id: dep,
        status: list
      }
      if (title == '添加合同组') {
        businessHandleRequest = businessGroupAdd
      } else {
        params.type_id = type_id
        businessHandleRequest = businessGroupUpdate
      }
      businessHandleRequest(params)
        .then(res => {
          this.$message.success(res.data)
          this.getBusinessGroupList()
          this.businessClose()
        })
        .catch(() => {})
    }
  },
  created() {
    this.getBusinessGroupList()
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.content-title {
  padding: 10px;
  border-bottom: 1px solid #e6e6e6;
}
.content-title > span {
  display: inline-block;
  height: 36px;
  line-height: 36px;
  margin-left: 20px;
}

/* 合同组设置 */

.business-table {
  border: 1px solid #e6e6e6;
  margin: 30px;
  flex: 1;
  overflow: auto;
  box-sizing: border-box;
}
</style>
