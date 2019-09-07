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
            <div class="axis-content">
                <div id="axismain"></div>
            </div>
            <div class="table-content">
                <el-table :data="list"
                          stripe
                          border
                          height="400"
                          highlight-current-row>
                    <el-table-column v-for="(item, index) in fieldList"
                                     :key="index"
                                     align="center"
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
    import rankingMixins from './mixins/ranking'
    import echarts from 'echarts'
    import { adminStructuresSubIndex, getSubUserByStructrue } from '@/api/common'
    import { getComplaintDatnum } from '@/api/businessIntelligence/complaint'
    import { biAchievementStatistics } from '@/api/businessIntelligence/bi'
    import moment from 'moment'

    export default {
        /** 业绩目标完成情况 */
        name: 'task-complete-statistics',
        mixins: [rankingMixins],
        components: {},
        data() {
            return {
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now()
                    }
                },
                loading: false,

                dateSelect: '', // 选择的date
                typeSelect: 1, // 类型选择 1销售（目标）2回款（目标）
                /** 部门选择解析数据 */
                structuresProps: {
                    children: 'children',
                    label: 'label',
                    value: 'id'
                },
                deptList: [], // 部门列表
                structuresSelectValue: '',
                /** 用户列表 */
                userOptions: [],
                userSelectValue: '',

                list: [],
                fieldList: [
                    { field: 'type', name: '客诉类型' },
                    { field: 'count', name: '总数' },
                    { field: 'rate', name: '客诉占比' }
                ],

                axisChart: null, // 柱状图
                axisOption: null
            }
        },
        computed: {},
        mounted() {
            this.dateSelect = moment(new Date())
                .year()
                .toString()
            this.getDeptList()
            this.initAxis([])
        },
        methods: {
            /**
             * 获取部门列表
             */
            getDeptList() {
                this.loading = true
                adminStructuresSubIndex({
                    m: 'bi',
                    c: 'achievement',
                    a: 'read'
                })
                    .then(res => {
                        this.deptList = res.data
                        this.loading = false
                        if (res.data.length > 0) {
                            this.structuresSelectValue = res.data[0].id
                            // this.getUserList()
                            // this.getAhievementDatalist()
                        }
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            getDataList(params) {
                this.loading = true
                getComplaintDatnum(params).then(res => {
                    this.loading = false
                    this.list = res.data
                    this.initAxis(res.data)
                })
            },
            /** 部门更改 */
            structuresValueChange(data) {
                this.userSelectValue = ''
                this.userOptions = []
                this.getUserList() // 更新员工列表
            },
            /** 部门下员工 */
            getUserList() {
                let params = {}
                params.structure_id = this.structuresSelectValue
                getSubUserByStructrue(params)
                    .then(res => {
                        this.userOptions = res.data
                    })
                    .catch(() => {})
            },
            /** 获取部门业绩完成信息 */
            getAhievementDatalist() {
                this.loading = true
                biAchievementStatistics({
                    year: this.dateSelect,
                    status: this.typeSelect,
                    structure_id: this.structuresSelectValue,
                    user_id: this.userSelectValue
                })
                    .then(res => {
                        this.list = []
                        let receivabless = []
                        let achiements = []
                        let rates = []
                        for (let index = 1; index < 13; index++) {
                            const element = res.data[index]
                            receivabless.push(element.receivables)
                            achiements.push(element.achiement)
                            rates.push(element.rate)
                            this.list.push(element)
                        }

                        this.axisOption.series[0].data = receivabless
                        this.axisOption.series[1].data = achiements
                        this.axisOption.series[2].data = rates
                        this.axisChart.setOption(this.axisOption, true)
                        this.loading = false
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            /** 顶部操作 */
            handleClick(type) {
                if (type === 'search') {
                    this.refreshTableHeadAndChartInfo()
                    this.getAhievementDatalist()
                }
            },
            /**
             * 刷新表头和图标关键字
             */
            refreshTableHeadAndChartInfo() {
                this.fieldList[1].name =
                    this.typeSelect == 1 ? '订单金额(元)' : '回款金额(元)'
                this.axisOption.legend.data[0] =
                    this.typeSelect == 1 ? '订单金额' : '回款金额'
                this.axisOption.series[0].name =
                    this.typeSelect == 1 ? '订单金额' : '回款金额'
            },
            /** 柱状图 */
            initAxis(list) {
                let axisChart = echarts.init(document.getElementById('axismain'))
                let xData = []
                for (let i = 0; i < list.length; i++) {
                    xData.push({
                        name: list[i].type,
                        value: list[i].count
                    })
                }
                let option = {
                    backgroundColor: '#fff',
                    title: {
                        text: '客诉占比',
                        left: 'center',
                        top: 20,
                        textStyle: {
                            color: '#ccc'
                        }
                    },

                    tooltip : {
                        trigger: 'item',
                        formatter: "{a} <br/>{b} : {c} ({d}%)"
                    },

                    visualMap: {
                        show: false,
                        min: 80,
                        max: 600,
                        inRange: {
                            colorLightness: [0, 1]
                        }
                    },
                    series : [
                        {
                            name:'客诉占比',
                            type:'pie',
                            radius : '55%',
                            center: ['50%', '50%'],
                            data: xData.sort(function(a, b) { return a.value - b.value }),
                            roseType: 'radius',
                            label: {
                                normal: {
                                    textStyle: {
                                        color: 'rgba(255, 255, 255, 0.3)'
                                    }
                                }
                            },
                            labelLine: {
                                normal: {
                                    lineStyle: {
                                        color: 'rgba(255, 255, 255, 0.3)'
                                    },
                                    smooth: 0.2,
                                    length: 10,
                                    length2: 20
                                }
                            },
                            itemStyle: {
                                normal: {
                                    color: '#c23531',
                                    shadowBlur: 200,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            },

                            animationType: 'scale',
                            animationEasing: 'elasticOut',
                            animationDelay: function (idx) {
                                return Math.random() * 200;
                            }
                        }
                    ]
                }

                axisChart.setOption(option, true)
                this.axisOption = option
                this.axisChart = axisChart
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    @import './styles/detail.scss';
    .handle-bar {
        background-color: white;
        padding: 15px 20px 5px 20px;
        .el-date-editor {
            width: 130px;
            margin-right: 15px;
        }
        .el-cascader {
            width: 130px;
            margin-right: 15px;
        }
        .el-select {
            width: 120px;
            margin-right: 15px;
        }
    }
</style>

