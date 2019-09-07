<template>
    <div v-loading="loading"
         class="main-container">
        <filtrate-handle-view class="filtrate-bar"
                              :showUserSelect="false"
                              moduleType="ranking"
                              @load="loading=true"
                              @change="getDataList">
        </filtrate-handle-view>
        <div class="content">
            <!--<div class="content-title">新增联系人数排行（按创建人、创建时间统计）</div>-->
            <div class="table-content">
                <el-table :data="list"
                          height="800"
                          stripe
                          border
                          :span-method="objectSpanMethod"
                          highlight-current-row>
                    <!--<el-table-column align="center"
                                     header-align="center"
                                     show-overflow-tooltip
                                     label="公司总排名">
                        <template slot-scope="scope">
                            {{scope.$index + 1}}
                        </template>
                    </el-table-column>-->
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
    import { getAccountDatnum } from '@/api/businessIntelligence/accountDatnum'

    export default {
        /** 新增联系人数排行 */
        name: 'ranking-add-contacts-statistics',
        data() {
            return {
                rowSpanData: []
            }
        },
        mixins: [rankingMixins],
        computed: {},
        mounted() {
            this.fieldList = [
                { field: 'contract_id', name: '订单编号' },
                { field: 'name', name: '订单名称' },
                { field: 'money', name: '订单金额' },
                { field: 'invoice_code', name: '发票编号' },
                { field: 'invoce_money', name: '发票金额' },
                { field: 'return_date', name: '回款日期' },
                { field: 'return_money', name: '回款金额' },
                { field: 'debt', name: '欠款金额' }
            ]
        },
        methods: {
            getDataList(params) {
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
            objectSpanMethod({ row, column, rowIndex, columnIndex }) {
                // console.log(rowIndex, columnIndex)
                for (var i = 0 ; i < this.rowSpanData.length; i++) {
                    console.log(rowIndex)
                    if (columnIndex === 0 || columnIndex === 1 || columnIndex === 2) {
                        if (rowIndex % this.rowSpanData[i] === 0) {
                            return {
                                rowspan: this.rowSpanData[i],
                                colspan: 1
                            }
                        } else {
                            return {
                                rowspan: 0,
                                colspan: 1
                            }
                        }
                    }
                    if (columnIndex === 3 || columnIndex === 4 || columnIndex === 7) {
                        if (row.planLength) {
                            if ( rowIndex % row.planLength === 0) {
                                return {
                                    rowspan: row.planLength,
                                    colspan: 1
                                }
                            }
                        }
                    }
                }

            },
            processData(list) {
                var arr = []
                let rowSpanData = []
                for (var i = 0; i < list.length; i++) {
                    this.rowSpanData.push(0)
                    if (list[i].receivables.length) {
                        for (var j = 0; j < list[i].receivables.length; j++) {
                            if (list[i].receivables[j].plans.length) {
                                for (var m = 0; m < list[i].receivables[j].plans.length; m++) {
                                    this.rowSpanData[i]++
                                    arr.push({
                                        balance:list[i].balance,
                                        contract_id: list[i].contract_id,
                                        money: list[i].money,
                                        name: list[i].name,
                                        return_money: list[i].receivables[j].money,
                                        planLength:list[i].receivables[j].plans.length,
                                        invoice_code : list[i].receivables[j].plans[m].invoice_code,
                                        return_date: list[i].receivables[j].plans[m].return_date,
                                        invoce_money: list[i].receivables[j].plans[m].money,
                                        debt: list[i].receivables[j].plans[m].money
                                    })
                                }
                            }else{
                                this.rowSpanData[i]++
                                arr.push({
                                    balance:list[i].balance,
                                    contract_id: list[i].contract_id,
                                    money: list[i].money,
                                    name: list[i].name,
                                    return_money: 0,
                                    planLength: 0,
                                    invoice_code : 0,
                                    return_date: 0,
                                    invoce_money: 0,
                                    debt: 0
                                })
                            }
                        }
                    }else{
                        this.rowSpanData[i]++
                        arr.push({
                            balance:list[i].balance,
                            contract_id: list[i].contract_id,
                            money: list[i].money,
                            name: list[i].name,
                            return_money: 0,
                            planLength: 0,
                            invoice_code : 0,
                            return_date: 0,
                            invoce_money: 0,
                            debt: 0
                        })
                    }
                }
                this.list =  arr
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    @import './styles/detail.scss';
</style>
