<template>
  <slide-view v-empty="!canShowDetail"
              :listenerIDs="listenerIDs"
              :noListenerIDs="noListenerIDs"
              :noListenerClass="noListenerClass"
              @side-close="hideView"
              :body-style="{padding: 0, height: '100%'}">
    <flexbox v-if="canShowDetail"
             v-loading="loading"
             xs-empty-icon="nopermission"
             xs-empty-text="暂无权限"
             direction="column"
             align="stretch"
             class="d-container">
      <c-r-m-detail-head crmType="receivables_plan"
                         @handle="detailHeadHandle"
                         :isSeas="!showEditorBtn"
                         @close="hideView"
                         :headDetails="headDetails"
                         :id="id">
      </c-r-m-detail-head>
      <div class="examine-info">
        <examine-info :id="id"
                      class="examine-info-border"
                      examineType="crm_receivables_plan"
                      @checkCreatedId="checkCreatedId"
                      :detail="dataDetail"
                      :flow_id="dataDetail.flow_id">
        </examine-info>
      </div>
      <div class="tabs">
        <el-tabs v-model="tabCurrentName"
                 @tab-click="handleClick">
          <el-tab-pane v-for="(item, index) in tabnames"
                       :key="index"
                       :label="item.label"
                       :name="item.name"></el-tab-pane>
        </el-tabs>
      </div>
      <div class="t-loading-content"
           id="follow-log-content">
        <keep-alive>
          <component v-bind:is="tabName"
                     crmType="receivables_plan"
                     :detail="detailData"
                     :id="id"></component>
        </keep-alive>
      </div>
    </flexbox>
    <c-r-m-create-view v-if="isCreate"
                       crm-type="receivables_plan"
                       :detail="dataDetail"
                       :action="{type: 'update', id: this.id}"
                       @save-success="editSaveSuccess"
                       @hiden-view="isCreate=false"></c-r-m-create-view>
  </slide-view>
</template>

<script>
import { crmContractRead } from '@/api/customermanagement/contract'
import SlideView from '@/components/SlideView'
import CRMDetailHead from '../components/CRMDetailHead'
import ContractFollow from './components/InvoiceFollow' // 跟进记录
import CRMBaseInfo from '../components/CRMBaseInfo' // 合同基本信息
import RelativeHandle from '../components/RelativeHandle' //相关操作
import RelativeTeam from '../components/RelativeTeam' //相关团队
import RelativeProduct from '../components/RelativeProduct' //相关团队
import RelativeReturnMoney from '../components/RelativeReturnMoney' //相关回款
import RelativeFiles from '../components/RelativeFiles' //相关附件
import ExamineInfo from '@/components/Examine/ExamineInfo'

import CRMCreateView from '../components/CRMCreateView' // 新建页面
import { mapGetters } from 'vuex'

import moment from 'moment'
import detail from '../mixins/detail'

export default {
  /** 客户管理 的 订单详情 */
  name: 'Invoice-detail',
  components: {
    SlideView,
    CRMDetailHead,
    ContractFollow,
    CRMBaseInfo,
    RelativeHandle,
    RelativeTeam,
    RelativeProduct,
    RelativeReturnMoney,
    RelativeFiles,
    ExamineInfo,
    CRMCreateView
  },
  mixins: [detail],
  props: {
    // 详情信息id
    id: [String, Number],
    // 监听的dom 进行隐藏详情
    listenerIDs: {
      type: Array,
      default: () => {
        return ['crm-main-container']
      }
    },
    // 不监听
    noListenerIDs: {
      type: Array,
      default: () => {
        return []
      }
    },
    noListenerClass: {
      type: Array,
      default: () => {
        return ['el-table__body']
      }
    },
    dataDetail : {
      type: Object,
      default: () => {
          return {}
      }
    }
  },
  watch: {
      dataDetail: {
          handler: function(val, oldval) {
              this.dataDetail = val
              this.setHead()
          },
          deep: true // 对象内部的属性监听，也叫深度监听
      },
      created_id: {
          handler: function(val, oldval) {
              this.created_id = val
          },
          deep: true // 对象内部的属性监听，也叫深度监听
      }
  },
  data() {
    return {
      loading: false, // 展示加载loading
      crmType: 'receivables_plan',
      created_id: '', // 判断当前账户是不是发起人
      detailData: {},
      headDetails: [
        { title: '发票编号', value: '' },
        { title: '客户名称', value: '' },
        { title: '订单名称', value: '' },
        { title: '发票方', value: '' },
        { title: '真实票号', value: '' },
        { title: '开票金额', value: '' },
        { title: '回款方式', value: '' }
      ],
      tabCurrentName: 'basicinfo',
      isCreate: false // 编辑操作
    }
  },
  computed: {
    tabName() {
      if (this.tabCurrentName == 'followlog') {
        return 'contract-follow'
      } else if (this.tabCurrentName == 'basicinfo') {
        return 'c-r-m-base-info'
      } else if (this.tabCurrentName == 'team') {
        return 'relative-team'
      } else if (this.tabCurrentName == 'contract') {
        return 'relative-contract'
      } else if (this.tabCurrentName == 'operationlog') {
        return 'relative-handle'
      } else if (this.tabCurrentName == 'product') {
        return 'relative-product'
      } else if (this.tabCurrentName == 'returnedmoney') {
        return 'relative-return-money'
      } else if (this.tabCurrentName == 'file') {
        return 'relative-files'
      }
      return ''
    },
    tabnames() {
      var tempsTabs = []
      // tempsTabs.push({ label: '跟进记录', name: 'followlog' })
      if (this.crm.contract && this.crm.contract.read) {
        tempsTabs.push({ label: '基本信息', name: 'basicinfo' })
      }
      // if (this.crm.product && this.crm.product.index) {
      //   tempsTabs.push({ label: '产品', name: 'product' })
      // }
      // if (this.crm.receivables && this.crm.receivables.index) {
      //   tempsTabs.push({ label: '回款信息', name: 'returnedmoney' })
      // }
      // tempsTabs.push({ label: '相关团队', name: 'team' })
      tempsTabs.push({ label: '附件', name: 'file' })
      // tempsTabs.push({ label: '操作记录', name: 'operationlog' })

      return tempsTabs
    },
      ...mapGetters([
          'userInfo'
      ]),
      showEditorBtn() {
         return this.created_id == this.userInfo.id
      }
  },
  mounted() {
     this.setHead()
  },
  methods: {
    setHead() {
        this.headDetails[0].value = this.dataDetail.invoice_code
        this.headDetails[1].value = this.dataDetail.customer_name
        this.headDetails[2].value = this.dataDetail.contract_name
        this.headDetails[3].value = this.dataDetail.invoicer
        this.headDetails[4].value = this.dataDetail.real_code
        this.headDetails[5].value = this.dataDetail.money
        this.headDetails[6].value = this.dataDetail.return_type
    },
    getDetial() {
      this.loading = true
      crmContractRead({
        id: this.id
      })
        .then(res => {
          this.loading = false
          this.detailData = res.data // 创建回款计划的时候使用

        })
        .catch(() => {
          this.loading = false
        })
    },
    //** 点击关闭按钮隐藏视图 */
    hideView() {
      this.$emit('hide-view')
    },
    //** tab标签点击 */
    handleClick(tab, event) {},
    editSaveSuccess() {
      this.getDetial()
    },
    /* 编辑按钮的方法，子组件传参 */
    checkCreatedId(id) {
        this.created_id = id
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/crmdetail.scss';
</style>
