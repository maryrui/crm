import request from '@/utils/request'

/**
 * 订单数量分析/金额分析/回款金额分析
 * count：订单数量分析；money：金额分析；back：回款金额分析
 */
export function biAchievementAnalysisAPI(data) {
  return request({
    url: 'bi/contract/analysis',
    method: 'post',
    data: data
  })
}

/**
 * 订单汇总表
 * @param {*} data 
 */
export function biAchievementSummaryAPI(data) {
  return request({
    url: 'bi/contract/summary',
    method: 'post',
    data: data
  })
}
