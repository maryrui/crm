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
                          max-height="800"
                          :show-summary="true"
                          :summary-method="summarySpanMethod"
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
    import { timestampToFormatTime } from "@/utils"
    export default {
        /** 账款数据分析 */
        name: 'ranking-add-contacts-statistics',
        data() {
            return {
                rowSpanData: [],
                spanArr: [],
                codeArr: [],
                code: [],
                pos: [],
                sum: 0,
                codeSum: 0,
                detSum: 0,
                downloadLoading: false,
                fieldList: [
                    { field: 'index', name: 'id' },
                    { field: 'contract_id', name: '订单编号' },
                    { field: 'name', name: '订单名称' },
                    { field: 'customer_name', name: '客户名称' },
                    { field: 'realname', name: '客户负责人' },
                    { field: 'money', name: '订单金额' },
                    { field: 'invoice_code', name: '发票编号' },
                    { field: 'invoice_money', name: '发票金额' },
                    { field: 'debt', name: '欠款金额' },
                    { field: 'realnames', name: '开票申请人' },
                    { field: 'create_time', name: '开票申请时间' },
                    { field: 'return_num', name: '回款编号' },
                    { field: 'return_money', name: '回款金额' },
                    { field: 'return_date', name: '回款日期' }
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
                        this.codeArr.push(1)
                        this.code = 0
                    } else {
                        // 判断当前元素与上一个元素是否相同
                        if (data[i].contract_id === data[i - 1].contract_id) {
                            this.spanArr[this.pos] += 1
                            this.spanArr.push(0)
                            if (data[i].invoice_code === data[i - 1].invoice_code) {
                                this.codeArr[this.code] += 1
                                this.codeArr.push(0)
                            } else {
                                this.codeArr.push(1)
                                this.code = i
                            }
                        } else {
                            this.spanArr.push(1)
                            this.pos = i
                            this.codeArr.push(1)
                            this.code = i
                        }
                    }
                }
            },
            summarySpanMethod(params) {
                const { columns, data } = params
                const sums = []
                columns.forEach((column, index) => {
                    if (index === 0) {
                        sums[index] = '总价'
                        return
                    }
                    if (index === 5) {
                        sums[index] = this.sum + '元'
                        return
                    }
                    if (index === 7) {
                        sums[index] = this.codeSum + '元'
                        return
                    }
                    if (index === 8) {
                        sums[index] = this.detSum + '元'
                        return
                    }
                    if (index === 12) {
                        sums[index] = this.return_money + '元'
                        return
                    } else {
                        sums[index] = ''
                        return
                    }
                    const values = data.map(item => Number(item[column.property]))
                    if (!values.every(value => isNaN(value))) {
                        sums[index] = values.reduce((prev, curr) => {
                            const value = Number(curr)
                            if (!isNaN(value)) {
                                return prev + curr
                            } else {
                                return prev
                            }
                        }, 0)
                        sums[index] += ' 元'
                    } else {
                        sums[index] = 'N/A'
                    }
                })
                return sums
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
                if (columnIndex === 5 || columnIndex === 6 || columnIndex === 7 || columnIndex === 8 || columnIndex === 9) {
                    const _row1 = this.codeArr[rowIndex]
                    const _col1 = _row1 > 0 ? 1 : 0
                    return {
                        rowspan: _row1,
                        colspan: _col1
                    }
                }
            },
            processData(list) {
                var arr = []
                this.sum = 0
                this.codeSum = 0
                this.detSum = 0
                this.return_money = 0
                for (var i = 0; i < list.length; i++) {
                    if (list[i].receivables_plan.length > 0) {
                        for (var j = 0; j < list[i].receivables_plan.length; j++) {
                            this.codeSum += parseFloat(list[i].receivables_plan[j].money)
                            this.sum += parseFloat(list[i].money)
                            this.detSum += parseFloat(list[i].receivables_plan[j].balance)
                            if (list[i].receivables_plan[j].receivables.length > 0) {
                                for (var m = 0; m < list[i].receivables_plan[j].receivables.length; m++) {
                                    this.return_money += parseFloat(list[i].receivables_plan[j].receivables[m].money)
                                    arr.push({
                                        index: i + 1,
                                        contract_id: list[i].num,
                                        customer_name: list[i].customer_name,
                                        realname: list[i].realname,
                                        money: list[i].money,
                                        name: list[i].name,
                                        return_money: list[i].receivables_plan[j].receivables[m].money,
                                        invoice_money: list[i].receivables_plan[j].money,
                                        invoice_code: list[i].receivables_plan[j].invoice_code,
                                        return_date: list[i].receivables_plan[j].receivables[m].create_time ? timestampToFormatTime(list[i].receivables_plan[j].receivables[m].create_time).replace('T', ' ').split('+')[0] : 0,
                                        return_num: list[i].receivables_plan[j].receivables[m].number,
                                        realnames: list[i].receivables_plan[j].realname,
                                        create_time: list[i].receivables_plan[j].create_time ? timestampToFormatTime(list[i].receivables_plan[j].create_time).replace('T', ' ').split('+')[0] : 0,
                                        debt: list[i].receivables_plan[j].balance
                                    })
                                }
                            } else {
                                arr.push({
                                    index: i + 1,
                                    contract_id: list[i].num,
                                    customer_name: list[i].customer_name,
                                    realname: list[i].realname,
                                    money: list[i].money,
                                    name: list[i].name,
                                    return_money: 0,
                                    invoice_money: list[i].receivables_plan[j].money,
                                    invoice_code: list[i].receivables_plan[j].invoice_code,
                                    debt: list[i].receivables_plan[j].balance,
                                    realnames: list[i].receivables_plan[j].realname,
                                    create_time:list[i].receivables_plan[j].create_time ? timestampToFormatTime(list[i].receivables_plan[j].create_time).replace('T', ' ').split('+')[0] : 0,
                                    return_date: 0,
                                    return_num: 0
                                })
                            }
                        }
                    } else {
                        arr.push({
                            index: i + 1,
                            contract_id: list[i].num,
                            customer_name: list[i].customer_name,
                            realname: list[i].realname,
                            money: list[i].money,
                            name: list[i].name,
                            return_money: 0,
                            realnames: 0,
                            create_time: 0,
                            invoice_code: 0,
                            return_date: 0,
                            return_num: 0,
                            invoice_money: 0,
                            debt: 0
                        })
                    }
                }
                this.list =  arr
                this.spanArr = []
                this.pos = []
                this.codeArr = []
                this.code = []
                this.getSpanArr(arr)
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    @import './styles/detail.scss';
</style>
