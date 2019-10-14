<template>
    <div v-loading="loading"
         class="main-container">
        <filtrate-handle-view class="filtrate-bar"
                              :showUserSelect="true"
                              :showBalance="true"
                              :showClient="true"
                              moduleType="ranking"
                              @load="loading=true"
                              @change="getDataList">
        </filtrate-handle-view>
        <div class="content">
            <!--<div class="content-title">新增联系人数排行（按创建人、创建时间统计）</div>-->
            <div class="table-content">
                <el-button :loading="downloadLoading" style="margin:10px 0 10px 0;" type="primary" icon="el-icon-document" @click="handleDownload(fieldList, list, '账款数据分析')">
                    导出
                </el-button>
                <el-table :data="list"
                          height="800"
                          stripe
                          border
                          :span-method="objectSpanMethod"
                          highlight-current-row>
                    <el-table-column v-for="(items, index) in fieldList"
                                     :key="index"
                                     align="center"
                                     header-align="center"
                                     show-overflow-tooltip
                                     :prop="items.field"
                                     :label="items.name">
                    </el-table-column>
                </el-table>
            </div>
        </div>
    </div>
</template>

<script>
    import rankingMixins from './mixins/ranking'
    import exportTable from './mixins/exportTable'
    import { getAccountDatnum } from '@/api/businessIntelligence/accountDatnum'

    export default {
        /** 账款数据分析 */
        name: 'ranking-add-contacts-statistics',
        data() {
            return {
                rowSpanData: [],
                spanArr: [],
                pos: [],
                downloadLoading: false,
                fieldList: [
                    { field: 'contract_id', name: '订单编号' },
                    { field: 'name', name: '订单名称' },
                    { field: 'customer_name', name: '客户名称' },
                    { field: 'realname', name: '客户负责人' },
                    { field: 'money', name: '订单金额' },
                    { field: 'invoice_code', name: '发票编号' },
                    { field: 'invoice_money', name: '发票金额' },
                    { field: 'return_date', name: '回款日期' },
                    { field: 'return_money', name: '回款金额' },
                    { field: 'debt', name: '欠款金额' }
                ]
            }
        },
        mixins: [rankingMixins, exportTable],
        computed: {},
        methods: {
            getDataList(params) {
                this.list = []
                this.loading = true
                getAccountDatnum(params)
                    .then(res => {
                        this.loading = false
                        this.processData(res.data)
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            getSpanArr(data) {
                for (var i = 0; i < data.length; i++) {
                    if (i === 0) {
                        this.spanArr.push(1)
                        this.pos = 0
                    } else {
                        // 判断当前元素与上一个元素是否相同
                        if (data[i].contract_id === data[i - 1].contract_id) {
                            this.spanArr[this.pos] += 1
                            this.spanArr.push(0)
                        } else {
                            this.spanArr.push(1)
                            this.pos = i
                        }
                    }
                }
            },
            objectSpanMethod({ row, column, rowIndex, columnIndex }) {
                if (columnIndex === 0 || columnIndex === 1 || columnIndex === 2 || columnIndex === 3 || columnIndex === 4) {
                    const _row = this.spanArr[rowIndex]
                    const _col = _row > 0 ? 1 : 0
                    return {
                        rowspan: _row,
                        colspan: _col
                    }
                }
            },
            processData(list) {
                var arr = []
                for (var i = 0; i < list.length; i++) {
                    if (list[i].receivables_plan.length > 0) {
                        for (var j = 0; j < list[i].receivables_plan.length; j++) {
                            arr.push({
                                contract_id: list[i].num,
                                customer_name: list[i].customer_name,
                                realname: list[i].realname,
                                money: list[i].money,
                                name: list[i].name,
                                return_money: list[i].receivables_plan[j].receivables_money,
                                invoice_money: list[i].receivables_plan[j].money,
                                invoice_code: list[i].receivables_plan[j].invoice_code,
                                return_date: list[i].receivables_plan[j].return_date,
                                debt: list[i].receivables_plan[j].balance
                            })
                        }
                    } else {
                        arr.push({
                            contract_id: list[i].num,
                            customer_name: list[i].customer_name,
                            realname: list[i].realname,
                            money: list[i].money,
                            name: list[i].name,
                            return_money: 0,
                            invoice_code: 0,
                            return_date: 0,
                            invoice_money: 0,
                            debt: 0
                        })
                    }
                }
                this.list =  arr
                this.spanArr = []
                this.pos = []
                this.getSpanArr(arr)
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    @import './styles/detail.scss';
</style>
